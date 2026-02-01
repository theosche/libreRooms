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
echo "Etapes suivantes:"
echo "1. Créer une base de données avec un user"
echo "2. Diriger un serveur web vers le dossier public/"
echo "3. Configurer  LibreRooms en y accédant depuis un navigateur"
echo "4. Ajouter le scheduler dans cron (user www-data) - par ex:"
echo '   sudo crontab -u www-data -e'
echo '   * * * * * cd /var/www/html/libreRooms && php artisan schedule:run >> /dev/null 2>&1'
