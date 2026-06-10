<?php

namespace App\Serializer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Keeps API Platform serialization groups as the source of truth: the decorated
 * normalizer first decides which relation fields are exposed, then this normalizer
 * replaces exposed to-one relations with a first-level business summary.
 */
class DoctrineRelationIdNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'doctrine_relation_id_normalizer_already_called';
    private const ENTITY_NAMESPACE = 'App\\Entity\\';
    private const RELATION_FIELDS = [
        'id' => ['getId'],
        'code' => ['getCode'],
        'nom' => ['getNom'],
        'libelle' => ['getLibelle'],
        'intitule' => ['getIntitule', 'getTitre'],
    ];

    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>|string|int|float|bool|null
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array|string|int|float|bool|null
    {
        $context[self::ALREADY_CALLED] = true;
        $normalized = $this->normalizer->normalize($object, $format, $context);

        if (!is_array($normalized) || !is_object($object)) {
            return $normalized;
        }

        $metadata = $this->managerRegistry->getManagerForClass($object::class)?->getClassMetadata($object::class);

        if ($metadata === null) {
            return $normalized;
        }

        foreach ($metadata->getAssociationNames() as $associationName) {
            if (!$metadata->isSingleValuedAssociation($associationName) || !array_key_exists($associationName, $normalized)) {
                continue;
            }

            $getter = 'get'.ucfirst($associationName);

            if (!method_exists($object, $getter)) {
                continue;
            }

            $relation = $object->{$getter}();
            $normalized[$associationName] = is_object($relation) ? $this->normalizeRelationSummary($relation) : null;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return empty($context[self::ALREADY_CALLED])
            && is_object($data)
            && str_starts_with($data::class, self::ENTITY_NAMESPACE)
            && $this->managerRegistry->getManagerForClass($data::class) !== null;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        if (is_array($data)) {
            $data = $this->resolveDoctrineRelationIds($data, $type);
        }

        $context[self::ALREADY_CALLED] = true;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return empty($context[self::ALREADY_CALLED])
            && is_array($data)
            && str_starts_with($type, self::ENTITY_NAMESPACE)
            && $this->managerRegistry->getManagerForClass($type) !== null;
    }

    /**
     * @return array<string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => false,
            '*' => false,
        ];
    }

    /**
     * @return array<string, int|string|float|bool|null>
     */
    private function normalizeRelationSummary(object $relation): array
    {
        $summary = [];

        foreach (self::RELATION_FIELDS as $field => $getters) {
            foreach ($getters as $getter) {
                if (!method_exists($relation, $getter)) {
                    continue;
                }

                $value = $relation->{$getter}();

                if ($value === null || is_scalar($value)) {
                    $summary[$field] = $value;
                    break;
                }
            }
        }

        return $summary;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function resolveDoctrineRelationIds(array $data, string $type): array
    {
        $objectManager = $this->managerRegistry->getManagerForClass($type);

        if ($objectManager === null) {
            return $data;
        }

        $metadata = $objectManager->getClassMetadata($type);

        foreach ($metadata->getAssociationNames() as $associationName) {
            if (!$metadata->isSingleValuedAssociation($associationName)) {
                continue;
            }

            $idKey = $associationName.'Id';
            $hasAssociation = array_key_exists($associationName, $data);
            $hasIdAlias = array_key_exists($idKey, $data);

            if (!$hasAssociation && !$hasIdAlias) {
                continue;
            }

            $value = $hasAssociation ? $data[$associationName] : $data[$idKey];
            unset($data[$idKey]);

            if ($value === null || is_object($value) || is_array($value) || !$this->isSimpleIdentifier($value)) {
                continue;
            }

            $targetClass = $metadata->getAssociationTargetClass($associationName);
            $relation = $objectManager->getRepository($targetClass)->find($value);

            if ($relation === null) {
                throw new NotNormalizableValueException(
                    sprintf('Unable to resolve %s relation "%s" with id "%s".', $type, $associationName, (string) $value)
                );
            }

            $data[$associationName] = $relation;
        }

        return $data;
    }

    private function isSimpleIdentifier(mixed $value): bool
    {
        return is_int($value) || (is_string($value) && ctype_digit($value));
    }
}
