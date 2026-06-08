<?php

namespace App\Tests\Serializer;

use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Serializer\DoctrineRelationIdNormalizer;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DoctrineRelationIdNormalizerTest extends TestCase
{
    public function testItNormalizesDoctrineToOneRelationsAsSimpleIds(): void
    {
        $programme = (new ProgrammePner())->setCode('PUERG')->setNom('PUERG')->setPeriodeDebut(2023)->setPeriodeFin(2027);
        $this->setEntityId($programme, 1);

        $projet = (new ProjetElectrification())
            ->setCode('PRJ-TEST-001')
            ->setIntitule('Projet test')
            ->setProgrammePner($programme)
            ->setStatut(ProjetElectrification::STATUT_PLANIFIE)
            ->setDateDebutPrevue(new \DateTimeImmutable('2024-01-01'))
            ->setDateFinPrevue(new \DateTimeImmutable('2024-12-31'));

        $normalizer = $this->createRelationIdNormalizer();
        $normalizer->setNormalizer(new class implements NormalizerInterface {
            public function normalize(mixed $object, ?string $format = null, array $context = []): array
            {
                return ['code' => $object->getCode(), 'programmePner' => '/api/programme_pners/1'];
            }

            public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
            {
                return true;
            }

            public function getSupportedTypes(?string $format): array
            {
                return ['*' => false];
            }
        });

        self::assertSame(['code' => 'PRJ-TEST-001', 'programmePner' => 1], $normalizer->normalize($projet));
    }

    public function testItDenormalizesSimpleIdsIntoDoctrineRelations(): void
    {
        $programme = (new ProgrammePner())->setCode('PUERG')->setNom('PUERG')->setPeriodeDebut(2023)->setPeriodeFin(2027);
        $normalizer = $this->createRelationIdNormalizer($programme);

        $innerDenormalizer = new class implements DenormalizerInterface {
            /** @var array<string, mixed> */
            public array $payload = [];

            public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): object
            {
                $this->payload = $data;

                return new $type();
            }

            public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
            {
                return true;
            }

            public function getSupportedTypes(?string $format): array
            {
                return ['*' => false];
            }
        };
        $normalizer->setDenormalizer($innerDenormalizer);

        $normalizer->denormalize(['programmePner' => 1], ProjetElectrification::class);

        self::assertSame($programme, $innerDenormalizer->payload['programmePner']);
    }

    private function createRelationIdNormalizer(?ProgrammePner $programme = null): DoctrineRelationIdNormalizer
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAssociationNames', 'isSingleValuedAssociation', 'getAssociationTargetClass'])
            ->getMock();
        $metadata->method('getAssociationNames')->willReturn(['programmePner']);
        $metadata->method('isSingleValuedAssociation')->with('programmePner')->willReturn(true);
        $metadata->method('getAssociationTargetClass')->with('programmePner')->willReturn(ProgrammePner::class);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('find')->with(1)->willReturn($programme);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($metadata);
        $objectManager->method('getRepository')->with(ProgrammePner::class)->willReturn($repository);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($objectManager);

        return new DoctrineRelationIdNormalizer($managerRegistry);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $property = new \ReflectionProperty($entity, 'id');
        $property->setAccessible(true);
        $property->setValue($entity, $id);
    }
}
