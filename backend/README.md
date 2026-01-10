# Backend Laravel 11 API

## Installation

1. Dans le conteneur PHP :
```bash
docker compose exec php-fpm composer install
docker compose exec php-fpm php artisan key:generate
docker compose exec php-fpm php artisan migrate
docker compose exec php-fpm php artisan db:seed
```

## Configuration

Copier le fichier `env.template` à la racine du projet vers `.env` dans le dossier backend.

## Routes API

- `POST /api/auth/login` - Connexion
- `POST /api/auth/logout` - Déconnexion
- `GET /api/auth/me` - Utilisateur actuel
- `GET /api/dashboard` - Statistiques du tableau de bord
- `GET /api/projects` - Liste des projets
- `POST /api/projects` - Créer un projet (Admin)
- `GET /api/time-entries` - Liste des entrées de temps
- `POST /api/time-entries` - Créer une entrée de temps
- `POST /api/time-entries/start` - Démarrer un pointage
- `POST /api/time-entries/stop` - Arrêter un pointage

## Comptes de test

- Admin: `admin@besttime.test` / `password`
- Employee: `john@besttime.test` / `password`
- Employee: `jane@besttime.test` / `password`
