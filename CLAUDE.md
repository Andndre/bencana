# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**BENCANA ALAM** — Laravel 13 mobile-first web application for natural disaster early detection. Serves static/asset-heavy pages optimized for mobile screens (max-width ~440px container, black sidebar on tablet/desktop).

## Commands

```bash
# Full setup (composer install + env + key + migrate + npm install + build)
composer run setup

# Development (server + vite + queue + logs concurrently)
composer run dev

# Run tests
composer run test

# Lint
./vendor/bin/pint

# Vite production build
npm run build

# DDEV (Docker) environment
ddev start
ddev describe
```

## Architecture

- **Laravel 13** with Vite bundler, Tailwind CSS v4
- **Frontend**: `resources/views/` Blade templates + `resources/css/app.css`. Static images in `public/images/`
- **Routes** (`routes/web.php`): Single `HomeController` handles all views — no auth scaffolding
  - `GET /` → `home.blade.php` (splash + menu)
  - `GET /simulasi-bencana` → `simulasi-bencana.blade.php` (AR marker download + Buka AR)
  - `GET /penanggulangan-bencana` → `penanggulangan-bencana.blade.php` (Banjir, Gempa, Tsunami, Angin Puting Beliung, Longsor)
  - `GET /peta-bencana` → `peta-bencana.blade.php` (Leaflet.js map)
- **Database**: MariaDB via DDEV (`DB_CONNECTION=mariadb`, `DB_HOST=db`)
- **External CDN**: jQuery 3.7.1, Leaflet.js 1.9.4, Bunny Fonts (Instrument Sans 400–900)
- **Supporting docs**: `dokumen_pendukung/Deskripsi bencana.md` — disaster descriptions and mitigation guides

## Design Conventions

- Mobile-first container: `max-w-110` (~440px), centered with black background on larger screens
- Body height: `h-dvh` (dynamic viewport height) for mobile browser chrome handling
- Splash screen: orange base (`#c25c06`) + semi-transparent `bencana splash.webp` overlay + centered logo
- Menu screen: `bencana.webp` background + `button.webp` images as menu item bases with overlaid text
- Info button: `info.webp` image (circle with "i"), positioned bottom-left
- Branding colors: orange `#c25c06` (primary), amber `#ffac00` (headers/buttons), dark-red `#800000` (text)
- Heavy use of Tailwind arbitrary values for precise matching: `text-[#800000]`, `style="background-color: #c25c06"`
