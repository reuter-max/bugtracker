#!/bin/sh
set -e

if [ ! -d vendor ]; then
    echo "Installing Composer Dependencies..."
    composer install --no-interaction
fi

echo "Waiting for Database..."
until php -r "
    try {
        new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
    } catch (\Throwable \$e) {
        exit(1);
    }
"; do
    sleep 3
	echo "Database not accessible - Waiting 3 more seconds"
done
echo "Database is accessible."

echo "Running Migrations..."
php bin/migrate.php migrate --no-interaction

echo "Running Seed..."
php bin/seed.php

exec "$@"