#!/bin/bash
set -e

echo "==> Installing Composer dependencies..."
composer install --no-interaction --optimize-autoloader --no-scripts

echo "==> Dumping autoloader..."
composer dump-autoload --optimize --no-interaction

echo "==> Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "==> Installing bundle assets..."
php bin/console assets:install --no-interaction || true

echo "==> Installing importmap assets..."
php bin/console importmap:install --no-interaction || true

echo "==> Clearing cache..."
php bin/console cache:clear

echo "==> Starting PHP-FPM..."
exec "$@"
