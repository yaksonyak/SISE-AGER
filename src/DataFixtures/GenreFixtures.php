<?php

namespace App\DataFixtures;

use App\Entity\ActionGenre;
use App\Entity\BeneficiaireGenre;
use App\Entity\ComiteGenre;
use App\Entity\FormationGenre;
use App\Entity\IndicateurGenre;
use App\Entity\Localite;
use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Entity\Zer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class GenreFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $programme = $manager->getRepository(ProgrammePner::class)->findOneBy(['code' => 'PUERG']);
        $projet = $manager->getRepository(ProjetElectrification::class)->findOneBy(['code' => 'PRJ-PUERG-KDA-001']);
        $zer = $manager->getRepository(Zer::class)->findOneBy(['code' => 'ZER-BG']);
        $localite = $manager->getRepository(Localite::class)->findOneBy(['code' => 'LOC-SOU-001']);

        if (!$programme || !$projet || !$zer || !$localite) {
            return;
        }

        $action = $this->upsertAction($manager, $programme, $projet, $zer, $localite);
        $this->upsertBeneficiaire($manager, $action, $projet, $localite);
        $this->upsertFormation($manager, $action, $zer, $localite);
        $this->upsertIndicateur($manager, $action, $projet);
        $this->upsertComite($manager, $zer, $localite);

        $manager->flush();
    }

    /** @return array<class-string<Fixture>> */
    public function getDependencies(): array
    {
        return [PnerReferentialFixtures::class, ProjetElectrificationFixtures::class];
    }

    private function upsertAction(ObjectManager $manager, ProgrammePner $programme, ProjetElectrification $projet, Zer $zer, Localite $localite): ActionGenre
    {
        $action = $manager->getRepository(ActionGenre::class)->findOneBy(['code' => 'GEN-PUERG-KDA-001']) ?? new ActionGenre();
        $action
            ->setCode('GEN-PUERG-KDA-001')
            ->setTitre('Intégration genre du projet pilote PUERG Kindia')
            ->setDescription('Action de démonstration pour l’intégration du genre, l’inclusion et la participation communautaire dans le PNER.')
            ->setAxeStrategique(ActionGenre::AXE_INTEGRATION_PROJETS)
            ->setProgrammePner($programme)
            ->setProjetElectrification($projet)
            ->setZer($zer)
            ->setLocalite($localite)
            ->setStatut(ActionGenre::STATUT_EN_COURS)
            ->setDateDebutPrevue(new \DateTimeImmutable('2024-01-15'))
            ->setDateFinPrevue(new \DateTimeImmutable('2024-12-31'))
            ->setResponsable('Cellule Genre AGER');

        $manager->persist($action);

        return $action;
    }

    private function upsertBeneficiaire(ObjectManager $manager, ActionGenre $action, ProjetElectrification $projet, Localite $localite): BeneficiaireGenre
    {
        $beneficiaire = $manager->getRepository(BeneficiaireGenre::class)->findOneBy(['actionGenre' => $action, 'categorieBeneficiaire' => BeneficiaireGenre::CATEGORIE_MENAGES]) ?? new BeneficiaireGenre();
        $beneficiaire
            ->setActionGenre($action)
            ->setProjetElectrification($projet)
            ->setLocalite($localite)
            ->setCategorieBeneficiaire(BeneficiaireGenre::CATEGORIE_MENAGES)
            ->setNombreHommes(520)
            ->setNombreFemmes(600)
            ->setNombreJeunes(310)
            ->setNombrePersonnesVulnerables(85)
            ->setCommentaire('Bénéficiaires de démonstration suivis dans la localité pilote.');

        $manager->persist($beneficiaire);

        return $beneficiaire;
    }

    private function upsertFormation(ObjectManager $manager, ActionGenre $action, Zer $zer, Localite $localite): FormationGenre
    {
        $formation = $manager->getRepository(FormationGenre::class)->findOneBy(['code' => 'FORM-GEN-KDA-001']) ?? new FormationGenre();
        $formation
            ->setCode('FORM-GEN-KDA-001')
            ->setIntitule('Sensibilisation genre et accès productif à l’électricité')
            ->setActionGenre($action)
            ->setZer($zer)
            ->setLocalite($localite)
            ->setDateFormation(new \DateTimeImmutable('2024-03-20'))
            ->setNombreParticipants(45)
            ->setNombreFemmes(28)
            ->setNombreHommes(17)
            ->setTheme('Genre, inclusion et usages productifs de l’électricité rurale')
            ->setFormateur('Équipe inclusion AGER')
            ->setCommentaire('Session de démonstration pour les fixtures du module Genre / Inclusion.');

        $manager->persist($formation);

        return $formation;
    }

    private function upsertIndicateur(ObjectManager $manager, ActionGenre $action, ProjetElectrification $projet): IndicateurGenre
    {
        $indicateur = $manager->getRepository(IndicateurGenre::class)->findOneBy(['code' => 'IND-GEN-FEMMES-FORMEES']) ?? new IndicateurGenre();
        $indicateur
            ->setCode('IND-GEN-FEMMES-FORMEES')
            ->setLibelle('Femmes formées aux usages productifs de l’électricité')
            ->setDescription('Indicateur de démonstration pour suivre l’inclusion des femmes dans les activités PNER.')
            ->setActionGenre($action)
            ->setProjetElectrification($projet)
            ->setValeurReference(0.0)
            ->setValeurCible(150.0)
            ->setValeurActuelle(28.0)
            ->setUnite('personnes');

        $manager->persist($indicateur);

        return $indicateur;
    }

    private function upsertComite(ObjectManager $manager, Zer $zer, Localite $localite): ComiteGenre
    {
        $comite = $manager->getRepository(ComiteGenre::class)->findOneBy(['code' => 'COM-GEN-KDA-001']) ?? new ComiteGenre();
        $comite
            ->setCode('COM-GEN-KDA-001')
            ->setNom('Comité local genre et inclusion de Koliady')
            ->setTypeComite(ComiteGenre::TYPE_COMITE_LOCAL)
            ->setZer($zer)
            ->setLocalite($localite)
            ->setDateMiseEnPlace(new \DateTimeImmutable('2024-02-10'))
            ->setNombreMembres(12)
            ->setNombreFemmes(7)
            ->setNombreHommes(5)
            ->setStatut(ComiteGenre::STATUT_FONCTIONNEL)
            ->setCommentaire('Comité local de démonstration pour le suivi genre du PNER.');

        $manager->persist($comite);

        return $comite;
    }
}
