<?php

namespace App\Tests\Controller;

use App\Controller\DashboardSseController;
use App\Entity\Localite;
use App\Entity\ObservationSuivi;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Attribute\Route;

class DashboardSseControllerTest extends TestCase
{
    public function testRouteConfigurationMatchesDashboardEndpoint(): void
    {
        $reflection = new \ReflectionMethod(DashboardSseController::class, '__invoke');
        $attributes = $reflection->getAttributes(Route::class);

        self::assertNotEmpty($attributes);
        /** @var Route $route */
        $route = $attributes[0]->newInstance();

        self::assertSame('/api/dashboard/sse', $route->getPath());
        self::assertSame(['GET'], $route->getMethods());
    }

    public function testDashboardReturnsHttpOkAndExpectedKpiKeys(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnCallback(
            fn (string $class): ObjectRepository => new DashboardSseRepositoryStub($class)
        );

        $beneficiairesQuery = $this->createScalarQuery(128540);
        $montantMobiliseQuery = $this->createScalarQuery(287450000);
        $montantDecaisseQuery = $this->createScalarQuery(153320000);
        $entityManager->method('createQuery')->willReturnOnConsecutiveCalls(
            $beneficiairesQuery,
            $montantMobiliseQuery,
            $montantDecaisseQuery
        );

        $response = (new DashboardSseController($entityManager))();
        $payload = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        $expectedKeys = [
            'programmes',
            'zers',
            'localites',
            'projets',
            'beneficiaires',
            'tauxElectrification',
            'montantMobilise',
            'montantDecaisse',
            'tauxDecaissement',
            'alertesCritiques',
        ];

        foreach ($expectedKeys as $key) {
            self::assertArrayHasKey($key, $payload);
        }
        self::assertSame(64.4, $payload['tauxElectrification']);
        self::assertSame(53.3, $payload['tauxDecaissement']);
    }

    private function createScalarQuery(int|float $value): Query
    {
        $query = $this->createMock(Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('getSingleScalarResult')->willReturn($value);

        return $query;
    }
}

/** @implements ObjectRepository<object> */
final class DashboardSseRepositoryStub implements ObjectRepository
{
    public function __construct(private readonly string $className)
    {
    }

    /** @param array<string, mixed> $criteria */
    public function count(array $criteria): int
    {
        if ($this->className === Localite::class && $criteria === []) {
            return 45;
        }
        if ($this->className === Localite::class && ($criteria['statutElectrification'] ?? null) === 'ELECTRIFIEE') {
            return 29;
        }
        if ($this->className === ObservationSuivi::class) {
            return 12;
        }

        return match ($this->className) {
            \App\Entity\ProgrammePner::class => 5,
            \App\Entity\Zer::class => 7,
            \App\Entity\ProjetElectrification::class => 18,
            default => 0,
        };
    }

    public function find(mixed $id): ?object
    {
        return null;
    }

    public function findAll(): array
    {
        return [];
    }

    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return [];
    }

    public function findOneBy(array $criteria): ?object
    {
        return null;
    }

    public function getClassName(): string
    {
        return $this->className;
    }
}
