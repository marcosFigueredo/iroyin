#!/bin/sh
set -e

if [ ! -f .env ]; then
    echo "[IROYIN] .env não encontrado no container — criando a partir de .env.docker..."
    cp .env.docker .env
fi

if [ -z "$APP_KEY" ]; then
    echo "[IROYIN] APP_KEY vazia no ambiente do container — gerando em runtime..."
    export APP_KEY=$(php artisan key:generate --show)
fi

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
