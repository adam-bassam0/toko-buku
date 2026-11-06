# 📚 BukuKu — Digital Bookstore

BukuKu adalah aplikasi website katalog buku digital yang dirancang untuk memudahkan pengguna dalam mencari, menjelajahi, dan mendapatkan informasi mengenai berbagai koleksi buku.

Project ini dikembangkan menggunakan PHP Native dan MySQL. Antarmuka menggunakan Bootstrap 5 melalui CDN dengan fokus pada desain yang sederhana, responsif, dan mudah digunakan.

## ✨ Features

### User (Pelanggan)
- **Book Catalog** — Menampilkan koleksi buku dengan informasi lengkap.
- **Book Search** — Mencari buku berdasarkan judul.
- **Book Details** — Melihat informasi detail buku, termasuk penulis, penerbit, harga, dan deskripsi.
- **Register & Login** — Daftar akun dan masuk sebagai pelanggan (password di-hash bcrypt).
- **Shopping Cart** — Menambah, mengubah jumlah, dan menghapus buku dari keranjang (dibatasi stok).
- **Checkout** — Membuat pesanan; stok berkurang otomatis dalam satu transaksi database.
- **Order History** — Melihat riwayat pesanan milik sendiri.
- **Responsive Design** — Tampilan yang menyesuaikan berbagai ukuran layar.

### Admin
- **Admin Dashboard** — Halaman pengelolaan buku, hanya untuk akun ber-role `admin`.
- **Add Books** — Menambahkan koleksi buku.
- **Edit Books** — Memperbarui informasi buku.
- **Delete Books** — Menghapus koleksi buku.
- **Role Protection** — Pelanggan tidak bisa membuka halaman admin.

### Keamanan
- **Anti SQL Injection** — Semua query yang melibatkan input pengguna memakai *prepared statement*.
- **Anti XSS** — Output dibungkus `htmlspecialchars()`.
- **Anti CSRF** — Token acak per session pada semua form yang mengubah data (login, registrasi, keranjang, CRUD admin, checkout, logout).
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
├── config/
│   ├── koneksi.php         # Koneksi database + mode APP_ENV
│   └── auth.php            # Session, CSRF, login guard, role admin
│
├── database/
│   ├── schema.sql          # Skema lengkap (buku, users, pesanan, detail_pesanan)
│   └── migrasi_auth.sql    # Migrasi untuk database yang sudah ada
│
├── partials/
│   └── navbar.php          # Navbar bersama (menu menyesuaikan status login)
│
├── proses/
│   ├── proses_tambah.php   # INSERT buku (khusus admin + CSRF)
│   ├── proses_edit.php     # UPDATE buku (khusus admin + CSRF)
│   ├── proses_hapus.php    # DELETE buku (khusus admin + CSRF)
│   ├── proses_login.php    # Verifikasi login
│   ├── proses_register.php # Pendaftaran akun
│   ├── proses_logout.php   # Keluar akun
│   └── proses_checkout.php # Transaksi pesanan (ACID)
│
├── admin/                  # Panel admin (dilindungi require_admin)
├── assets/
├── index.php               # Katalog + pencarian
├── detail.php              # Detail buku
├── cart.php                # Keranjang belanja
├── checkout.php            # Konfirmasi pesanan
├── pesanan.php             # Riwayat pesanan pelanggan
├── login.php
├── register.php
└── proses_cart.php         # Aksi keranjang
```

## 🚀 Getting Started

### Prerequisites

Pastikan sudah menginstal:
- [XAMPP](https://www.apachefriends.org/)
- [Git](https://git-scm.com/)
- Visual Studio Code

### 1. Clone Repository

```bash
git clone YOUR_REPOSITORY_URL
```

Masuk ke direktori project:

```bash
cd toko-buku
```

### 2. Setup Database

1. Jalankan Apache dan MySQL melalui XAMPP Control Panel.
2. Buka phpMyAdmin.
3. **Instalasi baru:** import file `database/schema.sql`.
4. **Database yang sudah ada:** import `database/migrasi_auth.sql` (hanya menambah tabel `users`, `pesanan`, `detail_pesanan` + akun contoh, tanpa menghapus data buku).
5. Sesuaikan konfigurasi koneksi database pada `config/koneksi.php`.

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

*Catatan: Jika username, password, atau nama database MySQL berbeda, sesuaikan `config/koneksi.php`.*

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
