# VeGlobs sur Paris

Plateforme d'information trafic pour les transports parisiens.

## Prérequis

- Docker Desktop
- Git

## Installation

```bash
git clone <repo>
cd veglobs

# Copier le fichier d'environnement
cp .env .env.local
# Renseigner IDFM_API_KEY et APP_SECRET dans .env.local

# Lancer les conteneurs
docker compose up -d

# Installer les dépendances
docker compose exec app composer install

# Créer la base de données
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Importer les données IDFM
docker compose exec app php bin/console app:import-prim
```

## Services disponibles

| Service     | URL                          |
|-------------|------------------------------|
| Application | http://localhost:8000        |
| phpMyAdmin  | http://localhost:8081        |
| MailHog     | http://localhost:8025        |
| Mercure     | http://localhost:1337        |

## Commandes utiles

```bash
# Vider le cache
docker compose exec app php bin/console cache:clear

# Importer les perturbations IDFM
docker compose exec app php bin/console app:import-prim

# Arrêter les conteneurs
docker compose down

# Arrêter et supprimer les volumes
docker compose down -v
```

## Stack technique

- **Backend** : Symfony 6 / PHP 8.2
- **Base de données** : MySQL 8.0
- **Emails** : MailHog (dev) / SMTP (prod)
- **Temps réel** : Mercure
- **API** : IDFM Prim (Île-de-France Mobilités)
- **Frontend** : Twig / CSS / Leaflet.js
