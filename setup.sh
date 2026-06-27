#!/usr/bin/env bash
# =============================================================================
# IROYIN — Setup Script
# Installs dependencies, configures the environment, seeds demo data,
# and runs the test suite.
#
# Usage:
#   chmod +x setup.sh
#   ./setup.sh
#
# Optional flags:
#   --no-demo    Skip demo data seeding
#   --no-tests   Skip test suite
# =============================================================================

set -e

SEED_DEMO=true
RUN_TESTS=true

for arg in "$@"; do
    case $arg in
        --no-demo)   SEED_DEMO=false ;;
        --no-tests)  RUN_TESTS=false ;;
    esac
done

# ── Colors ────────────────────────────────────────────────────────────────────
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m'

ok()   { echo -e "${GREEN}✔${NC}  $1"; }
info() { echo -e "${YELLOW}▶${NC}  $1"; }
fail() { echo -e "${RED}✘  $1${NC}"; exit 1; }
hr()   { echo -e "\n${BOLD}$1${NC}"; echo "────────────────────────────────────────"; }

# ── Banner ────────────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}  IROYIN — Institutional Information Display System${NC}"
echo "  Setup Script"
echo ""

# ── 1. Prerequisites ──────────────────────────────────────────────────────────
hr "1/6  Checking prerequisites"

command -v php  >/dev/null 2>&1 || fail "PHP not found. Install PHP >= 8.2"
command -v composer >/dev/null 2>&1 || fail "Composer not found. See https://getcomposer.org"
command -v node >/dev/null 2>&1 || fail "Node.js not found. Install Node.js >= 18"
command -v npm  >/dev/null 2>&1 || fail "npm not found."

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
ok "PHP $PHP_VERSION"
ok "Composer $(composer --version --no-ansi | awk '{print $3}')"
ok "Node.js $(node -v)"
ok "npm $(npm -v)"

# ── 2. Environment ────────────────────────────────────────────────────────────
hr "2/6  Environment"

if [ ! -f .env ]; then
    cp .env.example .env
    ok ".env created from .env.example"
else
    ok ".env already exists — skipping"
fi

info "Generating application key..."
php artisan key:generate --ansi
ok "App key generated"

# ── 3. Dependencies ───────────────────────────────────────────────────────────
hr "3/6  Installing dependencies"

info "Installing PHP dependencies (Composer)..."
composer install --no-interaction --prefer-dist --optimize-autoloader
ok "PHP dependencies installed"

info "Installing Node dependencies and building assets..."
npm ci --silent
npm run build --silent
ok "Frontend assets built"

# ── 4. Database ───────────────────────────────────────────────────────────────
hr "4/6  Database setup"

info "Running migrations..."
php artisan migrate --force
ok "Migrations complete"

info "Seeding default data (feeds + agencies)..."
php artisan db:seed --force
ok "Default data seeded"

if [ "$SEED_DEMO" = true ]; then
    info "Seeding demo data (institution, schedules, news)..."
    php artisan db:seed --class=DemoSeeder --force
    ok "Demo data seeded"
    echo ""
    echo -e "     ${BOLD}Demo credentials${NC}"
    echo "     Email:    admin@iroyin.demo"
    echo "     Password: demo@2026"
fi

# ── 5. Storage ────────────────────────────────────────────────────────────────
hr "5/6  Storage"

php artisan storage:link --force 2>/dev/null && ok "Storage symlink created" || ok "Storage symlink already exists"

# ── 6. Tests ──────────────────────────────────────────────────────────────────
if [ "$RUN_TESTS" = true ]; then
    hr "6/6  Running test suite"
    php artisan test
else
    hr "6/6  Tests skipped (--no-tests)"
fi

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}  Setup complete!${NC}"
echo ""
echo "  Start the development server:"
echo "    php artisan serve"
echo ""
echo "  Then open:  http://localhost:8000"
echo "  Kiosk:      http://localhost:8000  (public, no login)"
echo "  Admin:      http://localhost:8000/admin"
echo ""
