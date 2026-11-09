#!/usr/bin/env bash
# Instala uma cópia isolada do IROYIN em Apache + MariaDB + PHP e valida as
# rotas públicas. Execute em Ubuntu 24.04 limpo, como root, após instalar os
# pacotes listados em docs/testing-e2-e3.md.

set -Eeuo pipefail

if [[ ${EUID} -ne 0 ]]; then
    echo "ERRO: execute como root (sudo bash tests/Installation/lamp-smoke.sh)." >&2
    exit 1
fi

for command_name in apache2 composer curl mariadb npm php tar; do
    if ! command -v "${command_name}" >/dev/null 2>&1; then
        echo "ERRO: pré-requisito ausente: ${command_name}" >&2
        exit 1
    fi
done

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
INSTALL_ROOT="$(mktemp -d /var/www/iroyin-lamp-smoke.XXXXXX)"
DATABASE_NAME="iroyin_lamp_smoke"
DATABASE_USER="iroyin_lamp_smoke"
DATABASE_PASSWORD="iroyin-lamp-test-only"
APACHE_SITE="/etc/apache2/sites-available/iroyin-lamp-smoke.conf"

echo "[LAMP] Origem: ${PROJECT_ROOT}"
echo "[LAMP] Instalação isolada: ${INSTALL_ROOT}"

# Copia somente fontes, sem aproveitar dependências ou configuração da origem.
tar \
    --exclude=.env \
    --exclude=.env_local \
    --exclude=.git \
    --exclude=node_modules \
    --exclude=playwright-report \
    --exclude=test-results \
    --exclude=vendor \
    -C "${PROJECT_ROOT}" -cf - . | tar -C "${INSTALL_ROOT}" -xf -

cd "${INSTALL_ROOT}"
cp .env.example .env

sed -i \
    -e 's/^APP_ENV=.*/APP_ENV=production/' \
    -e 's/^APP_DEBUG=.*/APP_DEBUG=false/' \
    -e 's|^APP_URL=.*|APP_URL=http://127.0.0.1|' \
    -e "s/^DB_DATABASE=.*/DB_DATABASE=${DATABASE_NAME}/" \
    -e "s/^DB_USERNAME=.*/DB_USERNAME=${DATABASE_USER}/" \
    -e "s/^DB_PASSWORD=.*/DB_PASSWORD=${DATABASE_PASSWORD}/" \
    .env

service mariadb start

# Os alvos têm nomes fixos e exclusivos deste smoke test para que a repetição
# seja determinística e não alcance bancos/usuários da aplicação real.
mariadb --protocol=socket -uroot <<SQL
DROP DATABASE IF EXISTS \`${DATABASE_NAME}\`;
DROP USER IF EXISTS '${DATABASE_USER}'@'localhost';
CREATE DATABASE \`${DATABASE_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '${DATABASE_USER}'@'localhost' IDENTIFIED BY '${DATABASE_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${DATABASE_NAME}\`.* TO '${DATABASE_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[LAMP] Instalando dependências PHP e JavaScript do zero..."
COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci --silent
npm run build --silent

php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force

chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} +
find storage bootstrap/cache -type f -exec chmod 664 {} +
chmod 755 "${INSTALL_ROOT}"

cat >"${APACHE_SITE}" <<APACHE
<VirtualHost *:80>
    ServerName 127.0.0.1
    DocumentRoot ${INSTALL_ROOT}/public

    <Directory ${INSTALL_ROOT}/public>
        AllowOverride All
        Options FollowSymLinks
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/iroyin-lamp-smoke-error.log
    CustomLog \${APACHE_LOG_DIR}/iroyin-lamp-smoke-access.log combined
</VirtualHost>
APACHE

a2enmod rewrite >/dev/null
a2dissite 000-default >/dev/null 2>&1 || true
a2ensite iroyin-lamp-smoke >/dev/null
apache2ctl configtest
service apache2 restart

assert_status() {
    local path="$1"
    local expected="$2"
    local actual
    local response_file="${INSTALL_ROOT}/lamp-response.tmp"
    actual="$(curl --silent --show-error --output "${response_file}" \
        --write-out '%{http_code}' "http://127.0.0.1${path}")"

    if [[ "${actual}" != "${expected}" ]]; then
        echo "ERRO: ${path} retornou ${actual}; esperado ${expected}." >&2
        cat "${response_file}" >&2
        exit 1
    fi

    echo "[OK] ${path} -> ${actual}"
}

assert_status / 200
assert_status /ping.txt 200
assert_status /api/config 200
assert_status /api/horarios 200
assert_status /api/noticias 200
assert_status /api/editais 200
assert_status /api/clima 204
assert_status /setup 200

php artisan migrate:status --no-ansi

echo "[OK] Instalação LAMP não-Docker validada em ${INSTALL_ROOT}"
