# BENCANA ALAM - Sistem Pemetaan & Penanggulangan Bencana

BENCANA ALAM adalah aplikasi web berbasis Laravel 13 yang dirancang dengan pendekatan **mobile-first** untuk memberikan informasi deteksi dini, pemetaan, dan langkah-langkah penanggulangan bencana alam. Aplikasi ini mengintegrasikan teknologi **Augmented Reality (AR)** untuk interaksi simulasi dan pemetaan bencana di wilayah Buleleng, Bali.

## Fitur Utama

- **Simulasi Bencana (AR):** Interaksi interaktif menggunakan AR Marker untuk memvisualisasikan simulasi bencana dengan model 3D.
- **Penanggulangan Bencana:** Panduan lengkap langkah-langkah Pra-Bencana, Saat Bencana, dan Pasca-Bencana.
- **Peta Bencana:** Visualisasi lokasi rawan bencana menggunakan Leaflet.js.
- **Admin Panel:** Manajemen data bencana, lokasi, langkah mitigasi, dan AR Marker.
- **Download AR Marker:** Unduh marker AR dalam format ZIP.

## Teknologi

- **Backend:** Laravel 13
- **Frontend:** Tailwind CSS v4, Vite
- **AR Library:** AR.js / Three.js
- **Maps:** Leaflet.js
- **Development Environment:** DDEV (Docker)

## Instalasi & Setup

### Menggunakan DDEV

1. ddev start
2. ddev composer run setup

### Setup Manual

1. composer install && npm install
2. php artisan key:generate
3. php artisan migrate
4. npm run build
5. php artisan serve

## Lisensi

Proyek ini menggunakan lisensi MIT.

## Penggunaan

- **Akses User:** Buka URL aplikasi.
- **Akses Admin:** Masuk ke /admin untuk mengelola data.
- **Fitur AR:** Gunakan menu 'Simulasi Bencana' dan izinkan akses kamera.

---
*Dikembangkan untuk memberikan edukasi dan kesiapsiagaan terhadap bencana alam.*
