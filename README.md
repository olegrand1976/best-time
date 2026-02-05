# Best Time - Gestion de temps professionnelle

Application complète de gestion de temps avec **backend Laravel**, **frontend Nuxt 3**, et **application mobile Flutter**.

## 🎯 Fonctionnalités

- ✅ Authentification sécurisée avec rôles (Admin, Responsable, Team Leader, Ouvrier)
- ✅ Pointage en temps réel (Clock-in/Clock-out)
- ✅ Scan de QR Code pour pointage rapide
- ✅ Géolocalisation automatique et géofencing
- ✅ Dashboard avec résumé hebdomadaire
- ✅ Gestion de projets et clients
- ✅ Rapports et exports
- ✅ Conforme à la directive européenne 2003/88/CE

## 📋 Stack Technique

- **Backend:** Laravel 11 + PostgreSQL + Redis
- **Frontend Web:** Nuxt 3 + TypeScript + Tailwind CSS
- **Mobile:** Flutter 3.5+ (iOS & Android)
- **Infrastructure:** Docker + Docker Compose

## 🚀 Démarrage rapide (Docker)

### 1. Prérequis

- Docker et Docker Compose installés
- Make (optionnel mais recommandé)

### 2. Démarrer la stack complète

```bash
# Avec le script de démarrage (recommandé)
./start-docker.sh

# OU avec Make
make start

# OU manuellement
docker-compose up -d
```

Le script va automatiquement:
- Créer le fichier `.env` pour Laravel
- Installer les dépendances Composer
- Exécuter les migrations
- Créer les utilisateurs de test
- Afficher votre IP pour la configuration mobile

### 3. Services disponibles

| Service | URL | Description |
|---------|-----|-------------|
| **API Laravel** | http://localhost:8000 | Backend API |
| **Frontend Nuxt** | http://localhost:3020 | Interface web |
| **MailHog** | http://localhost:8025 | Emails de test |
| **PostgreSQL** | localhost:5432 | Base de données |
| **Redis** | localhost:6379 | Cache & queues |

### 4. Identifiants de test

- **Admin:** `admin@example.com` / `password`
- **Employé:** `employee@example.com` / `password`

## 📱 Application Mobile Flutter

### Installation

```bash
cd flutter
flutter pub get
```

### Configuration

Modifier `flutter/lib/config/app_config.dart`:

```dart
// Pour émulateur
static const String apiBaseUrl = 'http://localhost:8000/api';

// Pour appareil physique (remplacer par votre IP)
static const String apiBaseUrl = 'http://192.168.1.X:8000/api';
```

**Astuce:** Utilisez `make ip` pour afficher votre adresse IP.

### Lancer l'application

```bash
# Avec Make
make flutter-run

# OU directement
cd flutter
flutter run
```

## 🛠️ Commandes utiles (Make)

```bash
make help              # Afficher toutes les commandes
make start             # Démarrer la stack
make stop              # Arrêter la stack
make logs              # Voir les logs
make shell             # Shell Laravel
make migrate           # Exécuter les migrations
make fresh             # Réinitialiser la DB
make qr-generate       # Générer un QR code de test
make ip                # Afficher l'IP pour mobile
make health            # Vérifier la santé des services
make backup-db         # Sauvegarder la base de données
```

Voir le [Makefile](Makefile) pour toutes les commandes disponibles.

## 📚 Documentation

- **[DOCKER.md](DOCKER.md)** - Guide complet Docker avec dépannage
- **[flutter/README.md](flutter/README.md)** - Documentation de l'app mobile
- **[CONFORMITE_REGLEMENTAIRE.md](CONFORMITE_REGLEMENTAIRE.md)** - Conformité européenne

## 🏗️ Structure du projet

```
best-time-1/
├── backend/              # API Laravel
│   ├── app/
│   ├── database/
│   └── routes/
├── frontend/             # Interface Nuxt 3
│   ├── components/
│   ├── pages/
│   └── stores/
├── flutter/              # Application mobile
│   ├── lib/
│   │   ├── config/
│   │   ├── data/
│   │   └── presentation/
│   └── pubspec.yaml
├── docker/               # Configuration Docker
│   ├── nginx/
│   └── php/
├── scripts/              # Scripts utilitaires
├── docker-compose.yml    # Orchestration Docker
├── Makefile             # Commandes simplifiées
└── start-docker.sh      # Script de démarrage
```

## 🔧 Développement

### Backend (Laravel)

```bash
# Accéder au shell
make shell

# Exécuter des commandes Artisan
docker-compose exec php-fpm php artisan route:list
docker-compose exec php-fpm php artisan migrate
docker-compose exec php-fpm php artisan test
```

### Frontend (Nuxt)

```bash
cd frontend
npm install
npm run dev
```

### Mobile (Flutter)

```bash
cd flutter
flutter pub get
flutter run
flutter test
```

## 📱 Test de l'application mobile

### 1. Vérifier la connectivité

Depuis votre appareil mobile, ouvrir le navigateur:
```
http://VOTRE_IP:8000/api/health
```

### 2. Générer un QR Code de test

```bash
make qr-generate
```

Ou manuellement:
```bash
./scripts/generate-qr.sh
```

### 3. Scanner le QR Code

1. Ouvrir l'app Flutter
2. Se connecter
3. Cliquer sur "Scanner QR"
4. Scanner le QR code généré
5. Le pointage se fait automatiquement!

## 🔒 Sécurité

- Tokens JWT avec Laravel Sanctum
- Stockage sécurisé (flutter_secure_storage)
- Validation des entrées (FormRequests)
- Protection CSRF
- Rate limiting sur l'API
- HTTPS recommandé en production

## 🌍 Conformité RGPD

L'application respecte la directive européenne 2003/88/CE:
- ✅ Horodatage automatique et fiable
- ✅ Traçabilité complète
- ✅ Géolocalisation avec consentement
- ✅ Données sécurisées

Voir [CONFORMITE_REGLEMENTAIRE.md](CONFORMITE_REGLEMENTAIRE.md) pour plus de détails.

## 🐛 Dépannage

### L'API n'est pas accessible depuis le mobile

```bash
# Vérifier le firewall
sudo ufw allow 8000

# Vérifier l'IP
make ip

# Tester la connectivité
curl http://VOTRE_IP:8000/api/health
```

### Erreur de migration

```bash
# Réinitialiser la base de données
make fresh

# Ou manuellement
docker-compose exec php-fpm php artisan migrate:fresh --seed
```

### Problème de permissions

```bash
docker-compose exec php-fpm chown -R www-data:www-data /var/www/html/storage
docker-compose exec php-fpm chmod -R 775 /var/www/html/storage
```

Voir [DOCKER.md](DOCKER.md) pour plus de solutions.

## 📦 Déploiement en production

1. Modifier `APP_ENV=production` dans `.env`
2. Désactiver `APP_DEBUG=false`
3. Configurer HTTPS avec certificats SSL
4. Utiliser des secrets sécurisés
5. Configurer des sauvegardes automatiques
6. Mettre en place un monitoring

## 🤝 Contribution

Ce projet suit les conventions:
- PSR-12 pour PHP
- ESLint pour JavaScript/TypeScript
- Dart style guide pour Flutter

## 📄 Licence

Propriétaire - Best Time © 2026

## 🆘 Support

Pour toute question:
- Consulter la documentation dans `/docs`
- Voir les guides [DOCKER.md](DOCKER.md) et [flutter/README.md](flutter/README.md)
- Vérifier les issues GitHub