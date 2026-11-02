# 📚 BukuKu — Digital Bookstore

BukuKu adalah aplikasi website katalog buku digital yang dirancang untuk memudahkan pengguna dalam mencari, menjelajahi, dan mendapatkan informasi mengenai berbagai koleksi buku.

Project ini dikembangkan menggunakan React dan TypeScript untuk frontend, PHP Native sebagai backend, serta MySQL sebagai database. Antarmuka dibangun menggunakan shadcn/ui dan Tailwind CSS dengan fokus pada desain yang modern, responsif, dan mudah digunakan.

## ✨ Features

### User
- **Book Catalog** — Menampilkan koleksi buku dengan informasi lengkap.
- **Book Search** — Mencari buku berdasarkan judul.
- **Book Categories** — Mengelompokkan dan memfilter buku berdasarkan kategori.
- **Book Details** — Melihat informasi detail buku, termasuk penulis, penerbit, harga, dan deskripsi.
- **Responsive Design** — Tampilan yang menyesuaikan berbagai ukuran layar.

### Admin
- **Admin Dashboard** — Halaman pengelolaan buku.
- **Add Books** — Menambahkan koleksi buku.
- **Edit Books** — Memperbarui informasi buku.
- **Delete Books** — Menghapus koleksi buku.
- **Category Management** — Mengelola kategori buku.

## 🛠️ Tech Stack

| Technology | Usage |
|---|---|
| React.js | Frontend library |
| TypeScript | Type-safe JavaScript |
| Vite | Frontend build tool |
| Tailwind CSS | Styling |
| shadcn/ui | UI components |
| Motion | UI animations |
| PHP Native | Backend |
| MySQL | Database |
| Laragon | Local development environment |
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
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   ├── hooks/
│   │   ├── types/
│   │   └── lib/
│   ├── package.json
│   └── vite.config.ts
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
- [Node.js](https://nodejs.org/)
- [Laragon](https://laragon.org/)
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

1. Jalankan Apache dan MySQL melalui Laragon.
2. Buka phpMyAdmin.
3. Buat database sesuai konfigurasi project.
4. Import file `database/schema.sql`.
5. Sesuaikan konfigurasi koneksi database pada `config/koneksi.php`.

### 3. Setup Frontend

Masuk ke folder frontend:

```bash
cd frontend
```

Install dependencies:

```bash
npm install
```

Jalankan development server:

```bash
npm run dev
```

Buka alamat yang ditampilkan oleh Vite, biasanya:

```text
http://localhost:5173
```

### 4. Backend

Pastikan project berada di direktori:

```text
C:\laragon\www\toko-buku
```

Jalankan Apache dan MySQL melalui Laragon untuk mengakses backend PHP.

Alamat lokal:

```text
http://localhost/toko-buku
```

*Catatan: Sesuaikan alamat akses dan konfigurasi API dengan implementasi frontend-backend yang digunakan.*

## 🎯 Project Goals

Project ini dibuat sebagai sarana pembelajaran dan pengembangan kemampuan dalam:

- Membangun aplikasi frontend menggunakan React dan TypeScript.
- Mengimplementasikan reusable components menggunakan shadcn/ui.
- Mengembangkan backend menggunakan PHP Native.
- Mengelola database relasional menggunakan MySQL.
- Mengintegrasikan frontend dan backend.
- Menerapkan responsive UI/UX dan animasi.
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
