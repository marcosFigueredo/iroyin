# Changelog

All notable changes to IROYIN will be documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
Versions follow [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2026-06-27

### Added

- **Kiosk display** (`public/index.html`) — full-screen information panel with automatic rotation cycle
- **Class schedule panel** — shows today's timetable from the active semester; real-time color highlighting of ongoing classes
- **News panel** — displays articles imported from RSS feeds with image, source badge, QR code, and progress bar
- **Open calls panel (Editais)** — typographic panel for research funding calls; shows agency badge, title, objective, deadline, days remaining, and QR code
- **Weather widget** — current weather via OpenWeatherMap; server-side proxy (`/api/clima`) keeps API key out of the browser
- **Kiosk rotation** — configurable timed cycle: schedule → news (first half) → open calls → schedule → news (second half) → repeat
- **Night mode** — outside class hours shows news and open calls continuously instead of an empty schedule table
- **Offline overlay** — graceful message when internet connection is lost

- **Admin panel** (`/admin`) — Bootstrap 5 web interface for content management
- **First-run setup wizard** (`/setup`) — creates initial administrator account; redirects to institution configuration
- **Role-based access** — Admin (full access) and Editor (content only) roles
- **Institution settings** — name, acronym, department, city, logo, banner text
- **Display configuration** — weather city, OpenWeatherMap API key, schedule duration, news item duration, accent color
- **News management** — import from RSS feeds, manual entry, image upload, date range control
- **Feed management** — add/remove/toggle RSS sources; accessible to admins from the news page
- **Semester and schedule management** — multi-semester support with one active semester; bulk import
- **Open calls management (Editais)** — CRUD for funding calls linked to agencies; toggle active/inactive; deadline tracking
- **Agency management** — CRUD for funding agencies with brand color, RSS URL, and editorial page URL
- **User management** — create/edit/activate/deactivate users; no public registration

- **API layer** — public JSON endpoints consumed by the kiosk: `/api/config`, `/api/horarios`, `/api/noticias`, `/api/editais`, `/api/clima`
- **Default seeder** — pre-loads 12 RSS feeds and 4 Brazilian funding agencies (CAPES, CNPq, FINEP, FAPESB) with sample open calls
- **23 automated tests** covering authentication, setup wizard, middleware, and profile management
- **MIT License**
- **Zenodo DOI**: [10.5281/zenodo.20970621](https://doi.org/10.5281/zenodo.20970621)

### Security

- Weather API key stored server-side only; never sent to the browser
- Public registration route removed; user creation is admin-only
- Inactive users cannot log in
- SSL verification enabled on all outbound HTTP requests

---

## [Unreleased]

No changes yet.
