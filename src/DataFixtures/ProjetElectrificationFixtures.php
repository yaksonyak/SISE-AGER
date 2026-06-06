<?php

namespace App\DataFixtures;

use App\Entity\ActiviteProjet;
use App\Entity\Localite;
use App\Entity\PhaseProjet;
use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Entity\ProjetLocalite;
use App\Entity\SystemeElectrification;
use App\Entity\Zer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProjetElectrificationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $programme = $manager->getRepository(ProgrammePner::class)->findOneBy(['code' => 'PUERG']);
        $zer = $manager->getRepository(Zer::class)->findOneBy(['code' => 'ZER-BG']);
        $systeme = $manager->getRepository(SystemeElectrification::class)->findOneBy(['code' => 'EXT-30KV']);

        if (!$programme instanceof ProgrammePner) {
            return;
        }

        $projet = $this->upsertProjet($manager, $programme, $zer, $systeme);

        foreach (['LOC-SOU-001', 'LOC-SOU-002'] as $index => $codeLocalite) {
            $localite = $manager->getRepository(Localite::class)->findOneBy(['code' => $codeLocalite]);
            if ($localite instanceof Localite) {
                $this->upsertProjetLocalite($manager, $projet, $localite, $index);
            }
        }

        $phasePreparation = $this->upsertPhase($manager, $projet, 'PREP', 'Préparation et études', 1, PhaseProjet::STATUT_EN_COURS, '2024-01-15', '2024-06-30');
        $phaseTravaux = $this->upsertPhase($manager, $projet, 'TRVX', 'Travaux et raccordements', 2, PhaseProjet::STATUT_PLANIFIE, '2024-07-01', '2025-12-31');

        $this->upsertActivite($manager, $phasePreparation, 'PREP-APS', 'Réaliser les études APS/APD', ActiviteProjet::STATUT_EN_COURS, 45.0, '2024-01-15', '2024-04-30', 'Cellule études AGER');
        $this->upsertActivite($manager, $phasePreparation, 'PREPDAO', 'Préparer le dossier d’appel d’offres', ActiviteProjet::STATUT_PLANIFIE, 10.0, '2024-05-01', '2024-06-30', 'Cellule passation des marchés');
        $this->upsertActivite($manager, $phaseTravaux, 'TRVX-LIGNE', 'Construire les extensions MT/BT', ActiviteProjet::STATUT_PLANIFIE, 0.0, '2024-07-01', '2025-09-30', 'Entreprise travaux');

        $manager->flush();
    }

    /**
     * @return array<class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [PnerReferentialFixtures::class];
    }

    private function upsertProjet(ObjectManager $manager, ProgrammePner $programme, ?Zer $zer, ?SystemeElectrification $systeme): ProjetElectrification
    {
        $projet = $manager->getRepository(ProjetElectrification::class)->findOneBy(['code' => 'PRJ-PUERG-KDA-001']) ?? new ProjetElectrification();
        $projet
            ->setCode('PRJ-PUERG-KDA-001')
            ->setIntitule('Électrification rurale prioritaire de la zone de Kindia')
            ->setDescription('Projet démonstrateur du module de gestion des projets d’électrification rurale du PNER.')
            ->setProgrammePner($programme)
            ->setZer($zer)
            ->setSystemeElectrification($systeme)
            ->setStatut(ProjetElectrification::STATUT_EN_PREPARATION)
            ->setDateDebutPrevue(new \DateTimeImmutable('2024-01-15'))
            ->setDateFinPrevue(new \DateTimeImmutable('2025-12-31'))
            ->setMontantPrevisionnelUsd(12_500_000.0)
            ->setMontantMobiliseUsd(7_800_000.0)
            ->setSourceFinancement('Budget national et partenaires techniques et financiers')
            ->setMaitreOuvrage('AGER')
            ->setPartenaireTechnique('Unité de coordination PUERG');

        $manager->persist($projet);

        return $projet;
    }

    private function upsertProjetLocalite(ObjectManager $manager, ProjetElectrification $projet, Localite $localite, int $index): ProjetLocalite
    {
        $projetLocalite = $manager->getRepository(ProjetLocalite::class)->findOneBy([
            'projetElectrification' => $projet,
            'localite' => $localite,
        ]) ?? new ProjetLocalite();

        $projetLocalite
            ->setProjetElectrification($projet)
            ->setLocalite($localite)
            ->setStatutLocalite($index === 0 ? ProjetLocalite::STATUT_EN_TRAVAUX : ProjetLocalite::STATUT_A_ELECTRIFIER)
            ->setDateRaccordementPrevue(new \DateTimeImmutable($index === 0 ? '2025-03-31' : '2025-06-30'))
            ->setCommentaire('Localité pilote associée au projet PUERG de démonstration.');

        $manager->persist($projetLocalite);

        return $projetLocalite;
    }

    private function upsertPhase(ObjectManager $manager, ProjetElectrification $projet, string $code, string $nom, int $ordre, string $statut, string $debut, string $fin): PhaseProjet
    {
        $phase = $manager->getRepository(PhaseProjet::class)->findOneBy([
            'projetElectrification' => $projet,
            'code' => $code,
        ]) ?? new PhaseProjet();

        $phase
            ->setProjetElectrification($projet)
            ->setCode($code)
            ->setNom($nom)
            ->setOrdre($ordre)
            ->setStatut($statut)
            ->setDateDebutPrevue(new \DateTimeImmutable($debut))
            ->setDateFinPrevue(new \DateTimeImmutable($fin));

        $manager->persist($phase);

        return $phase;
    }

    private function upsertActivite(ObjectManager $manager, PhaseProjet $phase, string $code, string $libelle, string $statut, float $tauxExecution, string $debut, string $fin, string $responsable): ActiviteProjet
    {
        $activite = $manager->getRepository(ActiviteProjet::class)->findOneBy([
            'phaseProjet' => $phase,
            'libelle' => $libelle,
        ]) ?? new ActiviteProjet();

        $activite
            ->setPhaseProjet($phase)
            ->setLibelle($libelle)
            ->setDescription(sprintf('Activité %s du projet pilote PUERG.', $code))
            ->setStatut($statut)
            ->setTauxExecution($tauxExecution)
            ->setDateDebutPrevue(new \DateTimeImmutable($debut))
            ->setDateFinPrevue(new \DateTimeImmutable($fin))
            ->setResponsable($responsable);

        $manager->persist($activite);

        return $activite;
    }
}
