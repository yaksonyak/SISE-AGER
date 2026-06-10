<?php

namespace App\DataFixtures;

use App\Entity\IndicateurPner;
use App\Entity\Localite;
use App\Entity\ObservationSuivi;
use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Entity\RapportSuivi;
use App\Entity\ValeurIndicateur;
use App\Entity\Zer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SseFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $programme = $manager->getRepository(ProgrammePner::class)->findOneBy(['code' => 'PUERG']);
        $zer = $manager->getRepository(Zer::class)->findOneBy(['code' => 'ZER-BG']);
        $projet = $manager->getRepository(ProjetElectrification::class)->findOneBy(['code' => 'PRJ-PUERG-KDA-001']);
        $localite = $manager->getRepository(Localite::class)->findOneBy(['code' => 'LOC-SOU-001']);

        if (!$programme instanceof ProgrammePner) {
            return;
        }

        $indicateurAcces = $this->upsertIndicateur(
            $manager,
            'SSE-ACCES-001',
            'Taux d’accès à l’électricité rurale',
            IndicateurPner::TYPE_ACCES_ELECTRICITE,
            '%',
            18.0,
            35.0,
            IndicateurPner::FREQUENCE_TRIMESTRIELLE,
            'SIG-ER et enquêtes SSE'
        );
        $indicateurLocalites = $this->upsertIndicateur(
            $manager,
            'SSE-LOC-001',
            'Nombre de localités électrifiées',
            IndicateurPner::TYPE_LOCALITES_ELECTRIFIEES,
            'localités',
            0.0,
            1200.0,
            IndicateurPner::FREQUENCE_TRIMESTRIELLE,
            'Rapports projets AGER'
        );

        $this->upsertValeur($manager, $indicateurAcces, $programme, $zer, $projet, $localite, 'T1', 2024, 21.5, 'Progression initiale mesurée dans la zone pilote.');
        $this->upsertValeur($manager, $indicateurLocalites, $programme, $zer, $projet, null, 'T1', 2024, 2.0, 'Deux localités intégrées dans le portefeuille PUERG pilote.');

        $rapport = $this->upsertRapport($manager, $programme, $zer, $projet);
        $this->upsertObservation($manager, $rapport, $projet, $localite);

        $manager->flush();
    }

    /**
     * @return array<class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [PnerReferentialFixtures::class, ProjetElectrificationFixtures::class];
    }

    private function upsertIndicateur(ObjectManager $manager, string $code, string $libelle, string $type, string $unite, ?float $reference, ?float $cible, string $frequence, string $source): IndicateurPner
    {
        $indicateur = $manager->getRepository(IndicateurPner::class)->findOneBy(['code' => $code]) ?? new IndicateurPner();
        $indicateur
            ->setCode($code)
            ->setLibelle($libelle)
            ->setDescription('Indicateur de démonstration pour le suivi-évaluation du PNER Horizon 2040.')
            ->setTypeIndicateur($type)
            ->setUnite($unite)
            ->setValeurReference($reference)
            ->setValeurCible($cible)
            ->setFrequenceSuivi($frequence)
            ->setSourceDonnee($source);

        $manager->persist($indicateur);

        return $indicateur;
    }

    private function upsertValeur(ObjectManager $manager, IndicateurPner $indicateur, ProgrammePner $programme, ?Zer $zer, ?ProjetElectrification $projet, ?Localite $localite, string $periode, int $annee, float $valeur, string $commentaire): ValeurIndicateur
    {
        $valeurIndicateur = $manager->getRepository(ValeurIndicateur::class)->findOneBy([
            'indicateurPner' => $indicateur,
            'programmePner' => $programme,
            'periode' => $periode,
            'annee' => $annee,
        ]) ?? new ValeurIndicateur();

        $valeurIndicateur
            ->setIndicateurPner($indicateur)
            ->setProgrammePner($programme)
            ->setZer($zer)
            ->setProjetElectrification($projet)
            ->setLocalite($localite)
            ->setPeriode($periode)
            ->setAnnee($annee)
            ->setValeur($valeur)
            ->setCommentaire($commentaire);

        $manager->persist($valeurIndicateur);

        return $valeurIndicateur;
    }

    private function upsertRapport(ObjectManager $manager, ProgrammePner $programme, ?Zer $zer, ?ProjetElectrification $projet): RapportSuivi
    {
        $rapport = $manager->getRepository(RapportSuivi::class)->findOneBy(['code' => 'RPT-SSE-PUERG-2024-T1']) ?? new RapportSuivi();
        $rapport
            ->setCode('RPT-SSE-PUERG-2024-T1')
            ->setTitre('Rapport trimestriel SSE PUERG - T1 2024')
            ->setTypeRapport(RapportSuivi::TYPE_RAPPORT_TRIMESTRIEL)
            ->setProgrammePner($programme)
            ->setZer($zer)
            ->setProjetElectrification($projet)
            ->setPeriodeDebut(new \DateTimeImmutable('2024-01-01'))
            ->setPeriodeFin(new \DateTimeImmutable('2024-03-31'))
            ->setResume('Rapport de démonstration du dispositif de suivi-évaluation axé sur les résultats.')
            ->setRecommandations('Renforcer la collecte terrain et consolider les données SIG-ER.');

        $manager->persist($rapport);

        return $rapport;
    }

    private function upsertObservation(ObjectManager $manager, RapportSuivi $rapport, ?ProjetElectrification $projet, ?Localite $localite): ObservationSuivi
    {
        $observation = $manager->getRepository(ObservationSuivi::class)->findOneBy([
            'rapportSuivi' => $rapport,
            'titre' => 'Synchronisation des données terrain à améliorer',
        ]) ?? new ObservationSuivi();

        $observation
            ->setRapportSuivi($rapport)
            ->setProjetElectrification($projet)
            ->setLocalite($localite)
            ->setTitre('Synchronisation des données terrain à améliorer')
            ->setDescription('Les relevés terrain doivent être synchronisés plus régulièrement avec le SIG-ER pour fiabiliser les indicateurs SSE.')
            ->setNiveauCriticite(ObservationSuivi::NIVEAU_MOYEN)
            ->setStatut(ObservationSuivi::STATUT_OUVERTE);

        $manager->persist($observation);

        return $observation;
    }
}
