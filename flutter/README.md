# Best Time - Application Flutter

Application mobile professionnelle de gestion de temps avec scan QR et géolocalisation.

## 🎯 Fonctionnalités

### ✅ Implémentées
- **Authentification sécurisée** avec Laravel Sanctum
- **Pointage en temps réel** (clock-in/clock-out) avec timer actif
- **Scan de QR Code** pour pointage rapide sur projet
- **Géolocalisation automatique** lors du pointage
- **Géofencing configurable** (validation de la position)
- **Dashboard** avec résumé hebdomadaire
- **Liste des temps** avec filtres et suppression
- **Interface moderne** Material Design 3
- **Support multi-rôles** (admin, responsable, team_leader, ouvrier)

### 🚧 À venir
- Saisie manuelle de temps
- Mode hors-ligne avec synchronisation
- Rapports exportables
- Notifications push
- Support multi-langues (FR, EN, DE, NL, IT, PT)

## 📋 Prérequis

- Flutter SDK 3.5.4 ou supérieur
- Dart 3.0+
- Android Studio / Xcode (pour émulateurs)
- Backend Laravel en cours d'exécution

## 🚀 Installation

### 1. Cloner et installer les dépendances

```bash
cd /home/olivier/projets/best-time-1/flutter
flutter pub get
```

### 2. Configuration de l'API

Modifier l'URL de l'API dans `lib/config/app_config.dart`:

```dart
static const String apiBaseUrl = 'http://VOTRE_IP:8000/api';
```

**Important**: Pour tester sur un appareil physique, remplacez `localhost` par l'adresse IP de votre machine.

### 3. Backend - Exécuter les migrations

```bash
cd ../backend
composer install
php artisan migrate
php artisan db:seed  # Pour créer des utilisateurs de test
php artisan serve
```

### 4. Lancer l'application

#### Sur émulateur Android
```bash
flutter run
```

#### Sur appareil physique
```bash
flutter run --release
```

## 🔐 Identifiants de test

Après avoir exécuté `php artisan db:seed`:

- **Admin**: `admin@example.com` / `password`
- **Employé**: `employee@example.com` / `password`

## 📱 Utilisation

### Pointage standard
1. Se connecter avec vos identifiants
2. Sur le dashboard, cliquer sur "Pointer"
3. Sélectionner un projet (optionnel)
4. Ajouter une description (optionnel)
5. Cliquer sur "Commencer le travail"
6. La localisation est capturée automatiquement
7. Pour arrêter, cliquer sur "Arrêter" dans le timer actif

### Pointage via QR Code
1. Sur le dashboard, cliquer sur "Scanner QR"
2. Scanner le QR code du projet
3. Le pointage est automatique avec:
   - Projet assigné automatiquement
   - Localisation capturée
   - Validation géofencing (si configuré)

### Générer un QR Code (Admin)

Via l'API:
```bash
curl -X POST http://localhost:8000/api/admin/projects/1/qr-code/generate \
  -H "Authorization: Bearer VOTRE_TOKEN" \
  -H "Content-Type: application/json"
```

Le QR code retourné contient:
```json
{
  "type": "best_time_project",
  "token": "...",
  "project_id": 1,
  "project_name": "Nom du projet"
}
```

## ⚙️ Configuration Backend

### Activer la géolocalisation pour une organisation

```sql
UPDATE organizations 
SET location_required = true,
    geofencing_enabled = true,
    geofencing_radius = 100  -- en mètres
WHERE id = 1;
```

### Configurer le géofencing pour un projet

```sql
UPDATE projects 
SET latitude = 50.8503,
    longitude = 4.3517,
    geofence_radius = 50  -- en mètres
WHERE id = 1;
```

## 🏗️ Architecture

```
lib/
├── config/              # Configuration (API, thème)
├── data/
│   ├── models/          # Modèles de données
│   └── services/        # Services API
├── presentation/
│   ├── providers/       # State management (Riverpod)
│   ├── screens/         # Écrans de l'app
│   └── widgets/         # Widgets réutilisables
└── main.dart           # Point d'entrée
```

## 🔧 Dépendances principales

- `flutter_riverpod`: State management
- `http`: Client HTTP
- `flutter_secure_storage`: Stockage sécurisé des tokens
- `qr_code_scanner`: Scan de QR codes
- `geolocator`: Géolocalisation
- `permission_handler`: Gestion des permissions

## 📝 Permissions

### Android (`android/app/src/main/AndroidManifest.xml`)
- `CAMERA`: Scan QR code
- `ACCESS_FINE_LOCATION`: Géolocalisation précise
- `INTERNET`: Communication API

### iOS (`ios/Runner/Info.plist`)
- `NSCameraUsageDescription`: Scan QR code
- `NSLocationWhenInUseUsageDescription`: Géolocalisation

## 🐛 Dépannage

### Erreur de connexion à l'API
- Vérifier que le backend Laravel est en cours d'exécution
- Sur appareil physique, utiliser l'IP de votre machine au lieu de `localhost`
- Vérifier que le firewall autorise les connexions sur le port 8000

### Permissions refusées
- Android: Aller dans Paramètres > Applications > Best Time > Permissions
- iOS: Paramètres > Best Time > Activer Caméra et Localisation

### QR Code non reconnu
- Vérifier que le QR code a été généré via l'API
- S'assurer que le projet est actif (`status = 'active'`)
- Vérifier l'éclairage lors du scan

## 📦 Build pour production

### Android (APK)
```bash
flutter build apk --release
```

### Android (App Bundle pour Play Store)
```bash
flutter build appbundle --release
```

### iOS (App Store)
```bash
flutter build ios --release
```

## 🔒 Conformité RGPD

L'application respecte les exigences de la directive européenne 2003/88/CE:
- ✅ Horodatage automatique et fiable
- ✅ Traçabilité complète des actions
- ✅ Géolocalisation avec consentement
- ✅ Données stockées de manière sécurisée

## 📄 Licence

Propriétaire - Best Time © 2026

## 🤝 Support

Pour toute question ou problème, contacter l'équipe de développement.
