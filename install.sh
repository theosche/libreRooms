#!/bin/bash
set -e

echo "Installation de LibreRooms"

git config core.autocrlf false
git config core.filemode false
composer install --no-dev --optimize-autoloader
npm ci
npm run build
if [ -f .env ]; then
  echo ".env existe déjà – installation annulée"
  exit 1
fi
cp .env.example .env
php artisan key:generate
php artisan optimize
php artisan storage:link
sudo chown -R :www-data .
sudo chmod -R 755 .
sudo chown -R www-data:www-data storage bootstrap/cache .env
sudo chmod -R 775 storage bootstrap/cache .env

echo "Installation effectuée avec succès"
echo "Configurez LibreRooms en y accédant depuis un navigateur"
