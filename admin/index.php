<?php
// ============================================
// File    : admin/index.php
// Deskripsi: Halaman Panel Admin untuk
//            mengelola (CRUD) data buku.
//            Menampilkan semua buku dalam tabel.
// ============================================

// Path ke koneksi relatif terhadap lokasi file ini (folder admin/)
require_once '../config/koneksi.php';

// Ambil semua data buku dari database, urutkan dari yang terbaru
$result = $koneksi->query("SELECT * FROM buku ORDER BY id DESC");

// Tangkap pesan notifikasi dari proses CRUD (dikirim via URL parameter)
// Contoh URL: admin/index.php?pesan=tambah_sukses
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- ===== NAVBAR ADMIN ===== -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #0f3460;">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-shield-lock me-2"></i>Admin BukuKu
            </a>
            <div class="ms-auto">
                <a href="../index.php" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Ke Toko
                </a>
            </div>
        </div>
    </nav>

    <!-- ===== KONTEN UTAMA ===== -->
    <main class="container-fluid px-4 my-4">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="fw-bold mb-0">
                <i class="bi bi-journal-text me-2"></i>Manajemen Data Buku
            </h4>
            <!-- Tombol navigasi ke form tambah buku -->
            <a href="tambah.php" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Tambah Buku Baru
            </a>
        </div>

        <!-- ===== NOTIFIKASI PESAN ===== -->
        <?php if ($pesan === 'tambah_sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Buku baru berhasil ditambahkan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'edit_sukses'): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-pencil-fill me-2"></i>Data buku berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-trash-fill me-2"></i>Buku berhasil dihapus.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ===== TABEL DATA BUKU ===== -->
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-admin mb-0">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">#</th>
                                <th width="70" class="text-center">Cover</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Penerbit</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">Stok</th>
                                <th width="160" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php $no = 1; // Nomor urut baris ?>
                                <?php while ($buku = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center">
                                            <!-- Thumbnail cover buku -->
                                            <img
                                                src="<?= $buku['cover'] ? htmlspecialchars($buku['cover']) : 'https://via.placeholder.com/55x70?text=N/A' ?>"
                                                class="cover-thumb"
                                                alt="Cover"
                                                onerror="this.src='https://via.placeholder.com/55x70?text=N/A'"
                                            >
                                        </td>
                                        <td><?= htmlspecialchars($buku['judul']) ?></td>
                                        <td><?= htmlspecialchars($buku['penulis']) ?></td>
                                        <td><?= htmlspecialchars($buku['penerbit']) ?></td>
                                        <td class="text-end">Rp <?= number_format($buku['harga'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <!-- Warnai stok berdasarkan jumlah -->
                                            <span class="badge <?= $buku['stok'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $buku['stok'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <!-- Tombol Edit: kirim ID buku ke halaman edit -->
                                            <a href="edit.php?id=<?= $buku['id'] ?>" class="btn btn-warning btn-sm me-1" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <!-- Tombol Hapus: panggil fungsi JS untuk konfirmasi sebelum hapus -->
                                            <button
                                                onclick="konfirmasiHapus('../proses/proses_hapus.php?id=<?= $buku['id'] ?>')"
                                                class="btn btn-danger btn-sm"
                                                title="Hapus"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!-- Pesan jika tabel masih kosong -->
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        Belum ada data buku. <a href="tambah.php">Tambahkan buku pertama!</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Informasi total buku -->
        <p class="text-muted small mt-2">
            Total: <strong><?= $result->num_rows ?></strong> buku terdaftar.
        </p>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>

