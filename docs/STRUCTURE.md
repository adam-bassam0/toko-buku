# Struktur Project BukuKu

Dokumen ini menjelaskan pembagian folder setelah restrukturisasi.

## Pohon Folder

```text
toko-buku/
├── admin/                  # Halaman panel admin (entry point web)
│   ├── index.php           # Data buku
│   ├── tambah.php          # Form tambah buku
│   ├── edit.php            # Form edit buku
│   ├── kategori.php        # Kelola kategori
│   └── pesanan.php         # Monitoring pesanan & pembayaran
│
├── app/                    # Kode inti aplikasi (bukan entry point)
│   ├── bootstrap.php       # Titik masuk tunggal: env, auth, database
│   ├── config/
│   │   ├── app.php         # Konfigurasi umum (APP_ENV, APP_NAME)
│   │   ├── database.php    # Koneksi MySQL + auto-migration
│   │   └── env.php         # Pembaca file .env
│   ├── helpers/
│   │   └── auth.php        # Session, CSRF, flash, guard login/admin
│   └── views/
│       └── partials/       # Navbar & footer bersama
│
├── assets/                 # Aset publik
│   ├── css/style.css
│   └── js/script.js
│
├── config/                 # Wrapper kompatibilitas untuk kode lama
│   ├── auth.php            # -> app/helpers/auth.php
│   └── koneksi.php         # -> app/config/database.php
│
├── database/               # Skema & migrasi SQL
│   ├── schema.sql
│   ├── migrasi_auth.sql
│   ├── migrasi_kategori.sql
│   └── migrasi_pembayaran.sql
│
├── docs/                   # Dokumentasi tambahan
│   └── CONTRIBUTING.md
│
├── proses/                 # Handler POST (aksi)
│   ├── proses_cart.php
│   ├── proses_checkout.php
│   ├── proses_bayar.php
│   ├── proses_login.php
│   ├── proses_register.php
│   ├── proses_logout.php
│   ├── proses_tambah.php
│   ├── proses_edit.php
│   ├── proses_hapus.php
│   └── proses_kategori.php
│
├── storage/                # File yang tidak boleh diakses langsung
│   └── uploads/bukti/      # Bukti transfer (dilayani lewat bukti.php)
│
├── bukti.php               # Penyaji bukti pembayaran yang aman
├── index.php               # Katalog toko
├── detail.php              # Detail buku
├── cart.php                # Keranjang belanja
├── checkout.php            # Checkout & pilih metode bayar
├── pembayaran.php          # Instruksi QRIS / transfer
├── pesanan.php             # Riwayat pesanan pelanggan
├── login.php
├── register.php
├── setup.php               # Asisten setup database
├── keranjang.php           # Alias lama -> cart.php
├── .env.example            # Contoh konfigurasi environment
├── .editorconfig
└── README.md
```

## Aturan Penempatan

| Jenis kode | Lokasi |
|---|---|
| Halaman yang dibuka browser | root, `admin/` |
| Handler form (POST) | `proses/` |
| Logika inti & konfigurasi | `app/` |
| Partial tampilan | `app/views/partials/` |
| Aset publik (CSS/JS) | `assets/` |
| File upload pengguna | `storage/uploads/` |
| SQL & migrasi | `database/` |
| Dokumentasi | `docs/` |

## Alur Bootstrap

Semua halaman memuat satu titik masuk:

```php
require_once __DIR__ . '/app/bootstrap.php';   // dari root
require_once __DIR__ . '/../app/bootstrap.php'; // dari admin/ atau proses/
```

`app/bootstrap.php` memuat:

1. `app/config/env.php` + file `.env`
2. `app/config/app.php` (mode dev/prod)
3. `app/helpers/auth.php` (session, CSRF, guard)
4. `app/config/database.php` (koneksi + auto-migration)

## Catatan Kompatibilitas

File `config/auth.php` dan `config/koneksi.php` tetap tersedia sebagai
wrapper tipis agar kode lama tidak langsung rusak. Kode baru sebaiknya
langsung memakai `app/bootstrap.php`.

## Upload Bukti Pembayaran

1. File disimpan di `storage/uploads/bukti/` dengan nama acak.
2. Folder `storage/` diblokir dari akses langsung lewat `.htaccess`.
3. File hanya bisa dilihat melalui `bukti.php?pesanan=ID`.
4. `bukti.php` memeriksa login, kepemilikan pesanan (atau role admin),
   tipe MIME, dan mencegah path traversal.
