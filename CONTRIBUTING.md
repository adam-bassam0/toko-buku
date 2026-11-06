# Panduan Pengembangan — BukuKu

## 🔀 Workflow Branch

| Branch | Fungsi |
|---|---|
| `main` | Versi stabil / siap dipakai |
| `develop` | Tempat pengembangan fitur baru |
| `fitur/nama-fitur` | Branch fitur, dibuat dari `develop` |

## Alur Kerja Harian

```bash
# 1. Selalu mulai dari branch develop
git checkout develop

# 2. (Opsional, untuk fitur besar) buat branch fitur
git checkout -b fitur/nama-fitur

# 3. Commit dan dorong ke GitHub
git add .
git commit -m "Tambah fitur X"
git push -u origin fitur/nama-fitur

# 4. Fitur selesai -> merge ke develop
git checkout develop
git merge fitur/nama-fitur

# 5. develop sudah stabil -> merge ke main (rilis)
git checkout main
git merge develop
```

> **Catatan:** fitur terbaru (keranjang, README, dsb.) saat ini ada di `main`.
> Setelah `develop` di-merge dengan `main` satu kali, kedua branch akan sejajar
> dan workflow di atas bisa berjalan normal.

## ⚙️ Mode Environment

Konfigurasi ada di `config/koneksi.php`:

```php
define('APP_ENV', 'dev');   // error PHP tampilkan -> saat ngoding
define('APP_ENV', 'prod');  // error disembunyikan -> saat sudah online
```

- **`dev`**: semua error dan detail MySQL ditampilkan untuk mempermudah debug.
- **`prod`**: detail error disembunyikan agar tidak membocorkan informasi ke pengguna.

## 🚫 File yang Tidak Di-commit

Lihat `.gitignore`. Poin penting:

- `Thumbs.db`, `.DS_Store`, folder `.vscode/` / `.idea/`
- File log (`*.log`) dan file sementara
- `config/koneksi_local.php` — taruh kredensial asli/kursing di sini jika suatu saat ada server produksi
