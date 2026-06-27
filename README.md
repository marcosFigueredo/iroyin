# IROYIN — Institutional Information Display System

[![DOI](https://zenodo.org/badge/DOI/10.5281/zenodo.20970621.svg)](https://doi.org/10.5281/zenodo.20970621)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

**IROYIN** is an open-source web application for institutions (universities, departments, research centers) to display real-time information on public screens — class schedules, news from RSS feeds, open research calls, and weather — managed through a browser-based admin panel.

> *Iroyin* means "news" in Yoruba, reflecting the system's core purpose: keeping communities informed.

---

## Features

- **Class schedule display** — shows the current day's timetable with real-time highlighting of ongoing classes
- **News panel** — imports articles from RSS feeds with image support and QR code for each item
- **Open calls (Editais)** — displays active research grants and funding calls from agencies (CAPES, CNPq, FINEP, FAPESB, and others) with countdown to deadline and QR code
- **Weather widget** — current conditions via OpenWeatherMap (server-side proxy, API key never exposed to the browser)
- **Role-based admin** — Admin and Editor roles; setup wizard on first run (no hardcoded credentials)
- **Configurable display** — institution name, logo, colors, schedule duration, and news item duration
- **Offline detection** — graceful overlay when internet connection is lost
- **Kiosk rotation** — automatic cycle: schedule → news → open calls → repeat

---

## Requirements

| Dependency | Version |
| --- | --- |
| PHP | >= 8.2 |
| Laravel | 12.x |
| MySQL / MariaDB | >= 5.7 / >= 10.4 |
| Composer | >= 2.x |
| Node.js + npm | >= 18 (for asset build only) |

> SQLite is **not** supported. MySQL/MariaDB is required.

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/iroyin.git
cd iroyin

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader

# 3. Install and build frontend assets
npm install && npm run build

# 4. Configure environment
cp .env.example .env
php artisan key:generate

# 5. Edit .env — set DB_DATABASE, DB_USERNAME, DB_PASSWORD and APP_URL
nano .env

# 6. Run migrations and seed default data
php artisan migrate
php artisan db:seed

# 7. Create the storage symlink
php artisan storage:link
```

After installation, open `APP_URL` in a browser. You will be redirected to the **setup wizard** (`/setup`) to create the first administrator account.

---

## Configuration

All settings are managed through the admin panel (`/admin`):

| Section | Description |
| --- | --- |
| **Instituição** | Institution name, acronym, department, logo, and banner text |
| **Configurações** | City for weather, OpenWeatherMap API key, display durations |
| **Fontes RSS** | Add/remove/toggle RSS feeds for the news panel |
| **Usuários** | Create and manage Admin and Editor accounts |
| **Editais** | Manage open research calls and funding agencies |

### OpenWeatherMap API key

Register for a free key at [openweathermap.org](https://openweathermap.org/api). Enter the key in **Configurações** → *Chave da API de clima*. The key is stored in the database and proxied server-side — it is never sent to the browser.

---

## Project Structure

```text
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Admin panel controllers
│   │   ├── Api/            # JSON API endpoints consumed by the kiosk
│   │   └── SetupController.php
│   └── Models/             # Eloquent models
├── database/
│   ├── migrations/         # All database migrations
│   └── seeders/            # Default feeds and agencies seeder
├── public/
│   ├── index.html          # Kiosk display (no auth required)
│   └── assets/
│       ├── css/style.css
│       └── js/             # Kiosk JS modules
└── resources/views/
    └── admin/              # Admin panel Blade views
```

The kiosk display (`public/index.html`) is a static HTML file served directly. It communicates with the Laravel backend exclusively through the JSON API (`/api/config`, `/api/horarios`, `/api/noticias`, `/api/editais`, `/api/clima`).

---

## Kiosk API Endpoints

| Endpoint | Description |
| --- | --- |
| `GET /api/config` | Institution settings and display configuration |
| `GET /api/horarios` | Today's class schedule |
| `GET /api/noticias` | Active news items |
| `GET /api/editais` | Active open calls (ordered by deadline) |
| `GET /api/clima` | Current weather (proxied from OpenWeatherMap) |

---

## Running Tests

```bash
php artisan test
```

All 23 tests should pass on a fresh install.

---

## License

MIT — see [LICENSE](LICENSE).

---

## Citation

If you use IROYIN in academic work, please cite:

> Figueredo, M. (2026). IROYIN: An open-source institutional information display system for universities and research departments. *SoftwareX*. [https://doi.org/10.5281/zenodo.20970621](https://doi.org/10.5281/zenodo.20970621)

Or use the Zenodo metadata directly: [https://doi.org/10.5281/zenodo.20970621](https://doi.org/10.5281/zenodo.20970621)

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
