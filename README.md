# 📚 BukuKu — Digital Bookstore

BukuKu adalah aplikasi website katalog buku digital yang dirancang untuk memudahkan pengguna dalam mencari, menjelajahi, dan mendapatkan informasi mengenai berbagai koleksi buku.

Project ini dikembangkan menggunakan PHP Native dan MySQL. Antarmuka menggunakan Bootstrap 5 melalui CDN dengan fokus pada desain yang sederhana, responsif, dan mudah digunakan.

## ✨ Features

### User (Pelanggan)
- **Book Catalog** — Menampilkan koleksi buku dengan informasi lengkap.
- **Book Categories** — Filter dan jelajahi buku berdasarkan kategori (pills navigasi + dropdown navbar).
- **Book Search** — Mencari buku berdasarkan judul atau penulis, termasuk pencarian di dalam kategori aktif.
- **Book Details** — Melihat informasi detail buku, termasuk kategori, penulis, penerbit, harga, dan deskripsi.
- **Register & Login** — Daftar akun dan masuk sebagai pelanggan (password di-hash bcrypt).
- **Shopping Cart** — Menambah, mengubah jumlah, dan menghapus buku dari keranjang (dibatasi stok).
- **Checkout** — Membuat pesanan; stok berkurang otomatis dalam satu transaksi database.
- **Pembayaran QRIS & Transfer** — Memilih metode pembayaran, melihat instruksi/QR, lalu konfirmasi pembayaran (upload bukti opsional untuk transfer).
- **Order History** — Melihat riwayat pesanan + status pembayaran milik sendiri.
- **Responsive Design** — Tampilan yang menyesuaikan berbagai ukuran layar.

### Admin
- **Admin Dashboard** — Halaman pengelolaan buku, hanya untuk akun ber-role `admin`.
- **Category Management** — Tambah, edit, dan hapus kategori buku beserta jumlah buku terkait.
- **Add Books** — Menambahkan koleksi buku beserta pemilihan kategori.
- **Edit Books** — Memperbarui informasi buku dan kategori buku.
- **Delete Books** — Menghapus koleksi buku.
- **Monitoring Pesanan** — Melihat semua pesanan, metode pembayaran (QRIS/transfer), status lunas/menunggu, dan bukti transfer.
- **Role Protection** — Pelanggan tidak bisa membuka halaman admin.

### Keamanan
- **Anti SQL Injection** — Semua query yang melibatkan input pengguna memakai *prepared statement*.
- **Anti XSS** — Output dibungkus `htmlspecialchars()`.
- **Anti CSRF** — Token acak per session pada semua form yang mengubah data (login, registrasi, keranjang, CRUD admin, checkout, pembayaran, logout).
- **Upload Aman** — Bukti pembayaran divalidasi dari isi file (`finfo`), maksimal 2 MB, nama file diacak.
- **Password Hashing** — `password_hash()` bcrypt + `password_verify()`; tidak pernah menyimpan password asli.
- **Session Aman** — Cookie `HttpOnly` + `SameSite=Lax`, regenerasi session ID setelah login.

### ACID (Transaksi Database)
Checkout di `proses/proses_checkout.php` menerapkan ACID:
- **Atomicity** — Insert pesanan, insert rincian, dan pengurangan stok dibungkus `begin_transaction()` … `commit()`; jika satu gagal → `rollback()`.
- **Consistency** — Stok tidak mungkin minus (`UPDATE ... WHERE stok >= ?`), foreign key menjaga relasi.
- **Isolation** — Baris buku dikunci dengan `SELECT ... FOR UPDATE` agar dua pembeli tidak mengambil stok yang sama.
- **Durability** — Setelah `COMMIT`, data tersimpan permanen di InnoDB.

## 🛠️ Tech Stack

| Technology | Usage |
|---|---|
| PHP Native | Backend |
| MySQL | Database |
| XAMPP | Local development environment |
| Git & GitHub | Version control |

## 📁 Project Structure

```text
toko-buku/
├── app/                    # Kode inti aplikasi
│   ├── bootstrap.php       # Titik masuk: env, auth, database
│   ├── config/
│   │   ├── app.php         # Konfigurasi umum (APP_ENV)
│   │   ├── database.php    # Koneksi + auto-migration
│   │   └── env.php         # Pembaca file .env
│   ├── helpers/
│   │   └── auth.php        # Session, CSRF, guard login/admin
│   └── views/partials/     # Navbar & footer bersama
│
├── config/                 # Wrapper kompatibilitas kode lama
├── database/               # Skema & migrasi SQL
├── docs/                   # CONTRIBUTING.md + STRUCTURE.md
├── proses/                 # Handler POST (termasuk proses_cart.php)
├── admin/                  # Panel admin (dilindungi require_admin)
├── assets/                 # CSS & JS publik
├── storage/uploads/bukti/  # Bukti transfer (dilayani lewat bukti.php)
│
├── bukti.php               # Penyaji bukti pembayaran yang aman
├── index.php               # Katalog + filter kategori + pencarian
├── detail.php              # Detail buku
├── cart.php                # Keranjang belanja
├── checkout.php            # Checkout + pilih metode bayar
├── pembayaran.php          # Instruksi QRIS / transfer
├── pesanan.php             # Riwayat pesanan pelanggan
├── login.php
├── register.php
├── setup.php               # Asisten setup database
├── keranjang.php           # Alias lama -> cart.php
├── .env.example            # Contoh konfigurasi environment
└── README.md
```

Struktur lengkap dan aturan penempatan ada di [`docs/STRUCTURE.md`](docs/STRUCTURE.md).

## 🚀 Getting Started

### Prerequisites

Pastikan sudah menginstal:
- [XAMPP](https://www.apachefriends.org/)
- [Git](https://git-scm.com/)
- Visual Studio Code

### 1. Clone Repository

```bash
git clone https://github.com/adam-bassam0/toko-buku.git
```

Masuk ke direktori project:

```bash
cd toko-buku
```

### 2. Setup Database

1. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
2. Buka phpMyAdmin.
3. **Instalasi baru:** import file `database/schema.sql` (sudah termasuk tabel kategori, buku, users, pesanan, dan pembayaran).
4. **Database yang sudah ada:** 
   - Import `database/migrasi_auth.sql` (tabel login/pesanan).
   - Import `database/migrasi_pembayaran.sql` (tabel pembayaran).
   - Import `database/migrasi_kategori.sql` (tabel kategori, foreign key pada buku, dan pemetaan kategori buku bawaan).
5. Salin `.env.example` menjadi `.env`, lalu sesuaikan kredensial database di dalamnya.

### 3. Akun Demo

| Role | Email | Password |
|---|---|---|
| Admin | `admin@bukuku.test` | `admin123` |
| Pelanggan | `budi@bukuku.test` | `user123` |

> Setelah login sebagai admin, menu **Panel Admin** muncul di navbar.
> Pelanggan yang mencoba membuka `admin/` akan ditolak (redirect ke login).

### 4. Jalankan aplikasi

Pastikan project berada di direktori:

```text
C:\XAMPP\htdocs\toko-buku
```

Jalankan Apache dan MySQL melalui XAMPP Control Panel untuk mengakses aplikasi PHP.

Alamat lokal:

```text
http://localhost/toko-buku
```

*Catatan: Jika username, password, atau nama database MySQL berbeda, sesuaikan file `.env`.*

## 💳 Pembayaran (QRIS & Transfer Bank)

Setelah checkout, pelanggan memilih metode pembayaran:

| Metode | Tampilan | Konfirmasi |
|---|---|---|
| **QRIS** | Kode QR berisi kode bayar & nominal (simulasi) | Tombol "Saya Sudah Membayar" |
| **Transfer Bank** | Rekening BCA `1234567890` / Mandiri `9876543210` a.n. BukuKu Store + kode bayar sebagai berita transfer | Tombol + upload bukti (JPG/PNG/WebP, maks. 2 MB) |

**Alur status:**
```text
checkout → pesanan.status = 'pending'  → pembayaran.status = 'menunggu'
konfirmasi → pesanan.status = 'dibayar' → pembayaran.status = 'lunas'
```

**Keamanan pembayaran:**
- Halaman & konfirmasi hanya untuk **pemilik pesanan** (`WHERE user_id = ?`).
- Konfirmasi memakai **POST + CSRF**.
- Upload bukti divalidasi dari **isi file** (`finfo`), bukan nama file; nama disimpan acak.
- Update pembayaran + pesanan dilakukan dalam **satu transaksi** (ACID) dengan `FOR UPDATE`.
- Admin dapat memantau semua pesanan dan bukti transfer di `admin/pesanan.php`.

> ⚠️ Ini pembayaran **simulasi** untuk pembelajaran: tidak terhubung ke payment gateway dan tidak ada uang yang benar-benar berpindah.

## 🎯 Project Goals

Project ini dibuat sebagai sarana pembelajaran dan pengembangan kemampuan dalam:

- Mengembangkan backend menggunakan PHP Native.
- Mengelola database relasional menggunakan MySQL.
- Menerapkan autentikasi, otorisasi berbasis role, dan keamanan dasar aplikasi web.
- Menerapkan transaksi database (ACID) pada alur checkout.
- Menerapkan responsive UI/UX dengan Bootstrap.
- Menggunakan Git dan GitHub dalam workflow pengembangan (branch `main` & `develop`).

## 📌 Future Improvements

Beberapa pengembangan yang direncanakan:

- Integrasi Google Books API untuk mendapatkan informasi buku.
- Pengembangan sistem pencarian yang lebih optimal.
- Peningkatan fitur filter dan kategori.
- Halaman admin untuk mengelola status pesanan (pending → dibayar → dikirim).
- Integrasi payment gateway / upload bukti pembayaran.
- Optimasi performa dan pengalaman pengguna.

## 👨‍💻 Developer

Developed as a personal learning project to explore modern web development technologies and full-stack application development.

---

⭐ If you find this project interesting, feel free to explore the repository!
