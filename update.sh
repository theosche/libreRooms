#!/bin/bash
set -e

echo "Mise à jour LibreRooms"

git pull origin main

composer install --no-dev --optimize-autoloader

npm ci
npm run build

php artisan migrate --force

php artisan optimize:clear
php artisan optimize

sudo chown -R :www-data .
sudo chmod -R 755 .
sudo chown -R www-data:www-data storage bootstrap/cache .env
sudo chmod -R 775 storage bootstrap/cache .env

echo "Mise à jour terminée"
