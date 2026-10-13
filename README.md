# KosKu — Cari Kos Mahasiswa Perantau

Aplikasi web pencarian kos untuk mahasiswa perantau. Backend REST API (Laravel) +
Single Page Application (Vue 3), mencakup pencarian dengan filter lokasi/harga/fasilitas,
peta interaktif, tautan WhatsApp & Google Maps, ulasan & favorit, dashboard pemilik, serta
moderasi admin.

## Tumpukan Teknologi

- **Backend**: Laravel 12, Laravel Sanctum (SPA cookie auth), PostgreSQL
- **Frontend**: Vue 3, Vue Router, Pinia, Tailwind CSS v4, Leaflet, Vite
- **Testing**: PHPUnit (fitur API)

## Fitur

- Pencarian kos publik dengan filter tipe, rentang harga, fasilitas, ketersediaan, urutan
- Halaman detail kos dengan galeri foto, fasilitas, peta Leaflet, tautan WhatsApp & Google Maps
- Ulasan + rating (hanya ulasan terlihat yang dihitung), favorit
- Dashboard pemilik: CRUD kos, unggah/urutkan foto, update sisa kamar cepat
- Dashboard admin: verifikasi pemilik, moderasi kos, sembunyikan ulasan, kelola fasilitas & pengguna
- Aturan bisnis: kos tampil publik hanya jika `status=active` dan pemiliknya terverifikasi & aktif

## Menjalankan

### 1. Prasyarat
- PHP 8.3+ dengan ekstensi `pdo_pgsql`, `dom`, `zip`
- Composer, Node.js 20+, PostgreSQL

### 2. Instalasi
```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan kredensial database di `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kosku
DB_USERNAME=kosku
DB_PASSWORD=kosku

APP_LOCALE=id
FILESYSTEM_DISK=public
SESSION_DOMAIN=null
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:8000,127.0.0.1,127.0.0.1:8000
```

### 3. Migrasi, seed, storage link
```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Aset frontend
```bash
npm install
npm run build      # produksi
# atau: npm run dev  (pengembangan)
```

### 5. Jalankan server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```
Buka `http://localhost:8000`.

## Akun Demo

Kata sandi semua akun: `password`

| Peran              | Email                     |
| ------------------ | ------------------------- |
| Admin              | admin@kosku.test          |
| Pemilik terverifikasi | pemilik@kosku.test     |
| Pemilik belum verifikasi | pemilik.baru@kosku.test |
| Mahasiswa          | mahasiswa@test.com        |

## Pengujian

```bash
php artisan test
```
`phpunit.xml` menggunakan SQLite in-memory, jadi pastikan ekstensi `pdo_sqlite` tersedia.
Uji coba cepat API end-to-end: lihat skrip smoke test manual.

## Ringkasan API

Publik:
- `GET /api/kos` — daftar (filter: `q`, `type`, `price_min`, `price_max`, `facilities[]`, `available`, `sort`, `per_page`)
- `GET /api/kos/{slug}` — detail
- `GET /api/facilities`
- `POST /api/kos/{slug}/wa-click` — catat klik WhatsApp

Autentikasi (Sanctum SPA): `POST /api/register`, `POST /api/login`, `POST /api/logout`, `GET /api/user`
(Panggil `GET /sanctum/csrf-cookie` lebih dulu; frontend mengirim cookie + header `X-XSRF-TOKEN`.)

Mahasiswa: `POST /api/kos/{slug}/reviews`, `PUT/DELETE /api/reviews/{id}`,
`GET /api/favorites`, `POST/DELETE /api/kos/{slug}/favorite`

Pemilik: `GET /api/owner/dashboard`, resource `api/owner/kos`, `PUT /api/owner/kos/{kos}/stock`,
`POST /api/owner/kos/{kos}/photos`, dll.

Admin: `GET /api/admin/dashboard`, `POST /api/admin/owners/{user}/verify`,
`POST /api/admin/kos/{kos}/approve|reject|hide`, `POST /api/admin/reviews/{review}/hide|unhide`,
resource `api/admin/facilities`, `GET /api/admin/users`, `POST /api/admin/users/{user}/toggle-active`

## Struktur Penting

```
app/Http/Controllers/Api/   # Auth, Kos, Review, Favorite, Facility + Owner/* + Admin/*
app/Http/Requests/          # Validasi & otorisasi form
app/Http/Resources/         # Bentuk respons JSON
app/Models/                 # User, Kos, KosPhoto, Facility, Review
app/Policies/               # KosPolicy, ReviewPolicy, FacilityPolicy, UserPolicy
app/Support/helpers.php     # normalize_whatsapp, whatsapp_link, maps_link
database/seeders/           # Data demo + gambar contoh
resources/js/               # SPA Vue (router, stores, components, pages)
routes/api.php              # Semua rute API
```
