# Dokumentasi Realtime Kasir Dengan Laravel Reverb

Dokumen ini menjelaskan cara kerja fitur realtime di SiKaMo untuk skenario beberapa kasir bekerja bersamaan.

## Tujuan

Fitur realtime dipakai agar perubahan stok dari satu kasir langsung diketahui oleh kasir lain tanpa refresh manual.

Contoh:

1. Kasir A menjual produk `Teh Botol`.
2. Stok `Teh Botol` berkurang di database.
3. Sistem mengirim event realtime ke channel toko.
4. Kasir B yang sedang membuka halaman penjualan menerima event tersebut.
5. Halaman kasir B refresh data produk/stok dan menampilkan notifikasi.

## Teknologi Yang Dipakai

- Laravel Reverb sebagai WebSocket server.
- Laravel Broadcasting untuk event server-side.
- Laravel Echo untuk client browser.
- Pusher protocol melalui `pusher-js`.
- Private channel per merchant.

Dependency yang dipakai:

- `laravel/reverb`
- `laravel-echo`
- `pusher-js`

## File Yang Terlibat

- `config/broadcasting.php`
- `config/reverb.php`
- `routes/channels.php`
- `resources/js/echo.js`
- `resources/js/bootstrap.js`
- `app/Events/ProductStockUpdated.php`
- `app/Livewire/Merchant/SalesPage.php`
- `app/Http/Controllers/TransactionController.php`
- `app/Services/TransactionRefundService.php`
- `app/Filament/Merchant/Resources/PurchaseResource.php`
- `resources/views/livewire/merchant/sales-page.blade.php`

## Channel Realtime

Realtime memakai private channel:

```text
merchant.{merchantId}
```

Contoh:

```text
merchant.1
```

Authorization channel ada di:

```php
routes/channels.php
```

Aturannya:

- Owner hanya boleh subscribe ke merchant miliknya.
- Cashier hanya boleh subscribe ke merchant tempat dia bertugas.
- Role lain ditolak.

## Event Realtime

Event utama:

```php
App\Events\ProductStockUpdated
```

Event ini dikirim saat stok produk berubah karena:

- checkout penjualan,
- refund transaksi,
- pembelian/restock supplier.

Payload event:

```php
[
    'productId' => 17,
    'productName' => 'Teh Botol',
    'stock' => 8,
    'lowStockThreshold' => 5,
    'isLowStock' => false,
]
```

Nama broadcast event:

```text
ProductStockUpdated
```

Di frontend didengarkan sebagai:

```js
window.Echo.private('merchant.1')
    .listen('.ProductStockUpdated', (event) => {
        // refresh data produk/stok
    });
```

## Alur Checkout Dua Kasir

1. Kasir A dan Kasir B membuka halaman penjualan toko yang sama.
2. Keduanya melihat stok produk dari database.
3. Kasir A checkout.
4. Backend menjalankan database transaction.
5. Produk dikunci dengan `lockForUpdate()` saat stok dikurangi.
6. Stok divalidasi ulang di backend.
7. Setelah transaksi commit, event `ProductStockUpdated` dikirim.
8. Kasir B menerima event melalui Reverb.
9. UI kasir B melakukan refresh Livewire.
10. Stok terbaru tampil di halaman kasir B.

## Kenapa Tetap Perlu Locking Database

Realtime UI tidak cukup untuk mencegah stok minus.

Misalnya stok tinggal `1`:

- Kasir A dan Kasir B sama-sama melihat stok `1`.
- Keduanya klik checkout hampir bersamaan.

Tanpa locking, dua transaksi bisa sama-sama lolos.

Dengan `lockForUpdate()`:

- transaksi pertama mengunci row produk,
- transaksi kedua menunggu,
- setelah transaksi pertama selesai, transaksi kedua membaca stok terbaru,
- jika stok tidak cukup, checkout kedua ditolak.

Jadi realtime membuat UI cepat update, sedangkan locking membuat data tetap aman.

## Konfigurasi Environment

Contoh konfigurasi lokal:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=997618
REVERB_APP_KEY=local_reverb_key
REVERB_APP_SECRET=local_reverb_secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

File `.env.example` sudah berisi contoh konfigurasi tersebut.

## Cara Menjalankan Di Lokal

Jalankan Laravel, queue, Vite, dan Reverb.

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

Terminal 3:

```bash
npm run dev
```

Terminal 4:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug
```

Jika memakai production build:

```bash
npm run build
```

## Cara Test Manual

1. Jalankan server Laravel, Vite, queue, dan Reverb.
2. Login sebagai dua kasir berbeda di browser berbeda, atau browser normal dan incognito.
3. Pastikan keduanya berada di merchant/toko yang sama.
4. Buka halaman penjualan di kedua browser.
5. Di browser A, lakukan transaksi produk tertentu.
6. Lihat browser B.
7. Browser B seharusnya menerima notifikasi stok dan data produk refresh.

Untuk test restock:

1. Owner buka menu `Pembelian`.
2. Buat pembelian/restock produk.
3. Halaman kasir yang sedang terbuka akan menerima update stok.

Untuk test refund:

1. Refund transaksi yang berisi produk tertentu.
2. Stok produk bertambah.
3. Halaman kasir lain menerima update stok.

## Troubleshooting

### Event Tidak Masuk Ke Browser

Cek Reverb server berjalan:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug
```

Cek browser console:

- error WebSocket,
- error auth `/broadcasting/auth`,
- key/host/port Reverb salah.

### Browser Gagal Subscribe Private Channel

Cek user login dan channel authorization di:

```php
routes/channels.php
```

Pastikan:

- owner punya merchant,
- cashier punya `merchant_id`,
- user berada di toko yang sama dengan channel.

### Stok Tidak Refresh

Cek event dikirim dari backend:

- checkout penjualan,
- refund,
- pembelian supplier.

Cek juga apakah halaman penjualan memiliki `merchantRealtimeChannel`.

### Reverb Connect Tapi Tidak Ada Event

Cek `BROADCAST_CONNECTION`:

```env
BROADCAST_CONNECTION=reverb
```

Lalu bersihkan cache:

```bash
php artisan optimize:clear
```

### Asset Echo Belum Kebaca

Jalankan ulang:

```bash
npm install
npm run build
```

Atau untuk development:

```bash
npm run dev
```

## Catatan Deployment

Di production, Reverb harus dijalankan sebagai proses service terpisah, misalnya via Supervisor/systemd.

Minimal proses yang perlu jalan:

- PHP-FPM/web server untuk Laravel,
- queue worker,
- Reverb server,
- scheduler jika dipakai.

Contoh command Reverb:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Jika memakai HTTPS di domain utama, WebSocket biasanya perlu reverse proxy dari Nginx/Caddy ke port Reverb.

## Batasan Saat Ini

Realtime yang dibuat fokus pada update stok produk.

Belum mencakup:

- presence online kasir,
- realtime dashboard owner,
- realtime daftar transaksi,
- conflict indicator saat produk sedang di-checkout kasir lain,
- sinkronisasi isi keranjang antar device.

Fitur tersebut bisa ditambahkan di atas fondasi channel `merchant.{merchantId}` yang sudah ada.
