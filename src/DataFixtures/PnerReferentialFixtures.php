<?php

namespace App\DataFixtures;

use App\Entity\Localite;
use App\Entity\Prefecture;
use App\Entity\ProgrammePner;
use App\Entity\SousPrefecture;
use App\Entity\SystemeElectrification;
use App\Entity\Zer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PnerReferentialFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $puerg = $this->upsertProgramme($manager, 'PUERG', 'Programme d’Urgence d’Électrification Rurale de Guinée', 2023, 2027, 1_200, 35.0, 150_000_000.0);
        $permt = $this->upsertProgramme($manager, 'PERMT', 'Programme d’Électrification Rurale à Moyen Terme', 2028, 2033, 2_400, 55.0, 320_000_000.0);
        $pfauer = $this->upsertProgramme($manager, 'PFAUER', 'Programme Final d’Accès Universel à l’Électricité Rurale', 2034, 2040, 3_800, 100.0, 610_000_000.0);

        $extensionReseau = $this->upsertSysteme(
            $manager,
            'EXT-30KV',
            'Extension réseau MT 30 kV',
            SystemeElectrification::TYPE_EXTENSION_RESEAU_MT_30KV,
            30.0,
            20.0,
            'Réseau électrique interconnecté'
        );
        $miniReseauSolaire = $this->upsertSysteme(
            $manager,
            'PV-CENTRALISE',
            'Mini-réseau solaire PV centralisé',
            SystemeElectrification::TYPE_SOLAIRE_PV_CENTRALISE,
            null,
            5.0,
            'Solaire photovoltaïque'
        );
        $hybridePvHydro = $this->upsertSysteme(
            $manager,
            'HYBRIDE-PV-HYDRO',
            'Système hybride solaire PV et petite hydro',
            SystemeElectrification::TYPE_HYBRIDE_PV_HYDRO,
            null,
            8.0,
            'Solaire photovoltaïque et hydroélectricité'
        );

        $zerBasseGuinee = $this->upsertZer($manager, 'ZER-BG', 'ZER Basse Guinée', 36_200.0, 1_850_000, 285_000, 2_450, 62.0, 'Élevé', 'Moyen');
        $zerMoyenneGuinee = $this->upsertZer($manager, 'ZER-MG', 'ZER Moyenne Guinée', 58_400.0, 1_420_000, 218_000, 3_120, 71.0, 'Moyen', 'Élevé');

        $kindia = $this->upsertPrefecture($manager, 'KDA', 'Kindia', $zerBasseGuinee);
        $mamou = $this->upsertPrefecture($manager, 'MAM', 'Mamou', $zerMoyenneGuinee);

        $sougueta = $this->upsertSousPrefecture($manager, 'KDA-SOU', 'Souguéta', $kindia);
        $dounet = $this->upsertSousPrefecture($manager, 'MAM-DOU', 'Dounet', $mamou);

        $this->upsertLocalite($manager, 'LOC-SOU-001', 'Koliady', 9.7533, -13.0167, 185, 1_120, 'PLUS_800_HABITANTS', 'NON_ELECTRIFIEE', 18.5, $kindia, $sougueta, $zerBasseGuinee, $puerg, $extensionReseau);
        $this->upsertLocalite($manager, 'LOC-SOU-002', 'Fodéya', 9.7062, -12.9581, 96, 640, 'MOINS_800_HABITANTS', 'NON_ELECTRIFIEE', 42.0, $kindia, $sougueta, $zerBasseGuinee, $permt, $miniReseauSolaire);
        $this->upsertLocalite($manager, 'LOC-DOU-001', 'Hafia Dounet', 10.3154, -12.1075, 142, 910, 'PLUS_800_HABITANTS', 'NON_ELECTRIFIEE', 36.2, $mamou, $dounet, $zerMoyenneGuinee, $pfauer, $hybridePvHydro);

        $manager->flush();
    }

    private function upsertProgramme(ObjectManager $manager, string $code, string $nom, int $debut, int $fin, int $localites, float $taux, float $montant): ProgrammePner
    {
        $programme = $manager->getRepository(ProgrammePner::class)->findOneBy(['code' => $code]) ?? new ProgrammePner();
        $programme
            ->setCode($code)
            ->setNom($nom)
            ->setPeriodeDebut($debut)
            ->setPeriodeFin($fin)
            ->setNombreLocalitesPrevues($localites)
            ->setTauxElectrificationCible($taux)
            ->setMontantPrevisionnelUsd($montant)
            ->setDescription(sprintf('Programme de référence PNER Horizon 2040 pour la période %d-%d.', $debut, $fin));

        $manager->persist($programme);

        return $programme;
    }

    private function upsertSysteme(ObjectManager $manager, string $code, string $nom, string $type, ?float $tensionKv, ?float $rayonKm, string $sourceEnergie): SystemeElectrification
    {
        $systeme = $manager->getRepository(SystemeElectrification::class)->findOneBy(['code' => $code]) ?? new SystemeElectrification();
        $systeme
            ->setCode($code)
            ->setNom($nom)
            ->setType($type)
            ->setTensionKv($tensionKv)
            ->setRayonKm($rayonKm)
            ->setSourceEnergie($sourceEnergie)
            ->setDescription('Système d’électrification de démonstration pour le référentiel PNER.');

        $manager->persist($systeme);

        return $systeme;
    }

    private function upsertZer(ObjectManager $manager, string $code, string $nom, float $superficie, int $population, int $menages, int $localites, float $tauxMoins800, string $potentielSolaire, string $potentielHydro): Zer
    {
        $zer = $manager->getRepository(Zer::class)->findOneBy(['code' => $code]) ?? new Zer();
        $zer
            ->setCode($code)
            ->setNom($nom)
            ->setSuperficieKm2($superficie)
            ->setPopulation($population)
            ->setNombreMenages($menages)
            ->setNombreLocalites($localites)
            ->setTauxLocalitesMoins800Habitants($tauxMoins800)
            ->setPotentielSolaire($potentielSolaire)
            ->setPotentielHydro($potentielHydro)
            ->setDescription('Zone d’Électrification Rurale de démonstration pour le SIG-ER et le suivi-évaluation SSE.');

        $manager->persist($zer);

        return $zer;
    }

    private function upsertPrefecture(ObjectManager $manager, string $code, string $nom, Zer $zer): Prefecture
    {
        $prefecture = $manager->getRepository(Prefecture::class)->findOneBy(['code' => $code]) ?? new Prefecture();
        $prefecture->setCode($code)->setNom($nom)->setZer($zer);
        $manager->persist($prefecture);

        return $prefecture;
    }

    private function upsertSousPrefecture(ObjectManager $manager, string $code, string $nom, Prefecture $prefecture): SousPrefecture
    {
        $sousPrefecture = $manager->getRepository(SousPrefecture::class)->findOneBy(['code' => $code]) ?? new SousPrefecture();
        $sousPrefecture->setCode($code)->setNom($nom)->setPrefecture($prefecture);
        $manager->persist($sousPrefecture);

        return $sousPrefecture;
    }

    private function upsertLocalite(
        ObjectManager $manager,
        string $code,
        string $nom,
        float $latitude,
        float $longitude,
        int $nombreMenages,
        int $populationTotale,
        string $categoriePopulation,
        string $statutElectrification,
        float $distanceReseauKm,
        Prefecture $prefecture,
        SousPrefecture $sousPrefecture,
        Zer $zer,
        ProgrammePner $programmePner,
        SystemeElectrification $systemeElectrification,
    ): Localite {
        $localite = $manager->getRepository(Localite::class)->findOneBy(['code' => $code]) ?? new Localite();
        $localite
            ->setCode($code)
            ->setNom($nom)
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setNombreMenages($nombreMenages)
            ->setPopulationTotale($populationTotale)
            ->setCategoriePopulation($categoriePopulation)
            ->setStatutElectrification($statutElectrification)
            ->setDistanceReseauKm($distanceReseauKm)
            ->setPrefecture($prefecture)
            ->setSousPrefecture($sousPrefecture)
            ->setZer($zer)
            ->setProgrammePner($programmePner)
            ->setSystemeElectrification($systemeElectrification);

        $manager->persist($localite);

        return $localite;
    }
}
