# SISE-AGER Backend

Socle backend Symfony pour la plateforme **SISE-AGER** dédiée au **PNER 2040 / AGER**.

Ce Sprint 0 stabilise uniquement la base technique. Les entités métier PNER ne sont pas encore créées.

## Stack

- PHP 8.3 / Symfony 6.4
- API Platform
- Doctrine ORM / Doctrine Migrations
- PostgreSQL 16
- Docker avec services `app`, `nginx`, `postgres`
- Authentification JWT avec LexikJWTAuthenticationBundle
- CORS avec NelmioCorsBundle
- Fixtures Doctrine

## Prérequis

- Docker et Docker Compose
- Make optionnel, non requis
- Composer si vous lancez le projet hors Docker

## Configuration locale

Copiez si besoin les variables dans un fichier local non versionné :

```bash
cp .env .env.local
```

Variables utiles :

```dotenv
APP_ENV=dev
DATABASE_URL="postgresql://app:ChangeMe!@127.0.0.1:5432/sise_ager?serverVersion=16&charset=utf8"
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=ChangeThisJwtPassphrase
JWT_TOKEN_TTL=3600
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
ADMIN_EMAIL=admin@sise-ager.local
ADMIN_PASSWORD=admin123
ADMIN_NOM=Administrateur
ADMIN_PRENOM=SISE-AGER
ADMIN_TELEPHONE=
```

> Remplacez les mots de passe et la passphrase JWT avant tout déploiement hors environnement local.

## Installation et lancement avec Docker

Construire et démarrer les conteneurs :

```bash
docker compose up -d --build
```

Installer ou mettre à jour les dépendances PHP si nécessaire :

```bash
docker compose exec app composer install
```

Générer la paire de clés JWT locale :

```bash
docker compose exec app php bin/console lexik:jwt:generate-keypair --overwrite
```

Créer la base, exécuter les migrations et charger l'administrateur initial :

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction
```

L'API est ensuite disponible sur :

- Documentation API Platform : <http://localhost:8080/api>
- Authentification JWT : `POST http://localhost:8080/api/login_check`

Exemple de connexion admin :

```bash
curl -X POST http://localhost:8080/api/login_check \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@sise-ager.local","password":"admin123"}'
```

## Lancement hors Docker

Installer les dépendances :

```bash
composer install
```

Démarrer PostgreSQL localement puis exécuter :

```bash
php bin/console lexik:jwt:generate-keypair --overwrite
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
symfony server:start
```

## Sécurité et API

- `/api/login_check` est public et retourne un token JWT valide.
- Les autres routes `/api/**` demandent un JWT valide.
- La ressource `User` est exposée par API Platform uniquement aux utilisateurs ayant `ROLE_ADMIN`.
- Les mots de passe créés via l'API `User` passent par un processor API Platform qui les hache avant persistance.


## Endpoints métier PNER

Les référentiels métier PNER sont exposés par API Platform et consultables dans Swagger sur <http://localhost:8080/api>.

| Ressource | Endpoint collection | Usage |
| --- | --- | --- |
| Programmes PNER | `/api/programme_pners` | Programmes PUERG, PERMT et PFAUER du PNER Horizon 2040 |
| ZER | `/api/zers` | Zones d’Électrification Rurale |
| Préfectures | `/api/prefectures` | Préfectures rattachées aux ZER |
| Sous-préfectures | `/api/sous_prefectures` | Sous-préfectures rattachées aux préfectures |
| Localités | `/api/localites` | Localités rurales à électrifier et rattachements PNER |
| Systèmes d’électrification | `/api/systeme_electrifications` | Référentiel des solutions techniques d’électrification |
| Projets d’électrification | `/api/projet_electrifications` | Projets ruraux rattachés aux programmes PNER, ZER et systèmes techniques |
| Localités de projet | `/api/projet_localites` | Association des localités aux projets et suivi du raccordement |
| Phases de projet | `/api/phase_projets` | Phasage opérationnel des projets d’électrification |
| Activités de projet | `/api/activite_projets` | Activités détaillées et taux d’exécution par phase |
| Points GPS | `/api/point_gps` | Coordonnées collectées pour le SIG-ER et la cartographie |
| Infrastructures électriques | `/api/infrastructure_electriques` | Postes, lignes, mini-réseaux, centrales et stockage géolocalisés |
| Sites énergétiques | `/api/site_energetiques` | Sites solaires, hydro, hybrides, diesel ou stockage |
| Données géospatiales localité | `/api/donnee_geospatiale_localites` | Indicateurs géospatiaux rattachés aux localités rurales |
| Indicateurs PNER | `/api/indicateur_pners` | Référentiel des indicateurs du suivi-évaluation SSE |
| Valeurs d’indicateur | `/api/valeur_indicateurs` | Mesures périodiques des indicateurs par programme, ZER, projet ou localité |
| Rapports de suivi | `/api/rapport_suivis` | Rapports mensuels, trimestriels, annuels, projet ou ZER |
| Observations de suivi | `/api/observation_suivis` | Observations, alertes et points de vigilance SSE |
| Bailleurs de fonds | `/api/bailleur_fonds` | Référentiel des bailleurs, partenaires, banques et fonds climat |
| Sources de financement | `/api/source_financements` | Budget État, dons, prêts, PPP, subventions et autres sources |
| Conventions de financement | `/api/convention_financements` | Engagements et conventions liés aux programmes et projets PNER |
| Décaissements | `/api/decaissements` | Suivi des décaissements par convention et projet |
| Coûts prévisionnels | `/api/cout_previsionnels` | Prévisions de coûts par programme, projet, ZER et catégorie |
| Actions genre | `/api/action_genres` | Actions genre/inclusion rattachées aux programmes, projets, ZER et localités |
| Bénéficiaires genre | `/api/beneficiaire_genres` | Répartition hommes/femmes/jeunes/personnes vulnérables par action ou projet |
| Formations genre | `/api/formation_genres` | Sessions de formation et sensibilisation genre/inclusion |
| Indicateurs genre | `/api/indicateur_genres` | Indicateurs spécifiques de suivi genre et inclusion |
| Comités genre | `/api/comite_genres` | Comités sectoriels, intersectoriels, régionaux et locaux |

Les fixtures métier de démonstration chargent notamment les programmes `PUERG` (2023-2027), `PERMT` (2028-2033) et `PFAUER` (2034-2040), des ZER, des préfectures, des sous-préfectures, des localités, des systèmes d’électrification et un projet pilote PUERG avec phases, activités et localités associées, des points GPS, infrastructures électriques, sites énergétiques et données géospatiales SIG de démonstration, des indicateurs, valeurs, rapports et observations SSE, des bailleurs, sources, conventions, décaissements et coûts prévisionnels, ainsi que des actions, bénéficiaires, formations, indicateurs et comités genre/inclusion de démonstration.

## Import massif des données PNER

Les fichiers modèles CSV destinés au chargement massif des données PNER sont placés dans `data/import`. Ils utilisent des codes métiers (`PUERG`, `ZER-KANKAN`, `PREF-KANKAN`, `LOC-KOUROUSSA-001`, etc.) plutôt que des IDs techniques Doctrine.

Commandes disponibles :

```bash
php bin/console app:validate-pner-import-files
php bin/console app:import-pner-data
```

Ordre d’import appliqué par le service : programmes, ZER, préfectures, sous-préfectures, systèmes d’électrification, localités, projets, associations projet/localité, points GPS, infrastructures, sites énergétiques, indicateurs, valeurs d’indicateurs, données genre, puis financement.

Formats de correspondance principaux :

- `localites.csv` référence `zer_code`, `prefecture_code`, `sous_prefecture_code`, `programme_code` et `systeme_code`.
- `projet_localites.csv` référence `projet_code` et `localite_code`.
- `valeurs_indicateurs.csv` référence `indicateur_code`, `programme_code`, `zer_code`, `projet_code` et `localite_code`.
- `actions_genre.csv` référence `programme_code`, `projet_code`, `zer_code` et `localite_code`.
- `conventions_financement.csv` référence `bailleur_code`, `source_code`, `programme_code` et `projet_code`.

Exemple `localites.csv` :

```csv
code,nom,longitude,latitude,nombre_menages,population_totale,categorie_population,statut_electrification,distance_reseau_km,zer_code,prefecture_code,sous_prefecture_code,programme_code,systeme_code
LOC-KOUROUSSA-001,Kouroussa Centre,-9.8833,10.6500,240,1450,PLUS_800_HABITANTS,NON_ELECTRIFIEE,18.5,ZER-KANKAN,PREF-KANKAN,SP-KOUROUSSA,PUERG,EXT-30KV
```

Exemple `projet_localites.csv` :

```csv
projet_code,localite_code,statut_localite,date_raccordement_prevue,date_raccordement_effective,commentaire
PRJ-PUERG-KKN-001,LOC-KOUROUSSA-001,A_ELECTRIFIER,2025-06-30,,Localite raccordable au reseau MT 30 kV
```

Les fixtures existantes restent disponibles pour les démonstrations rapides ; l’import CSV sert au chargement progressif des données réelles issues des documents PNER.


## Relations API métier

Les écritures API Platform (`POST`, `PUT`, `PATCH`) continuent d’accepter les relations métier sous forme d’identifiants numériques simples afin de faciliter l’intégration front. Les payloads peuvent donc utiliser `programmePner: 1` au lieu de l’IRI `/api/programme_pners/1`.

Les lectures (`GET`) retournent désormais les relations `ManyToOne` sous forme d’objets résumés de premier niveau. Les objets imbriqués sont volontairement limités aux champs `id`, `code` et au libellé métier disponible (`nom`, `libelle` ou `intitule`) afin d’éviter les boucles de sérialisation et de ne pas exposer les collections inverses.

Exemple de lecture d’un projet d’électrification :

```json
{
  "id": 1,
  "code": "PRJ-PUERG-KDA-001",
  "intitule": "Électrification rurale prioritaire de la zone de Kindia",
  "programmePner": {
    "id": 1,
    "code": "PUERG",
    "nom": "Programme PUERG"
  },
  "zer": {
    "id": 2,
    "code": "ZER-KINDIA",
    "nom": "ZER Kindia"
  },
  "systemeElectrification": {
    "id": 1,
    "code": "EXT-30KV",
    "nom": "Extension réseau MT 30 kV"
  },
  "statut": "EN_PREPARATION"
}
```

Exemple de création avec relations sous forme d’IDs :

```json
{
  "code": "PRJ-TEST-001",
  "intitule": "Projet test",
  "programmePner": 1,
  "zer": 1,
  "systemeElectrification": 1,
  "statut": "PLANIFIE",
  "dateDebutPrevue": "2026-01-01",
  "dateFinPrevue": "2026-12-31"
}
```

Les alias `*Id` sont également acceptés en écriture pour les relations principales. Par exemple `programmePnerId: 1` est converti vers la relation `programmePner`.

## Commandes utiles

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php bin/console debug:router
php bin/console lint:yaml config
```
