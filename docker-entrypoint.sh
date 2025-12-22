#!/bin/bash
set -e

# Install/update dependencies if needed
if [ ! -d "/var/www/html/vendor" ] || [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader
fi

# Ensure symfony/runtime is installed
if [ ! -d "/var/www/html/vendor/symfony/runtime" ]; then
    echo "Installing Symfony Runtime..."
    COMPOSER_ALLOW_SUPERUSER=1 composer require symfony/runtime --no-interaction
fi

echo "Waiting for database to be ready..."
until mysql -h database -u ${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} --skip-ssl -e "SELECT 1" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

echo "Creating/updating database schema..."
php /var/www/html/bin/console doctrine:schema:update --force --complete

echo "Starting Apache..."
exec apache2-foreground
