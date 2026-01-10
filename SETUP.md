# Guide de démarrage - Best Time

## Prérequis

- Docker et Docker Compose installés
- Ports disponibles : 80, 3000, 5432, 6379

## Installation

### 1. Configuration de l'environnement

Copier le template d'environnement :
```bash
cp env.template .env
```

### 2. Démarrer les conteneurs Docker

```bash
docker compose up -d
```

Vérifier que tous les conteneurs sont démarrés :
```bash
docker compose ps
```

### 3. Installation du Backend (Laravel)

Entrer dans le conteneur PHP :
```bash
docker compose exec php-fpm bash
```

Dans le conteneur, installer les dépendances :
```bash
composer install
php artisan key:generate
```

Configurer la base de données (si nécessaire, copier env.template vers backend/.env) :
```bash
php artisan migrate
php artisan db:seed
```

Sortir du conteneur :
```bash
exit
```

### 4. Installation du Frontend (Nuxt)

Les dépendances seront installées automatiquement au démarrage du conteneur Node. Si nécessaire :

```bash
docker compose exec node npm install
```

### 5. Accès à l'application

- **Frontend** : http://localhost (proxifié via Nginx vers le serveur Nuxt sur le port 3000)
- **API Backend** : http://localhost/api
- **PostgreSQL** : localhost:5432 (user: postgres, password: postgres, db: besttime)
- **Redis** : localhost:6379

## Comptes de test

Après avoir exécuté les seeders :

- **Admin** : 
  - Email: `admin@besttime.test`
  - Password: `password`

- **Employé** : 
  - Email: `john@besttime.test` ou `jane@besttime.test`
  - Password: `password`

## Commandes utiles

### Voir les logs
```bash
docker compose logs -f [service-name]
# Exemples:
docker compose logs -f php-fpm
docker compose logs -f node
```

### Arrêter les conteneurs
```bash
docker compose down
```

### Redémarrer un service
```bash
docker compose restart [service-name]
```

### Exécuter des commandes Artisan
```bash
docker compose exec php-fpm php artisan [command]
```

### Exécuter des commandes npm
```bash
docker compose exec node npm [command]
```

## Structure du projet

```
best-time/
├── backend/          # Laravel 11 API
├── frontend/         # Nuxt 3 Frontend
├── docker/           # Configurations Docker
├── docker-compose.yml
└── .env              # Variables d'environnement (à créer)
```

## Troubleshooting

### Les conteneurs ne démarrent pas
Vérifiez que les ports 80, 3000, 5432, 6379 ne sont pas déjà utilisés.

### Erreur de connexion à la base de données
Attendez quelques secondes que PostgreSQL soit prêt. Vous pouvez vérifier avec :
```bash
docker compose exec postgres pg_isready -U postgres
```

### Frontend ne charge pas
Vérifiez les logs du conteneur Node :
```bash
docker compose logs node
```

### Backend retourne des erreurs 500
Vérifiez les permissions sur les dossiers storage et bootstrap/cache :
```bash
docker compose exec php-fpm chmod -R 775 storage bootstrap/cache
```
