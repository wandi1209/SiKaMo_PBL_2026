# Dokumentasi QRIS Dinamis

Dokumen ini menjelaskan cara kerja fitur QRIS dinamis di SiKaMo, mulai dari konfigurasi toko sampai QR yang tampil di halaman pembayaran kasir.

## Tujuan

Fitur QRIS dinamis membuat QR pembayaran berdasarkan total transaksi.

Contoh:

- Total belanja: `Rp80.000`
- Kasir memilih metode `QRIS`
- Sistem membuat QRIS dengan nominal `80000.00`
- Saat pelanggan scan, aplikasi pembayaran menampilkan nominal `Rp80.000`

## Alur Penggunaan

1. Owner masuk ke panel merchant.
2. Buka menu `Profil Toko`.
3. Upload gambar QRIS statis toko pada field `QR Code Toko (gambar)`.
4. Sistem membaca payload QRIS dari gambar tersebut secara otomatis.
5. Field `Payload QRIS` akan terisi otomatis jika QR berhasil dibaca.
6. Owner menyimpan profil toko.
7. Kasir membuat transaksi seperti biasa.
8. Pada halaman pembayaran, kasir memilih metode `QRIS`.
9. Sistem menampilkan QRIS dinamis sesuai total transaksi.
10. Setelah pelanggan membayar, kasir menekan `Selesaikan Transaksi`.

## Cara Kerja Teknis

### 1. Decode Payload Dari Gambar

Saat owner mengunggah gambar QRIS, browser menjalankan decoder QR menggunakan package frontend `html5-qrcode`.

File terkait:

- `resources/js/barcode-scanner.js`
- `resources/views/filament/merchant/pages/partials/qris-payload-auto-reader.blade.php`
- `app/Filament/Merchant/Pages/EditMerchantProfile.php`

Jika gambar berhasil dibaca dan hasilnya terlihat seperti payload QRIS Indonesia, sistem mengisi field `qris_payload`.

Validasi dasar yang digunakan:

- Payload diawali `000201`
- Payload mengandung `5802ID`

### 2. Simpan Payload Toko

Payload hasil decode disimpan di tabel `m_merchants`, kolom `qris_payload`.

Migration:

- `database/migrations/2026_05_30_212500_add_qris_payload_to_m_merchants_table.php`

Model:

- `app/Models/Merchant.php`

### 3. Generate QRIS Sesuai Nominal

Saat halaman pembayaran QRIS dibuka, Livewire mengambil:

- Merchant aktif
- Payload QRIS toko
- Total transaksi

Kemudian service QRIS menyisipkan nominal ke tag QRIS `54`, mengubah tipe transaksi menjadi dinamis di tag `01`, dan menghitung ulang CRC di tag `63`.

File terkait:

- `app/Services/QrisPayloadService.php`
- `app/Livewire/Merchant/SalesPage.php`

Contoh perubahan payload:

- Tag `01`: diubah menjadi `12` untuk dynamic QR
- Tag `54`: diisi nominal, misalnya `80000.00`
- Tag `63`: CRC dihitung ulang agar payload valid

### 4. Render QR Di Halaman Pembayaran

Payload hasil generate dikirim ke endpoint QR renderer:

```text
https://api.qrserver.com/v1/create-qr-code/?size=320x320&data={payload}
```

Gambar QR tersebut ditampilkan di halaman pembayaran kasir.

View terkait:

- `resources/views/livewire/merchant/sales-page.blade.php`

## Kondisi Tombol QRIS

Tombol metode pembayaran QRIS hanya aktif jika:

- Toko memiliki `qris_payload`
- Total transaksi lebih dari `0`
- Payload bisa diproses menjadi QRIS dinamis

Jika payload belum ada, halaman menampilkan pesan:

```text
Payload QRIS belum dikonfigurasi
```

## Cara Menguji Manual

1. Jalankan migration:

```bash
php artisan migrate
```

2. Build asset frontend:

```bash
npm run build
```

3. Login sebagai owner.
4. Buka `Profil Toko`.
5. Upload gambar QRIS yang jelas.
6. Pastikan field `Payload QRIS` otomatis terisi.
7. Simpan profil toko.
8. Masuk halaman kasir.
9. Tambahkan produk sampai total transaksi, misalnya `Rp80.000`.
10. Pilih `QRIS`.
11. Scan QR yang tampil dengan aplikasi pembayaran.
12. Pastikan nominal yang muncul sesuai total transaksi.

## Batasan

Fitur ini hanya membuat payload QRIS dinamis dari QRIS statis toko. Sistem belum memverifikasi pembayaran secara otomatis.

Artinya:

- Kasir masih perlu menekan `Selesaikan Transaksi` secara manual.
- Sistem belum menerima callback atau webhook pembayaran.
- Sistem belum tahu apakah pelanggan benar-benar sudah membayar.
- Rekonsiliasi pembayaran tetap perlu dicek dari aplikasi merchant atau mutasi pembayaran.

Untuk validasi pembayaran otomatis, perlu integrasi payment gateway atau acquirer resmi seperti Midtrans, Xendit, Duitku, Tripay, atau penyedia QRIS lain yang mendukung API dan webhook.

## Troubleshooting

### Payload QRIS Tidak Terisi Otomatis

Kemungkinan penyebab:

- Gambar blur atau QR terlalu kecil.
- File bukan gambar QRIS.
- Gambar berisi QR pembayaran biasa, bukan payload QRIS merchant.
- Asset frontend belum dibuild setelah perubahan.

Solusi:

```bash
npm run build
php artisan view:clear
```

Lalu upload ulang gambar QRIS di `Profil Toko`.

### QRIS Tidak Aktif Di Halaman Kasir

Kemungkinan penyebab:

- `qris_payload` belum tersimpan.
- Total keranjang masih `0`.
- Payload gagal diproses.

Cek ulang `Profil Toko`, upload ulang gambar QRIS, lalu simpan.

### Nominal Tidak Muncul Saat Scan

Kemungkinan penyebab:

- Aplikasi pembayaran tidak membaca tag nominal dari payload.
- Payload QRIS sumber tidak kompatibel.
- QRIS merchant dari penyedia tertentu tidak mendukung modifikasi lokal.

Jika ini terjadi, gunakan integrasi payment gateway resmi untuk QRIS dinamis.

## File Yang Terlibat

- `app/Services/QrisPayloadService.php`
- `app/Livewire/Merchant/SalesPage.php`
- `app/Models/Merchant.php`
- `app/Filament/Merchant/Pages/EditMerchantProfile.php`
- `app/Providers/Filament/MerchantPanelProvider.php`
- `resources/js/barcode-scanner.js`
- `resources/views/filament/merchant/pages/partials/qris-payload-auto-reader.blade.php`
- `resources/views/livewire/merchant/sales-page.blade.php`
- `database/migrations/2026_05_30_212500_add_qris_payload_to_m_merchants_table.php`
- `tests/Unit/QrisPayloadServiceTest.php`
