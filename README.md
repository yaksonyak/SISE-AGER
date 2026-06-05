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

Les fixtures métier de démonstration chargent notamment les programmes `PUERG` (2023-2027), `PERMT` (2028-2033) et `PFAUER` (2034-2040), des ZER, des préfectures, des sous-préfectures, des localités et des systèmes d’électrification.

## Commandes utiles

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php bin/console debug:router
php bin/console lint:yaml config
```
