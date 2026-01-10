Agis en tant qu'architecte logiciel Senior Full Stack et expert DevOps. Je souhaite que tu m'aides à créer le squelette et les fonctionnalités de base d'une application de "Gestion de temps de prestation" (Time Tracking) pour ouvriers et employés.

## 1. Stack Technique & Architecture
Le projet doit suivre une architecture moderne, sécurisée et performante :
- **Backend API :** Laravel 11 (API Only). Utilisation de Laravel Sanctum pour l'authentification.
- **Frontend :** Nuxt 3 (Composition API, TypeScript).
- **Base de données :** PostgreSQL.
- **Cache & Queue :** Redis.
- **Styling :** Tailwind CSS (via Nuxt UI ou Tailwind direct).
- **Déploiement :** Docker & Docker Compose (environnement de développement local qui simule la prod).

## 2. Structure du Projet
Je veux une structure "Monorepo" simulée dans le dossier racine :
- `/backend` (Code Laravel)
- `/frontend` (Code Nuxt)
- `docker-compose.yml` (à la racine)

## 3. Fonctionnalités Requises (MVP)

### A. Authentification & Rôles
- Login / Logout / Forgot Password.
- Deux rôles principaux :
  1. **Admin :** Peut voir les temps de tout le monde, gérer les utilisateurs, exporter des rapports.
  2. **Employé :** Ne peut voir que ses propres temps, pointer (Clock-in/Clock-out) ou saisir manuellement.

### B. Gestion du temps (Core)
- **Pointage en direct :** Un bouton "Démarrer" / "Arrêter" qui enregistre l'heure précise.
- **Saisie manuelle :** Possibilité d'ajouter une entrée passée (Date, Heure début, Heure fin, Description, Projet associé).
- **Calcul automatique :** Durée totale par jour et par semaine.

### C. Dashboard
- **Admin :** Vue globale des heures prestées aujourd'hui par tous les employés.
- **Employé :** Récapitulatif de ses heures de la semaine et compteur en cours s'il a pointé.

## 4. Base de données (Schéma suggéré)
Propose et implémente des migrations pour :
- `users` (avec colonne role).
- `projects` (nom, client, statut).
- `time_entries` (user_id, project_id, start_time, end_time, description, duration).

## 5. Instructions Docker
Le fichier `docker-compose.yml` doit orchestrer :
- Un conteneur **PHP-FPM** (Laravel).
- Un conteneur **Nginx** (Serveur web pour l'API et Proxy pour le front si nécessaire).
- Un conteneur **Node.js** (pour le dev server Nuxt).
- Un conteneur **PostgreSQL**.
- Un conteneur **Redis**.
- Assure-toi que les réseaux (networks) permettent la communication entre l'API et la BDD/Redis.

## 6. Consignes de Code
- **Laravel :** Utilise les API Resources pour les réponses JSON. Valide les entrées via FormRequests. Code propre et commenté.
- **Nuxt :** Utilise Pinia pour le state management (auth store). Utilise `$fetch` ou `useFetch` avec un intercepteur pour gérer les tokens Bearer automatiquement.
- **UI :** Interface propre, responsive (mobile-first pour les ouvriers sur chantier).

## Action demandée
Commence par me proposer l'arborescence des fichiers et le contenu du fichier `docker-compose.yml` complet pour initialiser l'environnement. Ensuite, guide-moi étape par étape pour l'installation de Laravel et Nuxt dans cet environnement Docker.