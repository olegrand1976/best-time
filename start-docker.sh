#!/bin/bash

# Best Time - Script de démarrage Docker
# Ce script configure et démarre toute la stack Docker pour le développement

set -e

echo "🚀 Best Time - Démarrage de la stack Docker"
echo "=============================================="

# Couleurs pour les messages
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

success() {
    echo -e "${GREEN}✓${NC} $1"
}

warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Vérifier que Docker est installé
if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose n'est pas installé. Veuillez l'installer d'abord."
    exit 1
fi

success "Docker et Docker Compose sont installés"

# Créer le fichier .env pour Laravel si nécessaire
if [ ! -f backend/.env ]; then
    info "Création du fichier .env pour Laravel..."
    cat > backend/.env << 'EOF'
APP_NAME="Best Time"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=besttime
DB_USERNAME=postgres
DB_PASSWORD=postgres

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@besttime.test"
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:8000
SESSION_DOMAIN=localhost
EOF
    success "Fichier .env créé"
else
    info "Fichier .env existe déjà"
fi

# Arrêter les conteneurs existants
info "Arrêt des conteneurs existants..."
docker-compose down 2>/dev/null || true

# Construire les images
info "Construction des images Docker..."
docker-compose build --no-cache

# Démarrer les services
info "Démarrage des services..."
docker-compose up -d

# Attendre que PostgreSQL soit prêt
info "Attente de PostgreSQL..."
sleep 5

# Installer les dépendances Composer
info "Installation des dépendances Composer..."
docker-compose exec -T php-fpm composer install --no-interaction

# Générer la clé d'application
info "Génération de la clé d'application Laravel..."
docker-compose exec -T php-fpm php artisan key:generate

# Exécuter les migrations
info "Exécution des migrations..."
docker-compose exec -T php-fpm php artisan migrate --force

# Créer les utilisateurs de test
info "Création des utilisateurs de test..."
docker-compose exec -T php-fpm php artisan db:seed --force

# Optimiser Laravel
info "Optimisation de Laravel..."
docker-compose exec -T php-fpm php artisan config:cache
docker-compose exec -T php-fpm php artisan route:cache

# Afficher l'adresse IP locale
IP_ADDRESS=$(hostname -I | awk '{print $1}')

echo ""
echo "=============================================="
success "Stack Docker démarrée avec succès!"
echo "=============================================="
echo ""
echo "📱 Configuration pour l'application mobile Flutter:"
echo ""
echo "   Modifier lib/config/app_config.dart:"
echo "   static const String apiBaseUrl = 'http://${IP_ADDRESS}:8000/api';"
echo ""
echo "🌐 Services disponibles:"
echo ""
echo "   • API Laravel:        http://localhost:8000"
echo "   • API (mobile):       http://${IP_ADDRESS}:8000"
echo "   • Frontend Nuxt:      http://localhost:3000"
echo "   • MailHog:            http://localhost:8025"
echo "   • PostgreSQL:         localhost:5432"
echo "   • Redis:              localhost:6379"
echo ""
echo "🔐 Identifiants de test:"
echo ""
echo "   • Admin:     admin@example.com / password"
echo "   • Employé:   employee@example.com / password"
echo ""
echo "📋 Commandes utiles:"
echo ""
echo "   • Voir les logs:      docker-compose logs -f"
echo "   • Arrêter:            docker-compose down"
echo "   • Redémarrer:         docker-compose restart"
echo "   • Shell Laravel:      docker-compose exec php-fpm sh"
echo ""
warning "N'oubliez pas d'autoriser le port 8000 dans votre firewall!"
echo ""
