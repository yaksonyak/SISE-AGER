<?php

namespace App\DataFixtures;

use App\Entity\DonneeGeospatialeLocalite;
use App\Entity\InfrastructureElectrique;
use App\Entity\Localite;
use App\Entity\PointGps;
use App\Entity\SiteEnergetique;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GisFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $localite = $manager->getRepository(Localite::class)->findOneBy(['code' => 'LOC-SOU-001']);

        if (!$localite instanceof Localite) {
            return;
        }

        $pointPoste = $this->upsertPointGps($manager, 9.7533, -13.0167, 74.0, 4.5, 'Collecte GPS terrain AGER');
        $pointCentrale = $this->upsertPointGps($manager, 9.7581, -13.0224, 78.0, 3.2, 'SIG-ER');

        $this->upsertInfrastructure($manager, 'INF-KDA-PS-001', 'Poste source rural de Koliady', InfrastructureElectrique::TYPE_POSTE_SOURCE, 'EN_SERVICE', $pointPoste, $localite);
        $this->upsertInfrastructure($manager, 'INF-KDA-MR-001', 'Mini-réseau de Koliady', InfrastructureElectrique::TYPE_MINI_RESEAU, 'PLANIFIE', $pointCentrale, $localite);
        $this->upsertSiteEnergetique($manager, 'SITE-KDA-PV-001', 'Centrale solaire PV de Koliady', SiteEnergetique::TYPE_SOLAIRE, 150.0, 'PLANIFIE', $pointCentrale, $localite);
        $this->upsertDonneeGeospatiale($manager, $localite, 18.6, 1_120, 185, 60.2);

        $manager->flush();
    }

    /**
     * @return array<class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [PnerReferentialFixtures::class];
    }

    private function upsertPointGps(ObjectManager $manager, float $latitude, float $longitude, ?float $altitude, ?float $precisionGps, string $source): PointGps
    {
        $pointGps = $manager->getRepository(PointGps::class)->findOneBy([
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]) ?? new PointGps();

        $pointGps
            ->setLatitude($latitude)
            ->setLongitude($longitude)
            ->setAltitude($altitude)
            ->setPrecisionGps($precisionGps)
            ->setSource($source);

        $manager->persist($pointGps);

        return $pointGps;
    }

    private function upsertInfrastructure(ObjectManager $manager, string $code, string $nom, string $type, string $statut, PointGps $pointGps, Localite $localite): InfrastructureElectrique
    {
        $infrastructure = $manager->getRepository(InfrastructureElectrique::class)->findOneBy(['code' => $code]) ?? new InfrastructureElectrique();
        $infrastructure
            ->setCode($code)
            ->setNom($nom)
            ->setTypeInfrastructure($type)
            ->setStatut($statut)
            ->setPointGps($pointGps)
            ->setLocalite($localite)
            ->setDescription('Infrastructure électrique de démonstration pour la cartographie PNER.');

        $manager->persist($infrastructure);

        return $infrastructure;
    }

    private function upsertSiteEnergetique(ObjectManager $manager, string $code, string $nom, string $type, float $puissanceKw, string $statut, PointGps $pointGps, Localite $localite): SiteEnergetique
    {
        $site = $manager->getRepository(SiteEnergetique::class)->findOneBy(['code' => $code]) ?? new SiteEnergetique();
        $site
            ->setCode($code)
            ->setNom($nom)
            ->setTypeSite($type)
            ->setPuissanceInstalleeKw($puissanceKw)
            ->setStatut($statut)
            ->setPointGps($pointGps)
            ->setLocalite($localite)
            ->setCommentaire('Site énergétique pilote pour l’intégration SIG-ER.');

        $manager->persist($site);

        return $site;
    }

    private function upsertDonneeGeospatiale(ObjectManager $manager, Localite $localite, float $superficie, int $population, int $menages, float $densite): DonneeGeospatialeLocalite
    {
        $donnee = $manager->getRepository(DonneeGeospatialeLocalite::class)->findOneBy(['localite' => $localite]) ?? new DonneeGeospatialeLocalite();
        $donnee
            ->setLocalite($localite)
            ->setSuperficieKm2($superficie)
            ->setPopulationReference($population)
            ->setMenagesReference($menages)
            ->setDensitePopulation($densite)
            ->setObservations('Données géospatiales de démonstration issues du SIG-ER.');

        $manager->persist($donnee);

        return $donnee;
    }
}
