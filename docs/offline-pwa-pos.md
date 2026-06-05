# Mode Offline PWA SiKaMo POS

Dokumen ini menjelaskan cara kerja mode offline untuk halaman kasir SiKaMo.

## Tujuan

Mode offline dibuat agar kasir tetap bisa melayani transaksi saat koneksi internet putus. Mode ini bukan pengganti dashboard online penuh. Fokusnya adalah fitur wajib untuk tetap berjualan:

- Katalog produk dari data terakhir
- Kategori produk
- Keranjang dan perhitungan total
- Diskon voucher yang sudah tersimpan lokal
- Pencarian member dari data terakhir
- Pembayaran tunai
- QRIS statis jika toko memiliki gambar QRIS tersimpan
- Struk transaksi
- Riwayat transaksi lokal pending
- Sinkronisasi transaksi saat koneksi kembali

## Alur Penggunaan

1. Login ke SiKaMo saat online.
2. Buka halaman kasir.
3. Tekan `Mode Offline` atau biarkan halaman mengambil snapshot otomatis.
4. Jika browser menampilkan prompt install, tekan `Install App`.
5. Saat koneksi terputus, halaman otomatis masuk ke `Kasir Offline`.
6. Kasir memilih produk, mengisi pembayaran, lalu menekan `Simpan Transaksi Offline`.
7. Transaksi disimpan di IndexedDB perangkat dengan status pending.
8. Saat online kembali, sistem mencoba sync otomatis.
9. Jika sync berhasil, transaksi masuk ke database server dan stok dikurangi.
10. Jika sync gagal, transaksi tetap ada di riwayat lokal dengan pesan error.

## Data Yang Disimpan Lokal

Data lokal disimpan di IndexedDB browser dengan database `sikamo-offline-pos`.

Snapshot offline berisi:

- Data user kasir
- Data merchant
- Produk, harga, stok, barcode, kategori, dan gambar
- Kategori
- Member aktif
- Diskon aktif
- Metode pembayaran offline

Transaksi pending berisi:

- `client_id` unik
- Nomor invoice lokal sementara dengan prefix `OFF-`
- Waktu transaksi offline
- Item transaksi
- Diskon/member yang dipakai
- Total, nominal bayar, kembalian
- Status sync

## Sinkronisasi

Endpoint yang dipakai:

- `GET /merchant/offline-pos/snapshot`
- `POST /merchant/offline-pos/sync`

Sinkronisasi bersifat idempotent memakai kolom `offline_client_id` di tabel `t_transactions`. Jika request sync terkirim dua kali, server mengembalikan transaksi yang sama dan tidak membuat duplikat.

Saat sync, server tetap melakukan validasi:

- User harus masih login
- Merchant harus valid
- Produk harus milik merchant tersebut
- Stok server harus cukup
- Diskon harus masih valid
- Member harus masih aktif
- Pembayaran tunai harus cukup

Jika validasi gagal, transaksi tetap pending di perangkat dan kasir bisa melihat pesan error.

## Batasan

- Mode offline hanya untuk halaman kasir, bukan seluruh dashboard.
- Dashboard analitik, laporan penuh, user management, pengaturan merchant, audit log, dan export tetap butuh internet.
- QRIS dinamis tidak bisa dibuat/validasi saat offline karena butuh server dan layanan eksternal.
- QRIS offline hanya aman sebagai QRIS statis jika merchant sudah menyimpan gambar QRIS.
- Stok lokal adalah stok terakhir dikurangi transaksi pending di perangkat itu. Konflik stok antar perangkat tetap diselesaikan saat sync.
- Jika session Laravel sudah expired saat internet kembali, user harus login ulang sebelum sync.
- Refund offline tidak didukung.

## Cara Uji Manual

1. Jalankan aplikasi dan login sebagai owner/kasir.
2. Buka halaman kasir.
3. Pastikan tombol `Mode Offline` muncul.
4. Tekan `Mode Offline`, lalu tekan `Perbarui Data`.
5. Matikan koneksi dari DevTools atau sistem operasi.
6. Tambahkan produk ke keranjang offline.
7. Selesaikan transaksi tunai.
8. Pastikan transaksi muncul di `Riwayat Lokal`.
9. Nyalakan koneksi.
10. Tekan `Sync Pending` jika sync otomatis belum berjalan.
11. Cek laporan transaksi dan stok produk di server.

## File Terkait

- `public/sw.js`
- `resources/js/app.js`
- `resources/views/livewire/merchant/sales-page.blade.php`
- `app/Http/Controllers/OfflinePosController.php`
- `database/migrations/2026_06_04_000001_add_offline_sync_fields_to_transactions_table.php`
- `routes/web.php`
