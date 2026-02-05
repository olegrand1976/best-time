# Best Time - Guide Docker pour développement mobile

## 🚀 Démarrage rapide

### 1. Démarrer toute la stack

```bash
./start-docker.sh
```

Ce script va:
- ✅ Créer le fichier `.env` pour Laravel
- ✅ Construire les images Docker
- ✅ Démarrer tous les services
- ✅ Installer les dépendances Composer
- ✅ Exécuter les migrations
- ✅ Créer les utilisateurs de test
- ✅ Afficher votre adresse IP pour la configuration mobile

### 2. Configurer l'application Flutter

Modifier `flutter/lib/config/app_config.dart`:

```dart
static const String apiBaseUrl = 'http://VOTRE_IP:8000/api';
```

Remplacez `VOTRE_IP` par l'adresse IP affichée par le script de démarrage.

### 3. Lancer l'application mobile

```bash
cd flutter
flutter run
```

## 📦 Services disponibles

| Service | URL Locale | URL Mobile | Description |
|---------|-----------|------------|-------------|
| **API Laravel** | http://localhost:8000 | http://VOTRE_IP:8000 | Backend API |
| **Frontend Nuxt** | http://localhost:3020 | http://VOTRE_IP:3020 | Interface web |
| **MailHog** | http://localhost:8025 | http://VOTRE_IP:8025 | Emails de test |
| **PostgreSQL** | localhost:5432 | VOTRE_IP:5432 | Base de données |
| **Redis** | localhost:6379 | VOTRE_IP:6379 | Cache & queues |

## 🔐 Identifiants de test

Après le démarrage, utilisez ces identifiants:

- **Admin**: `admin@example.com` / `password`
- **Employé**: `employee@example.com` / `password`

## 🛠️ Commandes Docker utiles

### Voir les logs en temps réel
```bash
docker-compose logs -f

# Logs d'un service spécifique
docker-compose logs -f nginx
docker-compose logs -f php-fpm
```

### Arrêter la stack
```bash
docker-compose down
```

### Redémarrer un service
```bash
docker-compose restart nginx
docker-compose restart php-fpm
```

### Accéder au shell d'un conteneur
```bash
# Laravel
docker-compose exec php-fpm sh

# PostgreSQL
docker-compose exec postgres psql -U postgres -d besttime

# Redis
docker-compose exec redis redis-cli
```

### Exécuter des commandes Artisan
```bash
docker-compose exec php-fpm php artisan migrate
docker-compose exec php-fpm php artisan db:seed
docker-compose exec php-fpm php artisan cache:clear
docker-compose exec php-fpm php artisan route:list
```

### Réinitialiser la base de données
```bash
docker-compose exec php-fpm php artisan migrate:fresh --seed
```

## 🔧 Configuration avancée

### Modifier les ports

Éditez `docker-compose.yml` pour changer les ports exposés:

```yaml
nginx:
  ports:
    - "8000:80"  # Changez 8000 par le port désiré
```

### Ajouter des variables d'environnement

Éditez `backend/.env` pour configurer Laravel:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Activer HTTPS (pour production)

1. Générer des certificats SSL
2. Modifier la configuration Nginx
3. Mettre à jour `APP_URL` dans `.env`

## 🐛 Dépannage

### L'API n'est pas accessible depuis le mobile

1. Vérifier que le firewall autorise le port 8000:
   ```bash
   sudo ufw allow 8000
   ```

2. Vérifier l'adresse IP:
   ```bash
   hostname -I
   ```

3. Tester depuis le mobile:
   ```bash
   curl http://VOTRE_IP:8000/api/health
   ```

### Erreur de connexion à PostgreSQL

```bash
# Vérifier que PostgreSQL est démarré
docker-compose ps postgres

# Voir les logs
docker-compose logs postgres

# Redémarrer PostgreSQL
docker-compose restart postgres
```

### Erreur "Class not found"

```bash
# Régénérer l'autoload
docker-compose exec php-fpm composer dump-autoload

# Vider le cache
docker-compose exec php-fpm php artisan cache:clear
docker-compose exec php-fpm php artisan config:clear
```

### Problème de permissions

```bash
# Corriger les permissions
docker-compose exec php-fpm chown -R www-data:www-data /var/www/html/storage
docker-compose exec php-fpm chmod -R 775 /var/www/html/storage
```

## 📱 Test de l'application mobile

### 1. Vérifier la connectivité

Depuis votre appareil mobile, ouvrir le navigateur et accéder à:
```
http://VOTRE_IP:8000/api/health
```

Vous devriez voir une réponse JSON.

### 2. Tester l'authentification

```bash
curl -X POST http://VOTRE_IP:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

### 3. Générer un QR Code de test

```bash
# Récupérer un token d'authentification
TOKEN=$(curl -s -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq -r '.token')

# Générer un QR code pour le projet 1
curl -X POST http://localhost:8000/api/admin/projects/1/qr-code/generate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"
```

## 🔄 Mise à jour de la stack

### Reconstruire les images
```bash
docker-compose build --no-cache
docker-compose up -d
```

### Mettre à jour les dépendances
```bash
docker-compose exec php-fpm composer update
docker-compose exec node npm update
```

## 🧹 Nettoyage

### Supprimer tous les conteneurs et volumes
```bash
docker-compose down -v
```

### Supprimer les images
```bash
docker-compose down --rmi all
```

### Nettoyage complet Docker
```bash
docker system prune -a --volumes
```

## 📊 Monitoring

### Voir l'utilisation des ressources
```bash
docker stats
```

### Inspecter un conteneur
```bash
docker inspect best-time-nginx
```

## 🚀 Déploiement en production

Pour un déploiement en production:

1. Modifier `APP_ENV=production` dans `.env`
2. Désactiver `APP_DEBUG=false`
3. Configurer un domaine avec HTTPS
4. Utiliser des secrets sécurisés pour les mots de passe
5. Configurer des sauvegardes automatiques de PostgreSQL
6. Mettre en place un reverse proxy (Traefik, Nginx Proxy Manager)

## 📝 Notes importantes

- ⚠️ **Ne jamais** exposer les ports de base de données en production
- ⚠️ **Toujours** utiliser HTTPS en production
- ⚠️ **Changer** tous les mots de passe par défaut
- ⚠️ **Sauvegarder** régulièrement la base de données
- ⚠️ **Monitorer** les logs en production

## 🤝 Support

Pour toute question ou problème, consulter:
- Documentation Laravel: https://laravel.com/docs
- Documentation Docker: https://docs.docker.com
- Documentation Flutter: https://flutter.dev/docs
