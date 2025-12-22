#!/bin/bash
set -e

echo "Waiting for database to be ready..."
until php /var/www/html/bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

echo "Creating/updating database schema..."
php /var/www/html/bin/console doctrine:schema:update --force --complete

echo "Starting Apache..."
exec apache2-foreground
