# SiKaMo Project

Proyek Laravel untuk sistem SiKaMo. Proyek ini menggunakan Laravel 12+, PHP 8.3+, dan Tailwind CSS.

## Prasyarat (Requirements)

Sebelum memulai, pastikan sistem Anda memiliki aplikasi berikut:
- **PHP** >= 8.3
- **Composer** (untuk manajemen package PHP)
- **Node.js** dan **NPM** (untuk build frontend assets)
- **Git** (opsional)

## Cara Instalasi

Ikuti langkah-langkah di bawah ini untuk mengatur proyek di lingkungan lokal Anda:

1. **Clone repositori (jika menggunakan Git):**
   ```bash
   git clone https://github.com/wandi1209/SiKaMo_PBL_2026.git
   cd SiKaMo
   ```

2. **Install dependensi PHP via Composer:**
   ```bash
   composer install
   ```

3. **Install dependensi Node.js / Frontend:**
   ```bash
   npm install
   ```

4. **Konfigurasi Environment:**
   Salin file konfigurasi bawaan Laravel:
   ```bash
   cp .env.example .env
   ```
   *Pastikan Anda telah membuat database MySQL (misalnya `db_sikamo`) dan sesuaikan kredensial username/password database Anda di dalam file `.env`.*

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database dan Seeding:**
   Jalankan perintah ini untuk membuat struktur tabel database dan mengisi data dummy awal (akun login admin):
   ```bash
   php artisan migrate:fresh --seed
   ```

## Cara Menjalankan Proyek

Untuk menjalankan server backend Laravel dan Vite server (Tailwind/Frontend assets) secara bersamaan, Anda bisa menggunakan perintah bawaan Laravel:

```bash
composer run dev
```

*Atau, jika ingin menjalankannya secara terpisah pada 2 terminal:*
- Terminal 1: `php artisan serve`
- Terminal 2: `npm run dev`

Aplikasi sekarang dapat diakses melalui browser pada `http://127.0.0.1:8000` atau `http://localhost:8000`.

## Akses Akun Login

Berdasarkan data *seeder* yang telah dijalankan, Anda bisa masuk ke dalam sistem dengan menggunakan kredensial berikut:

- **Email:** `admin@sikamo.com`
- **Password:** `admin123`

Saat Anda pertama kali membuka website (`/`), sistem akan otomatis mengarahkan ke halaman `/login`. Apabila Anda telah berhasil login, akses website akan langsung diarahkan ke halaman `/dashboard`.
