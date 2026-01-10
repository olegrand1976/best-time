#!/bin/bash

# Script pour installer Laravel 11 dans le conteneur PHP
echo "Installing Laravel 11..."

# Installer Laravel via Composer
composer create-project laravel/laravel:^11.0 . --prefer-dist --no-interaction

# Installer les dépendances supplémentaires
composer require laravel/sanctum

echo "Laravel 11 installed successfully!"
echo "Next steps:"
echo "1. Copy env.template to .env"
echo "2. Generate APP_KEY: php artisan key:generate"
echo "3. Run migrations: php artisan migrate"
