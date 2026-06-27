#!/bin/sh
set -e

echo "[IROYIN] Generating app key..."
php artisan key:generate --force

echo "[IROYIN] Running migrations..."
php artisan migrate --force

echo "[IROYIN] Seeding default data (feeds + agencies)..."
php artisan db:seed --force

echo "[IROYIN] Seeding demo data..."
php artisan db:seed --class=DemoSeeder --force

echo "[IROYIN] Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "[IROYIN] Clearing caches..."
php artisan config:clear
php artisan cache:clear

echo "[IROYIN] Ready. Starting PHP-FPM..."
exec "$@"
