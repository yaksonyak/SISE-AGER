<?php

namespace App\Service;

use App\Entity\ActionGenre;
use App\Entity\BailleurFonds;
use App\Entity\BeneficiaireGenre;
use App\Entity\ComiteGenre;
use App\Entity\ConventionFinancement;
use App\Entity\CoutPrevisionnel;
use App\Entity\Decaissement;
use App\Entity\DonneeGeospatialeLocalite;
use App\Entity\FormationGenre;
use App\Entity\IndicateurPner;
use App\Entity\InfrastructureElectrique;
use App\Entity\IndicateurGenre;
use App\Entity\Localite;
use App\Entity\PointGps;
use App\Entity\Prefecture;
use App\Entity\ProgrammePner;
use App\Entity\ProjetElectrification;
use App\Entity\ProjetLocalite;
use App\Entity\SiteEnergetique;
use App\Entity\SourceFinancement;
use App\Entity\SousPrefecture;
use App\Entity\SystemeElectrification;
use App\Entity\ValeurIndicateur;
use App\Entity\Zer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class PnerDataImportService
{
    /** @var array<string, list<string>> */
    private const REQUIRED_COLUMNS = [
        'programmes_pner.csv' => ['code', 'nom', 'periode_debut', 'periode_fin'],
        'zers.csv' => ['code', 'nom'],
        'prefectures.csv' => ['code', 'nom', 'zer_code'],
        'sous_prefectures.csv' => ['code', 'nom', 'prefecture_code'],
        'localites.csv' => ['code', 'nom', 'longitude', 'latitude'],
        'systemes_electrification.csv' => ['code', 'nom', 'type', 'source_energie'],
        'projets_electrification.csv' => ['code', 'intitule', 'programme_code', 'statut', 'date_debut_prevue', 'date_fin_prevue'],
        'projet_localites.csv' => ['projet_code', 'localite_code', 'statut_localite'],
        'points_gps.csv' => ['point_code', 'latitude', 'longitude'],
        'infrastructures_electriques.csv' => ['code', 'nom', 'type_infrastructure', 'statut'],
        'sites_energetiques.csv' => ['code', 'nom', 'type_site', 'localite_code', 'point_code', 'statut'],
        'donnee_geospatiale_localites.csv' => ['localite_code'],
        'indicateurs_pner.csv' => ['code', 'libelle', 'type_indicateur', 'unite', 'frequence_suivi'],
        'valeurs_indicateurs.csv' => ['indicateur_code', 'periode', 'annee', 'valeur'],
        'actions_genre.csv' => ['code', 'titre', 'axe_strategique', 'statut'],
        'beneficiaires_genre.csv' => ['action_code', 'categorie_beneficiaire', 'nombre_hommes', 'nombre_femmes', 'nombre_jeunes', 'nombre_personnes_vulnerables'],
        'formations_genre.csv' => ['code', 'intitule', 'theme', 'nombre_participants', 'nombre_femmes', 'nombre_hommes'],
        'indicateurs_genre.csv' => ['code', 'libelle'],
        'comites_genre.csv' => ['code', 'nom', 'type_comite', 'nombre_membres', 'nombre_femmes', 'nombre_hommes', 'statut'],
        'bailleurs_fonds.csv' => ['code', 'nom', 'type_bailleur'],
        'sources_financement.csv' => ['code', 'nom', 'type_source'],
        'conventions_financement.csv' => ['code', 'intitule', 'bailleur_code', 'montant_usd', 'statut'],
        'decaissements.csv' => ['reference_paiement', 'convention_code', 'montant_usd', 'date_decaissement', 'statut'],
        'couts_previsionnels.csv' => ['categorie_cout', 'montant_usd'],
    ];

    /** @var array<string, string> */
    private const UNIQUE_COLUMNS = [
        'programmes_pner.csv' => 'code', 'zers.csv' => 'code', 'prefectures.csv' => 'code', 'sous_prefectures.csv' => 'code',
        'localites.csv' => 'code', 'systemes_electrification.csv' => 'code', 'projets_electrification.csv' => 'code',
        'points_gps.csv' => 'point_code', 'infrastructures_electriques.csv' => 'code', 'sites_energetiques.csv' => 'code', 'donnee_geospatiale_localites.csv' => 'localite_code',
        'indicateurs_pner.csv' => 'code', 'actions_genre.csv' => 'code', 'formations_genre.csv' => 'code', 'indicateurs_genre.csv' => 'code', 'comites_genre.csv' => 'code',
        'bailleurs_fonds.csv' => 'code', 'sources_financement.csv' => 'code', 'conventions_financement.csv' => 'code',
        'decaissements.csv' => 'reference_paiement',
    ];

    /** @var list<string> */
    private const IMPORT_ORDER = [
        'programmes_pner.csv', 'zers.csv', 'prefectures.csv', 'sous_prefectures.csv', 'systemes_electrification.csv', 'localites.csv',
        'projets_electrification.csv', 'projet_localites.csv', 'points_gps.csv', 'infrastructures_electriques.csv', 'sites_energetiques.csv', 'donnee_geospatiale_localites.csv',
        'indicateurs_pner.csv', 'valeurs_indicateurs.csv', 'actions_genre.csv', 'beneficiaires_genre.csv', 'formations_genre.csv', 'indicateurs_genre.csv', 'comites_genre.csv',
        'bailleurs_fonds.csv', 'sources_financement.csv', 'conventions_financement.csv', 'decaissements.csv', 'couts_previsionnels.csv',
    ];

    /** @var array<string, PointGps> */
    private array $pointGpsByCode = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /** @return list<string> */
    public function getExpectedFiles(): array
    {
        return self::IMPORT_ORDER;
    }

    /** @return array{files: array<string, array{rows:int, errors:list<string>}>, errors:list<string>} */
    public function validateImportFiles(?string $directory = null): array
    {
        $directory ??= $this->getImportDirectory();
        $result = ['files' => [], 'errors' => []];
        $codeMaps = [];

        foreach (self::IMPORT_ORDER as $fileName) {
            $path = $directory.'/'.$fileName;
            $result['files'][$fileName] = ['rows' => 0, 'errors' => []];

            if (!is_file($path)) {
                $result['files'][$fileName]['errors'][] = 'Fichier absent.';
                $result['errors'][] = sprintf('%s: fichier absent.', $fileName);
                continue;
            }

            try {
                $rows = $this->readCsv($path);
            } catch (\RuntimeException $exception) {
                $result['files'][$fileName]['errors'][] = $exception->getMessage();
                $result['errors'][] = sprintf('%s: %s', $fileName, $exception->getMessage());
                continue;
            }

            $result['files'][$fileName]['rows'] = count($rows);
            $headers = $rows === [] ? $this->readHeader($path) : array_keys($rows[0]);

            foreach (self::REQUIRED_COLUMNS[$fileName] as $requiredColumn) {
                if (!in_array($requiredColumn, $headers, true)) {
                    $message = sprintf('Colonne obligatoire manquante "%s".', $requiredColumn);
                    $result['files'][$fileName]['errors'][] = $message;
                    $result['errors'][] = sprintf('%s: %s', $fileName, $message);
                }
            }

            $uniqueColumn = self::UNIQUE_COLUMNS[$fileName] ?? null;
            if ($uniqueColumn !== null) {
                $seen = [];
                foreach ($rows as $line => $row) {
                    $code = $this->code($row[$uniqueColumn] ?? '');
                    if ($code === null) {
                        continue;
                    }
                    if (isset($seen[$code])) {
                        $message = sprintf('Doublon "%s" dans la colonne "%s" (lignes %d et %d).', $code, $uniqueColumn, $seen[$code], $line + 2);
                        $result['files'][$fileName]['errors'][] = $message;
                        $result['errors'][] = sprintf('%s: %s', $fileName, $message);
                    }
                    $seen[$code] = $line + 2;
                    $codeMaps[$fileName][$code] = true;
                }
            }
        }

        foreach ($this->referenceRules() as [$fileName, $column, $targetFile, $targetColumn]) {
            if (!is_file($directory.'/'.$fileName)) {
                continue;
            }
            $rows = $this->readCsv($directory.'/'.$fileName);
            foreach ($rows as $line => $row) {
                $value = $this->code($row[$column] ?? '');
                if ($value === null) {
                    continue;
                }
                if (!isset($codeMaps[$targetFile][$value])) {
                    $message = sprintf('Référence "%s"=%s introuvable dans %s.%s (ligne %d).', $column, $value, $targetFile, $targetColumn, $line + 2);
                    $result['files'][$fileName]['errors'][] = $message;
                    $result['errors'][] = sprintf('%s: %s', $fileName, $message);
                }
            }
        }

        return $result;
    }

    /** @return array{programmes:int, zers:int, localites:int, projets:int, indicateurs:int, actions_genre:int, financements:int, files:array<string, int>} */
    public function import(?string $directory = null): array
    {
        $directory ??= $this->getImportDirectory();
        $this->pointGpsByCode = [];
        $summary = ['programmes' => 0, 'zers' => 0, 'localites' => 0, 'projets' => 0, 'indicateurs' => 0, 'actions_genre' => 0, 'financements' => 0, 'files' => []];

        foreach (self::IMPORT_ORDER as $fileName) {
            $path = $directory.'/'.$fileName;
            if (!is_file($path)) {
                continue;
            }
            $rows = $this->readCsv($path);
            $count = match ($fileName) {
                'programmes_pner.csv' => $this->importProgrammes($rows),
                'zers.csv' => $this->importZers($rows),
                'prefectures.csv' => $this->importPrefectures($rows),
                'sous_prefectures.csv' => $this->importSousPrefectures($rows),
                'systemes_electrification.csv' => $this->importSystemes($rows),
                'localites.csv' => $this->importLocalites($rows),
                'projets_electrification.csv' => $this->importProjets($rows),
                'projet_localites.csv' => $this->importProjetLocalites($rows),
                'points_gps.csv' => $this->importPointsGps($rows),
                'infrastructures_electriques.csv' => $this->importInfrastructures($rows),
                'sites_energetiques.csv' => $this->importSites($rows),
                'donnee_geospatiale_localites.csv' => $this->importDonneesGeospatiales($rows),
                'indicateurs_pner.csv' => $this->importIndicateurs($rows),
                'valeurs_indicateurs.csv' => $this->importValeursIndicateurs($rows),
                'actions_genre.csv' => $this->importActionsGenre($rows),
                'beneficiaires_genre.csv' => $this->importBeneficiairesGenre($rows),
                'formations_genre.csv' => $this->importFormationsGenre($rows),
                'indicateurs_genre.csv' => $this->importIndicateursGenre($rows),
                'comites_genre.csv' => $this->importComitesGenre($rows),
                'bailleurs_fonds.csv' => $this->importBailleurs($rows),
                'sources_financement.csv' => $this->importSourcesFinancement($rows),
                'conventions_financement.csv' => $this->importConventions($rows),
                'decaissements.csv' => $this->importDecaissements($rows),
                'couts_previsionnels.csv' => $this->importCouts($rows),
                default => 0,
            };

            $this->entityManager->flush();
            $summary['files'][$fileName] = $count;

            match ($fileName) {
                'programmes_pner.csv' => $summary['programmes'] += $count,
                'zers.csv' => $summary['zers'] += $count,
                'localites.csv' => $summary['localites'] += $count,
                'projets_electrification.csv' => $summary['projets'] += $count,
                'indicateurs_pner.csv' => $summary['indicateurs'] += $count,
                'actions_genre.csv' => $summary['actions_genre'] += $count,
                'bailleurs_fonds.csv', 'sources_financement.csv', 'conventions_financement.csv', 'decaissements.csv', 'couts_previsionnels.csv' => $summary['financements'] += $count,
                default => null,
            };
        }

        return $summary;
    }

    public function getImportDirectory(): string
    {
        return $this->projectDir.'/data/import';
    }

    /** @param list<array<string, string>> $rows */
    private function importProgrammes(array $rows): int
    {
        foreach ($rows as $row) {
            $programme = $this->upsertByCode(ProgrammePner::class, $row['code']);
            $programme->setCode($row['code'])->setNom($row['nom'])->setPeriodeDebut($this->int($row['periode_debut']))->setPeriodeFin($this->int($row['periode_fin']))
                ->setNombreLocalitesPrevues($this->nullableInt($row['nombre_localites_prevues'] ?? null))->setTauxElectrificationCible($this->nullableFloat($row['taux_electrification_cible'] ?? null))
                ->setMontantPrevisionnelUsd($this->nullableFloat($row['montant_previsionnel_usd'] ?? null))->setDescription($this->nullable($row['description'] ?? null));
            $this->entityManager->persist($programme);
        }
        return count($rows);
    }

    private function importZers(array $rows): int
    {
        foreach ($rows as $row) {
            $zer = $this->upsertByCode(Zer::class, $row['code']);
            $zer->setCode($row['code'])->setNom($row['nom'])->setSuperficieKm2($this->nullableFloat($row['superficie_km2'] ?? null))->setPopulation($this->nullableInt($row['population'] ?? null))
                ->setNombreMenages($this->nullableInt($row['nombre_menages'] ?? null))->setNombreLocalites($this->nullableInt($row['nombre_localites'] ?? null))
                ->setTauxLocalitesMoins800Habitants($this->nullableFloat($row['taux_localites_moins_800_habitants'] ?? null))->setPotentielSolaire($this->nullable($row['potentiel_solaire'] ?? null))
                ->setPotentielHydro($this->nullable($row['potentiel_hydro'] ?? null))->setDescription($this->nullable($row['description'] ?? null));
            $this->entityManager->persist($zer);
        }
        return count($rows);
    }

    private function importPrefectures(array $rows): int
    {
        foreach ($rows as $row) {
            $prefecture = $this->upsertByCode(Prefecture::class, $row['code']);
            $prefecture->setCode($row['code'])->setNom($row['nom'])->setZer($this->findByCode(Zer::class, $row['zer_code']));
            $this->entityManager->persist($prefecture);
        }
        return count($rows);
    }

    private function importSousPrefectures(array $rows): int
    {
        foreach ($rows as $row) {
            $sousPrefecture = $this->upsertByCode(SousPrefecture::class, $row['code']);
            $sousPrefecture->setCode($row['code'])->setNom($row['nom'])->setPrefecture($this->findByCode(Prefecture::class, $row['prefecture_code']));
            $this->entityManager->persist($sousPrefecture);
        }
        return count($rows);
    }

    private function importSystemes(array $rows): int
    {
        foreach ($rows as $row) {
            $systeme = $this->upsertByCode(SystemeElectrification::class, $row['code']);
            $systeme->setCode($row['code'])->setNom($row['nom'])->setType($row['type'])->setDescription($this->nullable($row['description'] ?? null))->setTensionKv($this->nullableFloat($row['tension_kv'] ?? null))->setRayonKm($this->nullableFloat($row['rayon_km'] ?? null))->setSourceEnergie($row['source_energie']);
            $this->entityManager->persist($systeme);
        }
        return count($rows);
    }

    private function importLocalites(array $rows): int
    {
        foreach ($rows as $row) {
            $localite = $this->upsertByCode(Localite::class, $row['code']);
            $localite->setCode($row['code'])->setNom($row['nom'])->setLongitude($this->nullableFloat($row['longitude'] ?? null))->setLatitude($this->nullableFloat($row['latitude'] ?? null))
                ->setNombreMenages($this->nullableInt($row['nombre_menages'] ?? null))->setPopulationTotale($this->nullableInt($row['population_totale'] ?? null))->setCategoriePopulation($this->nullable($row['categorie_population'] ?? null))
                ->setStatutElectrification($this->nullable($row['statut_electrification'] ?? null))->setDistanceReseauKm($this->nullableFloat($row['distance_reseau_km'] ?? null))
                ->setZer($this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null))->setPrefecture($this->findOptionalByCode(Prefecture::class, $row['prefecture_code'] ?? null))
                ->setSousPrefecture($this->findOptionalByCode(SousPrefecture::class, $row['sous_prefecture_code'] ?? null))->setProgrammePner($this->findOptionalByCode(ProgrammePner::class, $row['programme_code'] ?? null))
                ->setSystemeElectrification($this->findOptionalByCode(SystemeElectrification::class, $row['systeme_code'] ?? null));
            $this->entityManager->persist($localite);
        }
        return count($rows);
    }

    private function importProjets(array $rows): int
    {
        foreach ($rows as $row) {
            $projet = $this->upsertByCode(ProjetElectrification::class, $row['code']);
            $projet->setCode($row['code'])->setIntitule($row['intitule'])->setDescription($this->nullable($row['description'] ?? null))->setProgrammePner($this->findByCode(ProgrammePner::class, $row['programme_code']))
                ->setZer($this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null))->setSystemeElectrification($this->findOptionalByCode(SystemeElectrification::class, $row['systeme_code'] ?? null))->setStatut($row['statut'])
                ->setDateDebutPrevue($this->date($row['date_debut_prevue']))->setDateFinPrevue($this->date($row['date_fin_prevue']))->setDateDebutEffective($this->nullableDate($row['date_debut_effective'] ?? null))->setDateFinEffective($this->nullableDate($row['date_fin_effective'] ?? null))
                ->setMontantPrevisionnelUsd($this->nullableFloat($row['montant_previsionnel_usd'] ?? null))->setMontantMobiliseUsd($this->nullableFloat($row['montant_mobilise_usd'] ?? null))->setSourceFinancement($this->nullable($row['source_financement'] ?? null))->setMaitreOuvrage($this->nullable($row['maitre_ouvrage'] ?? null))->setPartenaireTechnique($this->nullable($row['partenaire_technique'] ?? null));
            $this->entityManager->persist($projet);
        }
        return count($rows);
    }

    private function importProjetLocalites(array $rows): int
    {
        foreach ($rows as $row) {
            $projet = $this->findByCode(ProjetElectrification::class, $row['projet_code']);
            $localite = $this->findByCode(Localite::class, $row['localite_code']);
            $projetLocalite = $this->entityManager->getRepository(ProjetLocalite::class)->findOneBy(['projetElectrification' => $projet, 'localite' => $localite]) ?? new ProjetLocalite();
            $projetLocalite->setProjetElectrification($projet)->setLocalite($localite)->setStatutLocalite($row['statut_localite'])->setDateRaccordementPrevue($this->nullableDate($row['date_raccordement_prevue'] ?? null))->setDateRaccordementEffective($this->nullableDate($row['date_raccordement_effective'] ?? null))->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($projetLocalite);
        }
        return count($rows);
    }

    private function importPointsGps(array $rows): int
    {
        foreach ($rows as $row) {
            $pointCode = $this->code($row['point_code']);
            $point = $this->entityManager->getRepository(PointGps::class)->findOneBy(['latitude' => $this->float($row['latitude']), 'longitude' => $this->float($row['longitude']), 'source' => $this->nullable($row['source'] ?? null)]) ?? new PointGps();
            $point->setLatitude($this->float($row['latitude']))->setLongitude($this->float($row['longitude']))->setAltitude($this->nullableFloat($row['altitude'] ?? null))->setPrecisionGps($this->nullableFloat($row['precision_gps'] ?? null))->setSource($this->nullable($row['source'] ?? null));
            $this->entityManager->persist($point);
            if ($pointCode !== null) {
                $this->pointGpsByCode[$pointCode] = $point;
            }
        }
        return count($rows);
    }

    private function importInfrastructures(array $rows): int
    {
        foreach ($rows as $row) {
            $infra = $this->upsertByCode(InfrastructureElectrique::class, $row['code']);
            $infra->setCode($row['code'])->setNom($row['nom'])->setTypeInfrastructure($row['type_infrastructure'])->setDescription($this->nullable($row['description'] ?? null))->setPointGps($this->findPoint($row['point_code'] ?? null))->setLocalite($this->findOptionalByCode(Localite::class, $row['localite_code'] ?? null))->setStatut($row['statut']);
            $this->entityManager->persist($infra);
        }
        return count($rows);
    }

    private function importSites(array $rows): int
    {
        foreach ($rows as $row) {
            $site = $this->upsertByCode(SiteEnergetique::class, $row['code']);
            $site->setCode($row['code'])->setNom($row['nom'])->setTypeSite($row['type_site'])->setLocalite($this->findByCode(Localite::class, $row['localite_code']))->setPointGps($this->findPoint($row['point_code']))->setPuissanceInstalleeKw($this->nullableFloat($row['puissance_installee_kw'] ?? null))->setStatut($row['statut'])->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($site);
        }
        return count($rows);
    }

    private function importDonneesGeospatiales(array $rows): int
    {
        foreach ($rows as $row) {
            $localite = $this->findByCode(Localite::class, $row['localite_code']);
            $donnee = $this->entityManager->getRepository(DonneeGeospatialeLocalite::class)->findOneBy(['localite' => $localite]) ?? new DonneeGeospatialeLocalite();
            $donnee->setLocalite($localite)
                ->setSuperficieKm2($this->nullableFloat($row['superficie_km2'] ?? null))
                ->setPopulationReference($this->nullableInt($row['population_reference'] ?? null))
                ->setMenagesReference($this->nullableInt($row['menages_reference'] ?? null))
                ->setDensitePopulation($this->nullableFloat($row['densite_population'] ?? null))
                ->setObservations($this->nullable($row['observations'] ?? null));
            $this->entityManager->persist($donnee);
        }
        return count($rows);
    }

    private function importIndicateurs(array $rows): int
    {
        foreach ($rows as $row) {
            $indicateur = $this->upsertByCode(IndicateurPner::class, $row['code']);
            $indicateur->setCode($row['code'])->setLibelle($row['libelle'])->setDescription($this->nullable($row['description'] ?? null))->setTypeIndicateur($row['type_indicateur'])->setUnite($row['unite'])->setValeurReference($this->nullableFloat($row['valeur_reference'] ?? null))->setValeurCible($this->nullableFloat($row['valeur_cible'] ?? null))->setFrequenceSuivi($row['frequence_suivi'])->setSourceDonnee($this->nullable($row['source_donnee'] ?? null));
            $this->entityManager->persist($indicateur);
        }
        return count($rows);
    }

    private function importValeursIndicateurs(array $rows): int
    {
        foreach ($rows as $row) {
            $indicateur = $this->findByCode(IndicateurPner::class, $row['indicateur_code']);
            $programme = $this->findOptionalByCode(ProgrammePner::class, $row['programme_code'] ?? null);
            $zer = $this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null);
            $projet = $this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null);
            $localite = $this->findOptionalByCode(Localite::class, $row['localite_code'] ?? null);
            $criteria = ['indicateurPner' => $indicateur, 'programmePner' => $programme, 'zer' => $zer, 'projetElectrification' => $projet, 'localite' => $localite, 'periode' => $row['periode'], 'annee' => $this->int($row['annee'])];
            $valeur = $this->entityManager->getRepository(ValeurIndicateur::class)->findOneBy($criteria) ?? new ValeurIndicateur();
            $valeur->setIndicateurPner($indicateur)->setProgrammePner($programme)->setZer($zer)->setProjetElectrification($projet)->setLocalite($localite)->setPeriode($row['periode'])->setAnnee($this->int($row['annee']))->setValeur($this->float($row['valeur']))->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($valeur);
        }
        return count($rows);
    }

    private function importActionsGenre(array $rows): int
    {
        foreach ($rows as $row) {
            $action = $this->upsertByCode(ActionGenre::class, $row['code']);
            $action->setCode($row['code'])->setTitre($row['titre'])->setDescription($this->nullable($row['description'] ?? null))->setAxeStrategique($row['axe_strategique'])->setProgrammePner($this->findOptionalByCode(ProgrammePner::class, $row['programme_code'] ?? null))->setProjetElectrification($this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null))->setZer($this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null))->setLocalite($this->findOptionalByCode(Localite::class, $row['localite_code'] ?? null))->setStatut($row['statut'])->setDateDebutPrevue($this->nullableDate($row['date_debut_prevue'] ?? null))->setDateFinPrevue($this->nullableDate($row['date_fin_prevue'] ?? null))->setDateDebutEffective($this->nullableDate($row['date_debut_effective'] ?? null))->setDateFinEffective($this->nullableDate($row['date_fin_effective'] ?? null))->setResponsable($this->nullable($row['responsable'] ?? null));
            $this->entityManager->persist($action);
        }
        return count($rows);
    }

    private function importBeneficiairesGenre(array $rows): int
    {
        foreach ($rows as $row) {
            $action = $this->findOptionalByCode(ActionGenre::class, $row['action_code'] ?? null);
            $localite = $this->findOptionalByCode(Localite::class, $row['localite_code'] ?? null);
            $beneficiaire = $this->entityManager->getRepository(BeneficiaireGenre::class)->findOneBy(['actionGenre' => $action, 'localite' => $localite, 'categorieBeneficiaire' => $row['categorie_beneficiaire']]) ?? new BeneficiaireGenre();
            $beneficiaire->setActionGenre($action)->setProjetElectrification($this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null))->setLocalite($localite)->setCategorieBeneficiaire($row['categorie_beneficiaire'])->setNombreHommes($this->int($row['nombre_hommes']))->setNombreFemmes($this->int($row['nombre_femmes']))->setNombreJeunes($this->int($row['nombre_jeunes']))->setNombrePersonnesVulnerables($this->int($row['nombre_personnes_vulnerables']))->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($beneficiaire);
        }
        return count($rows);
    }

    private function importFormationsGenre(array $rows): int
    {
        foreach ($rows as $row) {
            $formation = $this->upsertByCode(FormationGenre::class, $row['code']);
            $formation->setCode($row['code'])->setIntitule($row['intitule'])->setActionGenre($this->findOptionalByCode(ActionGenre::class, $row['action_code'] ?? null))->setZer($this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null))->setLocalite($this->findOptionalByCode(Localite::class, $row['localite_code'] ?? null))->setDateFormation($this->nullableDate($row['date_formation'] ?? null))->setNombreParticipants($this->int($row['nombre_participants']))->setNombreFemmes($this->int($row['nombre_femmes']))->setNombreHommes($this->int($row['nombre_hommes']))->setTheme($row['theme'])->setFormateur($this->nullable($row['formateur'] ?? null))->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($formation);
        }
        return count($rows);
    }

    private function importIndicateursGenre(array $rows): int
    {
        foreach ($rows as $row) {
            $indicateur = $this->upsertByCode(IndicateurGenre::class, $row['code']);
            $indicateur->setCode($row['code'])
                ->setLibelle($row['libelle'])
                ->setDescription($this->nullable($row['description'] ?? null))
                ->setActionGenre($this->findOptionalByCode(ActionGenre::class, $row['action_code'] ?? null))
                ->setProjetElectrification($this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null))
                ->setValeurReference($this->nullableFloat($row['valeur_reference'] ?? null))
                ->setValeurCible($this->nullableFloat($row['valeur_cible'] ?? null))
                ->setValeurActuelle($this->nullableFloat($row['valeur_actuelle'] ?? null))
                ->setUnite($this->nullable($row['unite'] ?? null));
            $this->entityManager->persist($indicateur);
        }
        return count($rows);
    }

    private function importComitesGenre(array $rows): int
    {
        foreach ($rows as $row) {
            $comite = $this->upsertByCode(ComiteGenre::class, $row['code']);
            $comite->setCode($row['code'])
                ->setNom($row['nom'])
                ->setTypeComite($row['type_comite'])
                ->setZer($this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null))
                ->setLocalite($this->findOptionalByCode(Localite::class, $row['localite_code'] ?? null))
                ->setDateMiseEnPlace($this->nullableDate($row['date_mise_en_place'] ?? null))
                ->setNombreMembres($this->int($row['nombre_membres']))
                ->setNombreFemmes($this->int($row['nombre_femmes']))
                ->setNombreHommes($this->int($row['nombre_hommes']))
                ->setStatut($row['statut'])
                ->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($comite);
        }
        return count($rows);
    }

    private function importBailleurs(array $rows): int
    {
        foreach ($rows as $row) {
            $bailleur = $this->upsertByCode(BailleurFonds::class, $row['code']);
            $bailleur->setCode($row['code'])->setNom($row['nom'])->setTypeBailleur($row['type_bailleur'])->setPays($this->nullable($row['pays'] ?? null))->setContactPrincipal($this->nullable($row['contact_principal'] ?? null))->setEmail($this->nullable($row['email'] ?? null))->setTelephone($this->nullable($row['telephone'] ?? null))->setDescription($this->nullable($row['description'] ?? null));
            $this->entityManager->persist($bailleur);
        }
        return count($rows);
    }

    private function importSourcesFinancement(array $rows): int
    {
        foreach ($rows as $row) {
            $source = $this->upsertByCode(SourceFinancement::class, $row['code']);
            $source->setCode($row['code'])->setNom($row['nom'])->setTypeSource($row['type_source'])->setBailleurFonds($this->findOptionalByCode(BailleurFonds::class, $row['bailleur_code'] ?? null))->setDescription($this->nullable($row['description'] ?? null));
            $this->entityManager->persist($source);
        }
        return count($rows);
    }

    private function importConventions(array $rows): int
    {
        foreach ($rows as $row) {
            $convention = $this->upsertByCode(ConventionFinancement::class, $row['code']);
            $convention->setCode($row['code'])->setIntitule($row['intitule'])->setBailleurFonds($this->findByCode(BailleurFonds::class, $row['bailleur_code']))->setSourceFinancement($this->findOptionalByCode(SourceFinancement::class, $row['source_code'] ?? null))->setProgrammePner($this->findOptionalByCode(ProgrammePner::class, $row['programme_code'] ?? null))->setProjetElectrification($this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null))->setMontantUsd($this->float($row['montant_usd']))->setDateSignature($this->nullableDate($row['date_signature'] ?? null))->setDateDebut($this->nullableDate($row['date_debut'] ?? null))->setDateFin($this->nullableDate($row['date_fin'] ?? null))->setStatut($row['statut'])->setDescription($this->nullable($row['description'] ?? null));
            $this->entityManager->persist($convention);
        }
        return count($rows);
    }

    private function importDecaissements(array $rows): int
    {
        foreach ($rows as $row) {
            $decaissement = $this->entityManager->getRepository(Decaissement::class)->findOneBy(['referencePaiement' => $row['reference_paiement']]) ?? new Decaissement();
            $decaissement->setReferencePaiement($row['reference_paiement'])->setConventionFinancement($this->findByCode(ConventionFinancement::class, $row['convention_code']))->setProjetElectrification($this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null))->setMontantUsd($this->float($row['montant_usd']))->setDateDecaissement($this->date($row['date_decaissement']))->setObjet($this->nullable($row['objet'] ?? null))->setStatut($row['statut']);
            $this->entityManager->persist($decaissement);
        }
        return count($rows);
    }

    private function importCouts(array $rows): int
    {
        foreach ($rows as $row) {
            $programme = $this->findOptionalByCode(ProgrammePner::class, $row['programme_code'] ?? null);
            $projet = $this->findOptionalByCode(ProjetElectrification::class, $row['projet_code'] ?? null);
            $zer = $this->findOptionalByCode(Zer::class, $row['zer_code'] ?? null);
            $annee = $this->nullableInt($row['annee'] ?? null);
            $criteria = ['programmePner' => $programme, 'projetElectrification' => $projet, 'zer' => $zer, 'categorieCout' => $row['categorie_cout'], 'annee' => $annee];
            $cout = $this->entityManager->getRepository(CoutPrevisionnel::class)->findOneBy($criteria) ?? new CoutPrevisionnel();
            $cout->setProgrammePner($programme)->setProjetElectrification($projet)->setZer($zer)->setCategorieCout($row['categorie_cout'])->setMontantUsd($this->float($row['montant_usd']))->setAnnee($annee)->setCommentaire($this->nullable($row['commentaire'] ?? null));
            $this->entityManager->persist($cout);
        }
        return count($rows);
    }

    /** @return list<array{0:string, 1:string, 2:string, 3:string}> */
    private function referenceRules(): array
    {
        return [
            ['prefectures.csv', 'zer_code', 'zers.csv', 'code'], ['sous_prefectures.csv', 'prefecture_code', 'prefectures.csv', 'code'],
            ['localites.csv', 'zer_code', 'zers.csv', 'code'], ['localites.csv', 'prefecture_code', 'prefectures.csv', 'code'], ['localites.csv', 'sous_prefecture_code', 'sous_prefectures.csv', 'code'], ['localites.csv', 'programme_code', 'programmes_pner.csv', 'code'], ['localites.csv', 'systeme_code', 'systemes_electrification.csv', 'code'],
            ['projets_electrification.csv', 'programme_code', 'programmes_pner.csv', 'code'], ['projets_electrification.csv', 'zer_code', 'zers.csv', 'code'], ['projets_electrification.csv', 'systeme_code', 'systemes_electrification.csv', 'code'], ['projet_localites.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['projet_localites.csv', 'localite_code', 'localites.csv', 'code'],
            ['infrastructures_electriques.csv', 'point_code', 'points_gps.csv', 'point_code'], ['infrastructures_electriques.csv', 'localite_code', 'localites.csv', 'code'], ['sites_energetiques.csv', 'point_code', 'points_gps.csv', 'point_code'], ['sites_energetiques.csv', 'localite_code', 'localites.csv', 'code'], ['donnee_geospatiale_localites.csv', 'localite_code', 'localites.csv', 'code'],
            ['valeurs_indicateurs.csv', 'indicateur_code', 'indicateurs_pner.csv', 'code'], ['valeurs_indicateurs.csv', 'programme_code', 'programmes_pner.csv', 'code'], ['valeurs_indicateurs.csv', 'zer_code', 'zers.csv', 'code'], ['valeurs_indicateurs.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['valeurs_indicateurs.csv', 'localite_code', 'localites.csv', 'code'],
            ['actions_genre.csv', 'programme_code', 'programmes_pner.csv', 'code'], ['actions_genre.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['actions_genre.csv', 'zer_code', 'zers.csv', 'code'], ['actions_genre.csv', 'localite_code', 'localites.csv', 'code'], ['beneficiaires_genre.csv', 'action_code', 'actions_genre.csv', 'code'], ['beneficiaires_genre.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['beneficiaires_genre.csv', 'localite_code', 'localites.csv', 'code'], ['formations_genre.csv', 'action_code', 'actions_genre.csv', 'code'], ['formations_genre.csv', 'zer_code', 'zers.csv', 'code'], ['formations_genre.csv', 'localite_code', 'localites.csv', 'code'], ['indicateurs_genre.csv', 'action_code', 'actions_genre.csv', 'code'], ['indicateurs_genre.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['comites_genre.csv', 'zer_code', 'zers.csv', 'code'], ['comites_genre.csv', 'localite_code', 'localites.csv', 'code'],
            ['sources_financement.csv', 'bailleur_code', 'bailleurs_fonds.csv', 'code'], ['conventions_financement.csv', 'bailleur_code', 'bailleurs_fonds.csv', 'code'], ['conventions_financement.csv', 'source_code', 'sources_financement.csv', 'code'], ['conventions_financement.csv', 'programme_code', 'programmes_pner.csv', 'code'], ['conventions_financement.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['decaissements.csv', 'convention_code', 'conventions_financement.csv', 'code'], ['decaissements.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['couts_previsionnels.csv', 'programme_code', 'programmes_pner.csv', 'code'], ['couts_previsionnels.csv', 'projet_code', 'projets_electrification.csv', 'code'], ['couts_previsionnels.csv', 'zer_code', 'zers.csv', 'code'],
        ];
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Impossible de lire le fichier.');
        }
        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return [];
        }
        $header = array_map(fn (string $value): string => trim(preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? ''), $header);
        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if ($data === [null] || $data === false) {
                continue;
            }
            $row = [];
            foreach ($header as $index => $column) {
                $row[$column] = isset($data[$index]) ? trim((string) $data[$index]) : '';
            }
            if (implode('', $row) === '') {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    /** @return list<string> */
    private function readHeader(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }
        $header = fgetcsv($handle) ?: [];
        fclose($handle);
        return array_map('trim', $header);
    }

    /** @template T of object @param class-string<T> $class @return T */
    private function upsertByCode(string $class, string $code): object
    {
        return $this->findOptionalByCode($class, $code) ?? new $class();
    }

    /** @template T of object @param class-string<T> $class @return T */
    private function findByCode(string $class, string $code): object
    {
        $entity = $this->findOptionalByCode($class, $code);
        if ($entity === null) {
            throw new \RuntimeException(sprintf('Aucune entité %s trouvée pour le code %s.', $class, $code));
        }
        return $entity;
    }

    /** @template T of object @param class-string<T> $class @return T|null */
    private function findOptionalByCode(string $class, ?string $code): ?object
    {
        $code = $this->code($code);
        if ($code === null) {
            return null;
        }
        return $this->entityManager->getRepository($class)->findOneBy(['code' => $code]);
    }

    private function findPoint(?string $code): ?PointGps
    {
        $code = $this->code($code);
        if ($code === null) {
            return null;
        }
        return $this->pointGpsByCode[$code] ?? null;
    }

    private function nullable(?string $value): ?string { $value = trim((string) $value); return $value === '' ? null : $value; }
    private function code(?string $value): ?string { $value = $this->nullable($value); return $value === null ? null : mb_strtoupper($value); }
    private function int(?string $value): int { return (int) str_replace(' ', '', (string) $value); }
    private function nullableInt(?string $value): ?int { return $this->nullable($value) === null ? null : $this->int($value); }
    private function float(?string $value): float { return (float) str_replace([' ', ','], ['', '.'], (string) $value); }
    private function nullableFloat(?string $value): ?float { return $this->nullable($value) === null ? null : $this->float($value); }
    private function date(?string $value): \DateTimeImmutable { return new \DateTimeImmutable((string) $value); }
    private function nullableDate(?string $value): ?\DateTimeImmutable { return $this->nullable($value) === null ? null : $this->date($value); }
}
