# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**BENCANA ALAM** — Laravel 13 mobile-first web application for natural disaster early detection. Serves static/asset-heavy pages optimized for mobile screens (max-width ~440px container, black sidebar on tablet/desktop).

## Commands

```bash
# Setup
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force

# Development (runs server + vite + queue + logs concurrently)
composer run dev

# Run tests
composer run test

# Lint (Pint)
./vendor/bin/pint

# Vite build (for production)
npm run build
```

## Architecture

- **Laravel 13** with Vite bundler, Tailwind CSS
- **Frontend**: `resources/views/` Blade templates + `resources/css/app.css`. Static images in `public/images/`
- **Routes**: `routes/web.php` — no auth scaffolding; `/` serves `home.blade.php`
- **Menu pages**: Currently all menu links (`href="#"`) — future pages to be created as blade views
- **Font**: Instrument Sans (Bunny Fonts) loaded via CDN with weights 400–900

## Design Conventions

- Mobile-first container: `max-w-110` (~440px), centered with black background on larger screens
- Splash screen: orange base (`#c25c06`) + semi-transparent `bencana splash.webp` overlay + centered logo
- Menu screen: `bencana.webp` background + `button.webp` images as menu item bases with overlaid text
- Info button: `info.webp` image (circle with "i"), positioned bottom-left
- Tailwind arbitrary values used for precise pixel/color matching: `text-[#800000]`, `style="background-color: #c25c06"`
