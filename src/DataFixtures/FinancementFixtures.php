<?php

namespace App\DataFixtures;

use App\Entity\BailleurFonds;
use App\Entity\ConventionFinancement;
use App\Entity\CoutPrevisionnel;
use App\Entity\Decaissement;
use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Entity\SourceFinancement;
use App\Entity\Zer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FinancementFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $programme = $manager->getRepository(ProgrammePner::class)->findOneBy(['code' => 'PUERG']);
        $zer = $manager->getRepository(Zer::class)->findOneBy(['code' => 'ZER-BG']);
        $projet = $manager->getRepository(ProjetElectrification::class)->findOneBy(['code' => 'PRJ-PUERG-KDA-001']);

        if (!$programme instanceof ProgrammePner) {
            return;
        }

        $etat = $this->upsertBailleur($manager, 'ETAT-GN', 'État guinéen', BailleurFonds::TYPE_ETAT, 'Guinée');
        $ptf = $this->upsertBailleur($manager, 'PTF-DEMO', 'Partenaire technique et financier de démonstration', BailleurFonds::TYPE_PTF, 'Multinational');

        $budgetEtat = $this->upsertSource($manager, 'BUDGET-ETAT-PNER', 'Budget national PNER', SourceFinancement::TYPE_BUDGET_ETAT, $etat);
        $donPtf = $this->upsertSource($manager, 'DON-PTF-PUERG', 'Don partenaire PUERG', SourceFinancement::TYPE_DON, $ptf);

        $convention = $this->upsertConvention($manager, $ptf, $donPtf, $programme, $projet);
        $this->upsertDecaissement($manager, $convention, $projet);
        $this->upsertCout($manager, $programme, $projet, $zer, CoutPrevisionnel::CATEGORIE_INFRASTRUCTURES, 8_500_000.0, 2024, 'Coûts prévisionnels des extensions et mini-réseaux pilotes.');
        $this->upsertCout($manager, $programme, $projet, $zer, CoutPrevisionnel::CATEGORIE_ETUDES, 650_000.0, 2024, 'Études techniques et environnementales préalables.');
        $this->upsertCout($manager, $programme, null, $zer, CoutPrevisionnel::CATEGORIE_SUIVI_EVALUATION, 300_000.0, 2024, 'Suivi-évaluation du portefeuille PUERG en Basse Guinée.');
        $this->upsertSource($manager, 'SUBV-AGER-PNER', 'Subvention AGER PNER', SourceFinancement::TYPE_SUBVENTION, $etat);
        $this->upsertConvention($manager, $etat, $budgetEtat, $programme, null, 'CONV-ETAT-PNER-2024', 'Convention de financement national PNER 2024', 5_000_000.0);

        $manager->flush();
    }

    /**
     * @return array<class-string<Fixture>>
     */
    public function getDependencies(): array
    {
        return [PnerReferentialFixtures::class, ProjetElectrificationFixtures::class];
    }

    private function upsertBailleur(ObjectManager $manager, string $code, string $nom, string $type, ?string $pays): BailleurFonds
    {
        $bailleur = $manager->getRepository(BailleurFonds::class)->findOneBy(['code' => $code]) ?? new BailleurFonds();
        $bailleur
            ->setCode($code)
            ->setNom($nom)
            ->setTypeBailleur($type)
            ->setPays($pays)
            ->setContactPrincipal('Point focal financement PNER')
            ->setEmail('financement@sise-ager.local')
            ->setTelephone('+224000000000')
            ->setDescription('Bailleur de fonds de démonstration pour le module financement PNER.');

        $manager->persist($bailleur);

        return $bailleur;
    }

    private function upsertSource(ObjectManager $manager, string $code, string $nom, string $type, BailleurFonds $bailleur): SourceFinancement
    {
        $source = $manager->getRepository(SourceFinancement::class)->findOneBy(['code' => $code]) ?? new SourceFinancement();
        $source
            ->setCode($code)
            ->setNom($nom)
            ->setTypeSource($type)
            ->setBailleurFonds($bailleur)
            ->setDescription('Source de financement de démonstration du PNER 2040.');

        $manager->persist($source);

        return $source;
    }

    private function upsertConvention(
        ObjectManager $manager,
        BailleurFonds $bailleur,
        SourceFinancement $source,
        ProgrammePner $programme,
        ?ProjetElectrification $projet,
        string $code = 'CONV-PTF-PUERG-001',
        string $intitule = 'Convention de financement du projet pilote PUERG Kindia',
        float $montant = 7_800_000.0,
    ): ConventionFinancement {
        $convention = $manager->getRepository(ConventionFinancement::class)->findOneBy(['code' => $code]) ?? new ConventionFinancement();
        $convention
            ->setCode($code)
            ->setIntitule($intitule)
            ->setBailleurFonds($bailleur)
            ->setSourceFinancement($source)
            ->setProgrammePner($programme)
            ->setProjetElectrification($projet)
            ->setMontantUsd($montant)
            ->setDateSignature(new \DateTimeImmutable('2024-02-15'))
            ->setDateDebut(new \DateTimeImmutable('2024-03-01'))
            ->setDateFin(new \DateTimeImmutable('2027-12-31'))
            ->setStatut(ConventionFinancement::STATUT_EN_EXECUTION)
            ->setDescription('Convention de démonstration pour le suivi des engagements PNER.');

        $manager->persist($convention);

        return $convention;
    }

    private function upsertDecaissement(ObjectManager $manager, ConventionFinancement $convention, ?ProjetElectrification $projet): Decaissement
    {
        $decaissement = $manager->getRepository(Decaissement::class)->findOneBy(['referencePaiement' => 'DEC-PUERG-2024-001']) ?? new Decaissement();
        $decaissement
            ->setConventionFinancement($convention)
            ->setProjetElectrification($projet)
            ->setMontantUsd(1_250_000.0)
            ->setDateDecaissement(new \DateTimeImmutable('2024-04-30'))
            ->setReferencePaiement('DEC-PUERG-2024-001')
            ->setObjet('Premier décaissement pour études, mobilisation et démarrage des travaux')
            ->setStatut(Decaissement::STATUT_EFFECTUE);

        $manager->persist($decaissement);

        return $decaissement;
    }

    private function upsertCout(ObjectManager $manager, ProgrammePner $programme, ?ProjetElectrification $projet, ?Zer $zer, string $categorie, float $montant, int $annee, string $commentaire): CoutPrevisionnel
    {
        $cout = $manager->getRepository(CoutPrevisionnel::class)->findOneBy([
            'programmePner' => $programme,
            'projetElectrification' => $projet,
            'zer' => $zer,
            'categorieCout' => $categorie,
            'annee' => $annee,
        ]) ?? new CoutPrevisionnel();

        $cout
            ->setProgrammePner($programme)
            ->setProjetElectrification($projet)
            ->setZer($zer)
            ->setCategorieCout($categorie)
            ->setMontantUsd($montant)
            ->setAnnee($annee)
            ->setCommentaire($commentaire);

        $manager->persist($cout);

        return $cout;
    }
}
