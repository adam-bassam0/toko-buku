# 📚 BukuKu — Digital Bookstore

BukuKu adalah aplikasi website katalog buku digital yang dirancang untuk memudahkan pengguna dalam mencari, menjelajahi, dan mendapatkan informasi mengenai berbagai koleksi buku.

Project ini dikembangkan menggunakan PHP Native dan MySQL. Antarmuka menggunakan Bootstrap 5 melalui CDN dengan fokus pada desain yang sederhana, responsif, dan mudah digunakan.

## ✨ Features

### User
- **Book Catalog** — Menampilkan koleksi buku dengan informasi lengkap.
- **Book Search** — Mencari buku berdasarkan judul.
- **Book Details** — Melihat informasi detail buku, termasuk penulis, penerbit, harga, dan deskripsi.
- **Responsive Design** — Tampilan yang menyesuaikan berbagai ukuran layar.

### Admin
- **Admin Dashboard** — Halaman pengelolaan buku.
- **Add Books** — Menambahkan koleksi buku.
- **Edit Books** — Memperbarui informasi buku.
- **Delete Books** — Menghapus koleksi buku.

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
│   └── koneksi.php
│
├── database/
│   └── schema.sql
│
├── proses/
│   ├── proses_tambah.php
│   ├── proses_edit.php
│   └── proses_hapus.php
│
├── assets/
├── index.php
└── detail.php
```

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
3. Buat database sesuai konfigurasi project.
4. Import file `database/schema.sql`.
5. Sesuaikan konfigurasi koneksi database pada `config/koneksi.php`.

### 3. Jalankan aplikasi

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
- Menerapkan responsive UI/UX dengan Bootstrap.
- Menggunakan Git dan GitHub dalam workflow pengembangan.

## 📌 Future Improvements

Beberapa pengembangan yang direncanakan:

- Integrasi Google Books API untuk mendapatkan informasi buku.
- Pengembangan sistem pencarian yang lebih optimal.
- Peningkatan fitur filter dan kategori.
- Optimasi performa dan pengalaman pengguna.
- Peningkatan keamanan dan validasi backend.

## 👨‍💻 Developer

Developed as a personal learning project to explore modern web development technologies and full-stack application development.

---

⭐ If you find this project interesting, feel free to explore the repository!
