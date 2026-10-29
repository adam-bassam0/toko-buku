<?php
// ============================================
// File    : detail.php
// Deskripsi: Halaman detail satu buku
//            berdasarkan ID yang dikirim via URL.
//            Contoh URL: detail.php?id=3
// ============================================

require_once 'config/koneksi.php';

// Ambil dan validasi parameter ID dari URL
// Pastikan ID adalah angka positif untuk keamanan
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    // Redirect ke halaman utama jika ID tidak valid
    header("Location: index.php");
    exit;
}

// Gunakan prepared statement untuk mencegah SQL Injection
$stmt = $koneksi->prepare("SELECT * FROM buku WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id); // "i" = integer
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah buku dengan ID tersebut ditemukan
if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

// Ambil data buku sebagai array asosiatif
$buku = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($buku['judul']) ?> - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book-half me-2"></i>BukuKu
            </a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php"><i class="bi bi-gear me-1"></i>Panel Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ===== KONTEN DETAIL ===== -->
    <main class="container my-5">

        <!-- Breadcrumb navigasi -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Buku</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0">
                <!-- Kolom Gambar Cover -->
                <div class="col-md-4 bg-light d-flex align-items-center justify-content-center p-4">
                    <img
                        src="<?= $buku['cover'] ? htmlspecialchars($buku['cover']) : 'https://via.placeholder.com/300x420?text=No+Cover' ?>"
                        class="detail-cover img-fluid"
                        alt="Cover <?= htmlspecialchars($buku['judul']) ?>"
                        onerror="this.src='https://via.placeholder.com/300x420?text=No+Cover'"
                    >
                </div>

                <!-- Kolom Informasi Buku -->
                <div class="col-md-8">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="card-title fw-bold mb-1"><?= htmlspecialchars($buku['judul']) ?></h2>

                        <!-- Informasi singkat penulis dan penerbit -->
                        <p class="text-muted mb-3">
                            <i class="bi bi-person-fill me-1"></i>
                            <strong>Penulis:</strong> <?= htmlspecialchars($buku['penulis']) ?>
                            &nbsp;|&nbsp;
                            <i class="bi bi-building me-1"></i>
                            <strong>Penerbit:</strong> <?= htmlspecialchars($buku['penerbit']) ?>
                        </p>

                        <!-- Harga -->
                        <h3 class="text-primary fw-bold mb-3">
                            Rp <?= number_format($buku['harga'], 0, ',', '.') ?>
                        </h3>

                        <!-- Status stok: tampilkan badge sesuai jumlah stok -->
                        <div class="mb-4">
                            <?php if ($buku['stok'] > 10): ?>
                                <span class="badge bg-success badge-stok">
                                    <i class="bi bi-check-circle me-1"></i>Stok Tersedia (<?= $buku['stok'] ?> unit)
                                </span>
                            <?php elseif ($buku['stok'] > 0): ?>
                                <span class="badge bg-warning text-dark badge-stok">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Stok Terbatas (<?= $buku['stok'] ?> unit)
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger badge-stok">
                                    <i class="bi bi-x-circle me-1"></i>Stok Habis
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Deskripsi buku -->
                        <h5 class="fw-bold">Deskripsi</h5>
                        <hr>
                        <p class="card-text text-secondary lh-lg">
                            <?= nl2br(htmlspecialchars($buku['deskripsi'] ?? 'Tidak ada deskripsi tersedia.')) ?>
                        </p>

                        <!-- Tombol aksi -->
                        <div class="d-flex gap-2 mt-4 flex-wrap">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
                            </a>
                            <a href="admin/edit.php?id=<?= $buku['id'] ?>" class="btn btn-warning">
                                <i class="bi bi-pencil me-1"></i>Edit Buku
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="text-center mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> BukuKu &mdash; Tugas Sistem Basis Data</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

