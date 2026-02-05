# 🚀 Quick Start - Best Time

Guide de démarrage ultra-rapide pour lancer toute la stack en 5 minutes.

## ⚡ Démarrage en 3 commandes

```bash
# 1. Démarrer Docker
./start-docker.sh

# 2. Installer Flutter (dans un nouveau terminal)
cd flutter && flutter pub get

# 3. Lancer l'app mobile
flutter run
```

C'est tout! 🎉

## 📱 Configuration mobile

Le script `start-docker.sh` affiche votre IP automatiquement. Copier cette ligne dans `flutter/lib/config/app_config.dart`:

```dart
static const String apiBaseUrl = 'http://VOTRE_IP:8000/api';
```

## 🔐 Se connecter

- **Email:** `admin@example.com`
- **Mot de passe:** `password`

## 🧪 Tester le scan QR

```bash
# Générer un QR code
make qr-generate

# Scanner avec l'app mobile
# → Pointage automatique!
```

## 📊 Accéder aux services

- **API:** http://localhost:8000
- **Frontend:** http://localhost:3020
- **Emails:** http://localhost:8025

## 🛠️ Commandes essentielles

```bash
make help          # Voir toutes les commandes
make logs          # Voir les logs
make shell         # Shell Laravel
make stop          # Arrêter
make fresh         # Réinitialiser la DB
```

## 🐛 Problème?

```bash
# Redémarrer tout
make restart

# Voir les logs
make logs

# Vérifier la santé
make health
```

## 📚 Documentation complète

- [README.md](README.md) - Documentation principale
- [DOCKER.md](DOCKER.md) - Guide Docker complet
- [flutter/README.md](flutter/README.md) - Guide Flutter

---

**Besoin d'aide?** Consultez [DOCKER.md](DOCKER.md) pour le dépannage complet.
