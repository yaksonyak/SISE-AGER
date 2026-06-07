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

class DoctrineRelationIdNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'doctrine_relation_id_normalizer_already_called';
    private const ENTITY_NAMESPACE = 'App\\Entity\\';

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
            $normalized[$associationName] = is_object($relation) && method_exists($relation, 'getId') ? $relation->getId() : null;
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
