<?php

namespace App\Tests\Serializer;

use App\Entity\ActionGenre;
use App\Entity\FormationGenre;
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
    public function testItNormalizesDoctrineToOneRelationsAsMinimalObjects(): void
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

        $normalizer = $this->createRelationIdNormalizer(associationName: 'programmePner', targetClass: ProgrammePner::class);
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

        self::assertSame(['code' => 'PRJ-TEST-001', 'programmePner' => ['id' => 1, 'code' => 'PUERG', 'nom' => 'PUERG']], $normalizer->normalize($projet));
    }


    public function testItMapsRelationTitlesToIntituleWithoutNestedCollections(): void
    {
        $actionGenre = (new ActionGenre())
            ->setCode('GEN-PILOTE-001')
            ->setTitre('Action genre pilote')
            ->setAxeStrategique(ActionGenre::AXE_INTEGRATION_PROJETS)
            ->setStatut(ActionGenre::STATUT_PLANIFIEE);
        $this->setEntityId($actionGenre, 7);

        $formation = (new FormationGenre())
            ->setCode('FORM-GEN-001')
            ->setIntitule('Formation genre pilote')
            ->setActionGenre($actionGenre)
            ->setNombreParticipants(20)
            ->setNombreFemmes(12)
            ->setNombreHommes(8)
            ->setTheme('Inclusion');

        $normalizer = $this->createRelationIdNormalizer(associationName: 'actionGenre', targetClass: ActionGenre::class);
        $normalizer->setNormalizer(new class implements NormalizerInterface {
            public function normalize(mixed $object, ?string $format = null, array $context = []): array
            {
                return ['code' => $object->getCode(), 'actionGenre' => '/api/action_genres/7'];
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

        self::assertSame(
            ['code' => 'FORM-GEN-001', 'actionGenre' => ['id' => 7, 'code' => 'GEN-PILOTE-001', 'intitule' => 'Action genre pilote']],
            $normalizer->normalize($formation)
        );
    }

    public function testItDenormalizesSimpleIdsIntoDoctrineRelations(): void
    {
        $programme = (new ProgrammePner())->setCode('PUERG')->setNom('PUERG')->setPeriodeDebut(2023)->setPeriodeFin(2027);
        $normalizer = $this->createRelationIdNormalizer(associationName: 'programmePner', targetClass: ProgrammePner::class, repositoryResult: $programme);

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

    /**
     * @param class-string $targetClass
     */
    private function createRelationIdNormalizer(string $associationName, string $targetClass, ?object $repositoryResult = null): DoctrineRelationIdNormalizer
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAssociationNames', 'isSingleValuedAssociation', 'getAssociationTargetClass'])
            ->getMock();
        $metadata->method('getAssociationNames')->willReturn([$associationName]);
        $metadata->method('isSingleValuedAssociation')->with($associationName)->willReturn(true);
        $metadata->method('getAssociationTargetClass')->with($associationName)->willReturn($targetClass);

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('find')->with(1)->willReturn($repositoryResult);

        $objectManager = $this->createMock(ObjectManager::class);
        $objectManager->method('getClassMetadata')->willReturn($metadata);
        $objectManager->method('getRepository')->with($targetClass)->willReturn($repository);

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
