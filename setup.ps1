# =============================================================================
# IROYIN — Setup Script (Windows / PowerShell)
# Installs dependencies, configures the environment, seeds demo data,
# and runs the test suite.
#
# Usage (PowerShell):
#   .\setup.ps1
#   .\setup.ps1 -NoDemo
#   .\setup.ps1 -NoTests
# =============================================================================

param(
    [switch]$NoDemo,
    [switch]$NoTests
)

$ErrorActionPreference = "Stop"

function Ok($msg)   { Write-Host "  [OK] $msg" -ForegroundColor Green }
function Info($msg) { Write-Host "   --> $msg" -ForegroundColor Yellow }
function Fail($msg) { Write-Host "  [ERR] $msg" -ForegroundColor Red; exit 1 }
function Hr($msg)   { Write-Host "`n$msg`n$(('-' * 44))" -ForegroundColor White }

# ── Banner ────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "  IROYIN -- Institutional Information Display System" -ForegroundColor Cyan
Write-Host "  Setup Script"
Write-Host ""

# ── 1. Prerequisites ──────────────────────────────────────────────────────────
Hr "1/6  Checking prerequisites"

if (-not (Get-Command php        -ErrorAction SilentlyContinue)) { Fail "PHP not found. Install PHP >= 8.2" }
if (-not (Get-Command composer   -ErrorAction SilentlyContinue)) { Fail "Composer not found. See https://getcomposer.org" }
if (-not (Get-Command node       -ErrorAction SilentlyContinue)) { Fail "Node.js not found. Install Node.js >= 18" }
if (-not (Get-Command npm        -ErrorAction SilentlyContinue)) { Fail "npm not found." }

Ok "PHP $(php -r 'echo PHP_MAJOR_VERSION.\".\".PHP_MINOR_VERSION;')"
Ok "Composer $(composer --version --no-ansi | Select-String -Pattern '\d+\.\d+\.\d+' | ForEach-Object { $_.Matches[0].Value })"
Ok "Node.js $(node -v)"
Ok "npm $(npm -v)"

# ── 2. Environment ────────────────────────────────────────────────────────────
Hr "2/6  Environment"

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Ok ".env created from .env.example"
} else {
    Ok ".env already exists -- skipping"
}

Info "Generating application key..."
php artisan key:generate --ansi
Ok "App key generated"

# ── 3. Dependencies ───────────────────────────────────────────────────────────
Hr "3/6  Installing dependencies"

Info "Installing PHP dependencies (Composer)..."
composer install --no-interaction --prefer-dist --optimize-autoloader
Ok "PHP dependencies installed"

Info "Installing Node dependencies and building assets..."
npm ci --silent
npm run build --silent
Ok "Frontend assets built"

# ── 4. Database ───────────────────────────────────────────────────────────────
Hr "4/6  Database setup"

Info "Running migrations..."
php artisan migrate --force
Ok "Migrations complete"

Info "Seeding default data (feeds + agencies)..."
php artisan db:seed --force
Ok "Default data seeded"

if (-not $NoDemo) {
    Info "Seeding demo data (institution, schedules, news)..."
    php artisan db:seed --class=DemoSeeder --force
    Ok "Demo data seeded"
    Write-Host ""
    Write-Host "     Demo credentials" -ForegroundColor Cyan
    Write-Host "     Email:    admin@iroyin.demo"
    Write-Host "     Password: demo@2026"
}

# ── 5. Storage ────────────────────────────────────────────────────────────────
Hr "5/6  Storage"

try {
    php artisan storage:link --force | Out-Null
    Ok "Storage symlink created"
} catch {
    Ok "Storage symlink already exists"
}

# ── 6. Tests ──────────────────────────────────────────────────────────────────
if (-not $NoTests) {
    Hr "6/6  Running test suite"
    php artisan test
} else {
    Hr "6/6  Tests skipped (-NoTests)"
}

# ── Done ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host "  Setup complete!" -ForegroundColor Green
Write-Host ""
Write-Host "  Start the development server:"
Write-Host "    php artisan serve"
Write-Host ""
Write-Host "  Then open:  http://localhost:8000"
Write-Host "  Kiosk:      http://localhost:8000  (public, no login)"
Write-Host "  Admin:      http://localhost:8000/admin"
Write-Host ""
