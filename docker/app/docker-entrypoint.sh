#!/bin/sh
set -e

if [ ! -d vendor ]; then
    echo "Installiere Composer-Abhaengigkeiten..."
    composer install --no-interaction
fi

echo "Warte auf Datenbank..."
until php -r "
    try {
        new PDO(
            'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_NAME'),
            getenv('DB_USER'),
            getenv('DB_PASSWORD')
        );
    } catch (\Throwable \$e) {
        exit(1);
    }
"; do
    sleep 1
done
echo "Datenbank ist erreichbar."

echo "Fuehre Migrationen aus..."
php bin/migrate.php migrate --no-interaction

echo "Fuehre Seed aus..."
php bin/seed.php

exec "$@"