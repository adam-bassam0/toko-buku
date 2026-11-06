# 🎤 Materi Presentasi — BukuKu (Toko Buku Online)

> Dokumen ini disusun berdasarkan kode asli di project, sudah mencakup
> fitur terbaru: login user/admin, keamanan SQL Injection, dan transaksi ACID.

---

## 1. Gambaran Umum

**BukuKu** adalah aplikasi web toko buku online dengan **PHP Native + MySQL**.
Pengunjung dapat mencari buku, menyusun keranjang, dan checkout setelah login.
Admin mengelola data buku lewat panel khusus yang terlindungi.

---

## 2. Arsitektur Sistem (3-Tier)

```
Browser Pengguna          Server (Apache + PHP)           Database
┌───────────────┐   HTTP    ┌──────────────────┐   SQL   ┌──────────────────┐
│  HTML + CSS   │ ────────► │  File .php       │ ──────► │  db_toko_buku    │
│  (Bootstrap)  │ ◄──────── │  (olah request,  │ ◄────── │  buku, users,    │
└───────────────┘   HTML    │   query, render) │  hasil  │  pesanan, detail │
                            └──────────────────┘         └──────────────────┘
```

Setiap klik: browser mengirim HTTP request → PHP memproses + query SQL →
MySQL mengembalikan hasil → PHP merakit HTML → dikirim ke browser.

---

## 3. Struktur Folder & Peran File

```text
toko-buku/
├── config/
│   ├── koneksi.php          # Koneksi DB + mode APP_ENV (dev/prod)
│   └── auth.php             # Session, CSRF, guard login & role admin
├── database/
│   ├── schema.sql           # Skema lengkap + data contoh
│   └── migrasi_auth.sql     # Migrasi untuk DB yang sudah ada
├── partials/
│   └── navbar.php           # Navbar bersama (menu sesuai status login)
├── proses/                  # Lapisan proses (tanpa render HTML)
│   ├── proses_tambah.php    # INSERT buku   (admin + CSRF)
│   ├── proses_edit.php      # UPDATE buku   (admin + CSRF)
│   ├── proses_hapus.php     # DELETE buku   (admin + CSRF)
│   ├── proses_login.php     # Verifikasi login
│   ├── proses_register.php  # Pendaftaran akun
│   ├── proses_logout.php    # Keluar akun
│   └── proses_checkout.php  # Transaksi pesanan (ACID)
├── admin/                   # Panel admin, dilindungi require_admin()
├── assets/                  # CSS & JS
├── index.php                # Katalog + pencarian
├── detail.php               # Detail buku
├── cart.php                 # Keranjang (session)
├── checkout.php             # Konfirmasi pesanan
├── pesanan.php              # Riwayat pesanan pelanggan
├── login.php / register.php # Autentikasi
└── proses_cart.php          # Aksi keranjang
```

**Pemisahan tanggung jawab:**
- Halaman `*.php` menampilkan data (SELECT + render).
- Folder `proses/` mengubah data (INSERT/UPDATE/DELETE + redirect).
- `config/` menyimpan koneksi dan seluruh logika keamanan.

---

## 4. Sistem Basis Data

### Tabel `buku`
| Kolom | Tipe | Fungsi |
|---|---|---|
| `id` | INT AUTO_INCREMENT, PK | Identitas unik |
| `judul` | VARCHAR(150) | Judul buku |
| `penulis` | VARCHAR(100) | Nama penulis |
| `penerbit` | VARCHAR(100) | Nama penerbit |
| `harga` | DECIMAL(12,0) | Harga rupiah (bukan FLOAT agar presisi) |
| `stok` | INT | Stok; dipakai validasi keranjang & checkout |
| `deskripsi` | TEXT | Sinopsis |
| `cover` | VARCHAR(255) | URL gambar cover |

### Tabel `users`
| Kolom | Tipe | Fungsi |
|---|---|---|
| `id` | INT AUTO_INCREMENT, PK | Identitas user |
| `nama` | VARCHAR(100) | Nama lengkap |
| `email` | VARCHAR(150), UNIQUE | Login (tidak boleh ganda) |
| `password` | VARCHAR(255) | Hash bcrypt, **bukan** teks asli |
| `role` | ENUM('user','admin') | Menentukan hak akses |
| `created_at` | TIMESTAMP | Waktu registrasi |

### Tabel `pesanan` (header transaksi)
`id`, `user_id` (FK → users), `total` DECIMAL, `status` ENUM(pending/dibayar/dikirim/selesai/batal), `created_at`.

### Tabel `detail_pesanan` (rincian)
`id`, `pesanan_id` (FK → pesanan, ON DELETE CASCADE), `buku_id` (FK → buku, ON DELETE SET NULL), `judul`, `harga`, `jumlah`, `subtotal`.

> **Kenapa judul & harga disalin ke detail?** Supaya riwayat pesanan tetap
> utuh walaupun admin mengubah/menghapus buku (snapshot data).

**Poin teknis:**
- Semua tabel memakai **InnoDB** → mendukung transaksi & foreign key.
- **utf8mb4** → mendukung karakter Indonesia & emoji.
- Relasi: 1 user → banyak pesanan; 1 pesanan → banyak detail; 1 buku → banyak detail.

---

## 5. Sistem Login User & Admin

### Alur
```
register.php ──► proses_register.php ──► INSERT users (role selalu 'user')
login.php    ──► proses_login.php    ──► verifikasi + session
logout       ──► proses_logout.php   ──► session dihapus
```

### Detail keamanan login
1. **Password hashing** — `password_hash()` (bcrypt + salt otomatis). Database tidak pernah menyimpan password asli.
2. **Verifikasi** — `password_verify()` membandingkan input dengan hash.
3. **Prepared statement** — `SELECT ... WHERE email = ?`; payload seperti `' OR '1'='1` diperlakukan sebagai teks biasa.
4. **Session fixation dicegah** — `session_regenerate_id(true)` setelah login berhasil.
5. **Cookie session** — `HttpOnly` + `SameSite=Lax`.
6. **Pesan error umum** — "Email atau password salah" (tidak membocorkan email mana yang terdaftar).
7. **Role dari server** — registrasi memaksa `role='user'`; user tidak bisa mengirim role lewat form.

### Otorisasi (hak akses)
- `require_login()` — halaman checkout & pesanan wajib login.
- `require_admin()` — seluruh folder `admin/` dan file CRUD di `proses/` hanya untuk role `admin`.
- Pelanggan yang membuka `admin/index.php` → redirect ke login + pesan "bukan admin".
- Tombol **Panel Admin** hanya muncul di navbar jika role admin.

**Akun demo:** admin `admin@bukuku.test / admin123` — pelanggan `budi@bukuku.test / user123`.

---

## 6. Cara Kerja Keranjang & Checkout

### Keranjang (cart.php + proses_cart.php)
- Disimpan di session: `$_SESSION['cart'] = [id_buku => jumlah]`.
- Aksi: tambah, ubah jumlah, hapus, kosongkan — semuanya **POST + CSRF**.
- Jumlah dibatasi stok: `min(jumlah, stok)`; buku stok 0 otomatis dibuang.
- Harga **selalu diambil ulang dari database**, bukan dari browser (anti manipulasi).

### Checkout (checkout.php → proses_checkout.php)
1. Wajib login.
2. Item & total dihitung ulang dari database.
3. Semua perubahan dijalankan dalam **satu transaksi ACID** (bagian 7).
4. Jika sukses: keranjang dikosongkan, pesanan muncul di `pesanan.php`.
5. Jika gagal: **rollback** — tidak ada pesanan setengah jadi, stok tidak berubah.

---

## 7. ACID di Backend (bukti di kode)

File: `proses/proses_checkout.php`

```php
$koneksi->begin_transaction();
try {
    // 1. Kunci & validasi stok
    SELECT ... FROM buku WHERE id = ? FOR UPDATE
    // 2. Buat header pesanan
    INSERT INTO pesanan (user_id, total, status) VALUES (?, ?, ?)
    // 3. Rincian + kurangi stok
    INSERT INTO detail_pesanan (...)
    UPDATE buku SET stok = stok - ? WHERE id = ? AND stok >= ?
    $koneksi->commit();
} catch (Throwable $e) {
    $koneksi->rollback();
}
```

| Huruf | Arti | Penerapan |
|---|---|---|
| **A**tomicity | Semua berhasil atau semua batal | `begin_transaction` + `commit`/`rollback` |
| **C**onsistency | Data tetap valid | `WHERE stok >= ?`, FK, stok tidak bisa minus |
| **I**solation | Transaksi saling terisolasi | `SELECT ... FOR UPDATE` mengunci baris buku |
| **D**urability | Data tersimpan permanen | InnoDB `COMMIT` (write-ahead log) |

**Skenario demo rollback:** masukkan buku ke keranjang → ubah stok jadi 0 di
phpMyAdmin → checkout → pesanan **tidak dibuat**, muncul pesan "Checkout
dibatalkan: stok tidak mencukupi". Ini membuktikan Atomicity.

---

## 8. Keamanan yang Diterapkan ⭐

| Ancaman | Perlindungan |
|---|---|
| **SQL Injection** | Semua query berinput memakai `prepare()` + `bind_param()` (login, register, pencarian, detail, keranjang, CRUD admin, checkout, riwayat) |
| **XSS** | Semua output dibungkus `htmlspecialchars()` |
| **CSRF** | Token acak per session + `hash_equals()` pada login, register, keranjang, CRUD, checkout, logout |
| **Password bocor** | Hash bcrypt; password asli tidak pernah disimpan/ditampilkan |
| **Session fixation** | `session_regenerate_id(true)` setelah login |
| **Akses tidak sah** | `require_login()` & `require_admin()`; role dicek di server |
| **Manipulasi harga/ID** | Harga dihitung ulang dari DB; semua ID di-cast `(int)` |
| **Akses langsung file proses** | Wajib POST; file proses menolak GET |
| **Stok minus** | Dibatasi validasi keranjang + syarat `stok >= ?` di transaksi |
| **Kredensial produksi** | Mode `APP_ENV`: error detail hanya tampil saat `dev` |

**Contoh uji SQL Injection (bisa didemokan):**
- Login dengan email `' OR '1'='1` → **gagal** ("Email atau password salah").
- Pencarian `' OR 1=1 --` → dianggap judul biasa, hasil 0 buku, tidak ada error SQL.

---

## 9. Teknologi

| Teknologi | Peran |
|---|---|
| PHP Native | Backend, session, transaksi MySQLi |
| MySQL / InnoDB | Database relasional + ACID |
| Bootstrap 5 (CDN) | Layout responsif |
| Bootstrap Icons | Ikon |
| PHP Session | Login, keranjang, token CSRF |
| Git & GitHub | Branch `main` (stabil), `develop` (pengembangan) |
| XAMPP | Apache + MySQL lokal |

---

## 10. Antisipasi Pertanyaan Penguji (Q&A)

**T: Kenapa memakai PHP Native, bukan framework?**
J: Untuk memahami mekanisme dasar HTTP, session, SQL, dan keamanan secara langsung tanpa "sihir" framework.

**T: Bagaimana mencegah SQL Injection?**
J: Semua query berinput memakai prepared statement. Query dan data dikirim terpisah, sehingga input berbahaya dianggap data biasa.

**T: Kenapa password di-hash? Boleh di-decrypt?**
J: Tidak bisa di-decrypt — bcrypt bersifat one-way. Saat login, input di-hash-kan lalu dibandingkan dengan hash tersimpan (`password_verify`). Kalau database bocor, password asli tetap aman.

**T: Bagaimana membedakan user dan admin?**
J: Kolom `role` di tabel `users`. Setelah login, role disimpan di session; `require_admin()` menolak role selain admin.

**T: Apa itu ACID dan di mana diterapkan?**
J: Atomicity, Consistency, Isolation, Durability — diterapkan pada checkout: insert pesanan + rincian + kurangi stok dalam satu transaksi; gagal sedikit → rollback semua. `FOR UPDATE` menjaga isolasi stok.

**T: Kenapa keranjang di session, bukan database?**
J: Keranjang bersifat sementara sebelum checkout. Setelah checkout, data pindah permanen ke tabel `pesanan` & `detail_pesanan`.

**T: Kenapa harga di detail pesanan disalin?**
J: Snapshot — riwayat pesanan tetap benar walau harga buku berubah di kemudian hari.

**T: Apa yang terjadi jika dua orang checkout buku terakhir bersamaan?**
J: Baris buku dikunci `FOR UPDATE`. Transaksi kedua menunggu; setelah stok habis, syarat `stok >= jumlah` gagal → rollback dengan pesan stok tidak cukup. Stok tidak pernah minus.

**T: Apakah aman untuk produksi?**
J: Untuk pembelajaran sudah menerapkan fondasi keamanan utama. Produksi perlu HTTPS, rate limiting login, verifikasi email, dan manajemen konfigurasi rahasia.

---

## 11. Ide Pengembangan Lanjutan

1. Halaman admin mengelola status pesanan (pending → dibayar → dikirim).
2. Upload bukti pembayaran / payment gateway.
3. Verifikasi email & fitur lupa password.
4. Tabel `kategori` + filter katalog.
5. Pagination katalog & laporan penjualan.
6. Rate limiting percobaan login.

---

## 12. Tips Presentasi (5 menit)

1. **Demo pelanggan (2 menit):** daftar/masuk → cari buku → detail → tambah keranjang → checkout → lihat "Pesanan Saya".
2. **Demo admin (1 menit):** login admin → tambah buku → edit stok → hapus.
3. **Demo keamanan (1 menit):** coba login dengan `' OR '1'='1` → gagal; buka `admin/index.php` tanpa login → ditolak.
4. **Demo ACID (1 menit):** tunjukkan potongan kode transaksi + lakukan skenario rollback stok.
5. **Tutup** dengan tabel keamanan & rencana pengembangan.
