.PHONY: help start stop restart logs shell migrate seed fresh test clean ip

# Couleurs pour l'affichage
BLUE := \033[0;34m
GREEN := \033[0;32m
NC := \033[0m # No Color

help: ## Afficher cette aide
	@echo "$(BLUE)Best Time - Commandes disponibles:$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-15s$(NC) %s\n", $$1, $$2}'
	@echo ""

start: ## Démarrer toute la stack Docker
	@echo "$(BLUE)🚀 Démarrage de la stack...$(NC)"
	@./start-docker.sh

stop: ## Arrêter tous les conteneurs
	@echo "$(BLUE)🛑 Arrêt de la stack...$(NC)"
	@docker-compose down

restart: ## Redémarrer tous les conteneurs
	@echo "$(BLUE)🔄 Redémarrage de la stack...$(NC)"
	@docker-compose restart

logs: ## Voir les logs en temps réel
	@docker-compose logs -f

logs-api: ## Voir les logs de l'API Laravel
	@docker-compose logs -f php-fpm nginx

logs-db: ## Voir les logs de PostgreSQL
	@docker-compose logs -f postgres

shell: ## Accéder au shell Laravel
	@docker-compose exec php-fpm sh

shell-db: ## Accéder au shell PostgreSQL
	@docker-compose exec postgres psql -U postgres -d besttime

migrate: ## Exécuter les migrations
	@echo "$(BLUE)📊 Exécution des migrations...$(NC)"
	@docker-compose exec php-fpm php artisan migrate

migrate-fresh: ## Réinitialiser la base de données
	@echo "$(BLUE)⚠️  Réinitialisation de la base de données...$(NC)"
	@docker-compose exec php-fpm php artisan migrate:fresh

seed: ## Créer les données de test
	@echo "$(BLUE)🌱 Création des données de test...$(NC)"
	@docker-compose exec php-fpm php artisan db:seed

fresh: ## Réinitialiser la DB et créer les données de test
	@echo "$(BLUE)🔄 Réinitialisation complète...$(NC)"
	@docker-compose exec php-fpm php artisan migrate:fresh --seed

cache-clear: ## Vider tous les caches
	@echo "$(BLUE)🧹 Nettoyage des caches...$(NC)"
	@docker-compose exec php-fpm php artisan cache:clear
	@docker-compose exec php-fpm php artisan config:clear
	@docker-compose exec php-fpm php artisan route:clear
	@docker-compose exec php-fpm php artisan view:clear

optimize: ## Optimiser Laravel
	@echo "$(BLUE)⚡ Optimisation...$(NC)"
	@docker-compose exec php-fpm php artisan config:cache
	@docker-compose exec php-fpm php artisan route:cache
	@docker-compose exec php-fpm php artisan view:cache

test: ## Exécuter les tests
	@echo "$(BLUE)🧪 Exécution des tests...$(NC)"
	@docker-compose exec php-fpm php artisan test

composer-install: ## Installer les dépendances Composer
	@echo "$(BLUE)📦 Installation des dépendances Composer...$(NC)"
	@docker-compose exec php-fpm composer install

composer-update: ## Mettre à jour les dépendances Composer
	@echo "$(BLUE)📦 Mise à jour des dépendances Composer...$(NC)"
	@docker-compose exec php-fpm composer update

npm-install: ## Installer les dépendances NPM (Frontend)
	@echo "$(BLUE)📦 Installation des dépendances NPM...$(NC)"
	@docker-compose exec node npm install

flutter-get: ## Installer les dépendances Flutter
	@echo "$(BLUE)📦 Installation des dépendances Flutter...$(NC)"
	@cd flutter && flutter pub get

flutter-run: ## Lancer l'application Flutter
	@echo "$(BLUE)📱 Lancement de l'application Flutter...$(NC)"
	@cd flutter && flutter run

qr-generate: ## Générer un QR code pour le projet 1
	@echo "$(BLUE)📷 Génération d'un QR code...$(NC)"
	@./scripts/generate-qr.sh

ip: ## Afficher l'adresse IP pour la configuration mobile
	@echo "$(BLUE)📱 Configuration mobile:$(NC)"
	@echo ""
	@echo "Modifier flutter/lib/config/app_config.dart:"
	@echo "static const String apiBaseUrl = 'http://$(shell hostname -I | awk '{print $$1}'):8000/api';"
	@echo ""

status: ## Afficher le statut des conteneurs
	@docker-compose ps

clean: ## Nettoyer les conteneurs et volumes
	@echo "$(BLUE)🧹 Nettoyage complet...$(NC)"
	@docker-compose down -v
	@docker system prune -f

rebuild: ## Reconstruire les images Docker
	@echo "$(BLUE)🔨 Reconstruction des images...$(NC)"
	@docker-compose build --no-cache
	@docker-compose up -d

health: ## Vérifier la santé des services
	@echo "$(BLUE)🏥 Vérification de la santé des services...$(NC)"
	@curl -s http://localhost:8000/api/health || echo "❌ API non accessible"
	@curl -s http://localhost:3000 > /dev/null && echo "✅ Frontend accessible" || echo "❌ Frontend non accessible"
	@docker-compose exec postgres pg_isready -U postgres && echo "✅ PostgreSQL OK" || echo "❌ PostgreSQL KO"
	@docker-compose exec redis redis-cli ping && echo "✅ Redis OK" || echo "❌ Redis KO"

backup-db: ## Sauvegarder la base de données
	@echo "$(BLUE)💾 Sauvegarde de la base de données...$(NC)"
	@mkdir -p backups
	@docker-compose exec -T postgres pg_dump -U postgres besttime > backups/backup-$(shell date +%Y%m%d-%H%M%S).sql
	@echo "$(GREEN)✓ Sauvegarde créée dans backups/$(NC)"

restore-db: ## Restaurer la dernière sauvegarde
	@echo "$(BLUE)📥 Restauration de la base de données...$(NC)"
	@docker-compose exec -T postgres psql -U postgres besttime < $(shell ls -t backups/*.sql | head -1)
	@echo "$(GREEN)✓ Base de données restaurée$(NC)"
