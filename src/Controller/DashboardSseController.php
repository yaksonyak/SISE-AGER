<?php

namespace App\Controller;

use App\Entity\BeneficiaireGenre;
use App\Entity\ConventionFinancement;
use App\Entity\Decaissement;
use App\Entity\Localite;
use App\Entity\ObservationSuivi;
use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Entity\Zer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DashboardSseController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/api/dashboard/sse', name: 'api_dashboard_sse', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $programmes = $this->count(ProgrammePner::class);
        $zers = $this->count(Zer::class);
        $localites = $this->count(Localite::class);
        $localitesElectrifiees = $this->count(Localite::class, ['statutElectrification' => 'ELECTRIFIEE']);
        $projets = $this->count(ProjetElectrification::class);
        $beneficiaires = $this->sum(sprintf('SELECT COALESCE(SUM(b.nombreHommes + b.nombreFemmes), 0) FROM %s b', BeneficiaireGenre::class));
        $montantMobilise = $this->sum(sprintf('SELECT COALESCE(SUM(c.montantUsd), 0) FROM %s c', ConventionFinancement::class));
        $montantDecaisse = $this->sum(
            sprintf('SELECT COALESCE(SUM(d.montantUsd), 0) FROM %s d WHERE d.statut = :statut', Decaissement::class),
            ['statut' => Decaissement::STATUT_EFFECTUE]
        );
        $alertesCritiques = $this->count(ObservationSuivi::class, ['niveauCriticite' => ObservationSuivi::NIVEAU_CRITIQUE]);

        return new JsonResponse([
            'programmes' => $programmes,
            'zers' => $zers,
            'localites' => $localites,
            'projets' => $projets,
            'beneficiaires' => (int) $beneficiaires,
            'tauxElectrification' => $this->percentage($localitesElectrifiees, $localites),
            'montantMobilise' => $this->number($montantMobilise),
            'montantDecaisse' => $this->number($montantDecaisse),
            'tauxDecaissement' => $this->percentage($montantDecaisse, $montantMobilise),
            'alertesCritiques' => $alertesCritiques,
        ]);
    }

    /** @param class-string $entityClass */
    private function count(string $entityClass, array $criteria = []): int
    {
        return $this->entityManager->getRepository($entityClass)->count($criteria);
    }

    /** @param array<string, mixed> $parameters */
    private function sum(string $dql, array $parameters = []): float
    {
        $query = $this->entityManager->createQuery($dql);
        foreach ($parameters as $name => $value) {
            $query->setParameter($name, $value);
        }

        return (float) $query->getSingleScalarResult();
    }

    private function percentage(float|int $value, float|int $total): float
    {
        if ((float) $total === 0.0) {
            return 0.0;
        }

        return round(((float) $value / (float) $total) * 100, 1);
    }

    private function number(float $value): int|float
    {
        $rounded = round($value, 2);

        return floor($rounded) === $rounded ? (int) $rounded : $rounded;
    }
}
