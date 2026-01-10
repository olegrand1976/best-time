# Frontend Nuxt 3

## Installation

Dans le conteneur Node (automatique via docker-compose) :
```bash
docker compose exec node npm install
```

## Démarrage

Le serveur de développement démarre automatiquement via docker-compose.

Accès : http://localhost:3000

## Structure

- `/pages` - Pages de l'application
- `/components` - Composants réutilisables
- `/stores` - Stores Pinia (auth, timeEntry)
- `/composables` - Composables (useApi, useAuth)
- `/middleware` - Middleware d'authentification
- `/layouts` - Layouts de l'application
