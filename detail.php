<?php

require_once 'config/auth.php';
require_once 'config/koneksi.php';
$jumlahCart = array_sum($_SESSION['cart'] ?? []);

// Ambil dan validasi parameter ID dari URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Gunakan prepared statement untuk keamanan
$stmt = $koneksi->prepare(
    "SELECT b.*, k.nama AS nama_kategori, k.slug AS slug_kategori
     FROM buku b
     LEFT JOIN kategori k ON b.kategori_id = k.id
     WHERE b.id = ? LIMIT 1"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$buku = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($buku['judul']) ?> — BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <?php require __DIR__ . '/app/views/partials/navbar.php'; ?>

    <!-- ===== KONTEN DETAIL ===== -->
    <main class="container my-5 flex-grow-1">

        <!-- Breadcrumb Navigasi -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white px-3 py-2 rounded-3 border border-slate-200 shadow-xs">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <?php if (!empty($buku['nama_kategori'])): ?>
                    <li class="breadcrumb-item">
                        <a href="index.php?kategori=<?= urlencode($buku['slug_kategori'] ?? $buku['kategori_id']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($buku['nama_kategori']) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 320px;">
                    <?= htmlspecialchars($buku['judul']) ?>
                </li>
            </ol>
        </nav>

        <div class="card detail-card border-0 shadow-sm overflow-hidden bg-white mb-5">
            <div class="row g-0">
                <!-- Kolom Cover Buku -->
                <div class="col-lg-5 p-4 p-md-5 d-flex flex-column align-items-center justify-content-center bg-slate-50 border-end border-slate-200">
                    <div class="detail-cover-wrapper w-100" style="max-width: 340px;">
                        <img
                            src="<?= $buku['cover'] ? htmlspecialchars($buku['cover']) : 'https://via.placeholder.com/340x480?text=No+Cover' ?>"
                            alt="Cover <?= htmlspecialchars($buku['judul']) ?>"
                            class="img-fluid w-100"
                            onerror="this.src='https://via.placeholder.com/340x480?text=No+Cover'"
                        >
                    </div>

                    <!-- Keunggulan Produk -->
                    <div class="w-100 mt-4 pt-3 border-top border-slate-200" style="max-width: 340px;">
                        <div class="d-flex align-items-center gap-2 small text-muted mb-2">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            <span>100% Produk Original langsung dari penerbit</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small text-muted mb-2">
                            <i class="bi bi-box-seam text-primary fs-5"></i>
                            <span>Kemasan rapi dan aman dengan bubble wrap</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 small text-muted">
                            <i class="bi bi-arrow-clockwise text-warning fs-5"></i>
                            <span>Garansi tukar jika ada cacat cetak</span>
                        </div>
                    </div>
                </div>

                <!-- Kolom Rincian Buku -->
                <div class="col-lg-7 p-4 p-md-5 d-flex flex-column">
                    <!-- Kategori & Meta -->
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <?php if (!empty($buku['nama_kategori'])): ?>
                            <a href="index.php?kategori=<?= urlencode($buku['slug_kategori'] ?? $buku['kategori_id']) ?>" class="badge category-badge text-decoration-none">
                                <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($buku['nama_kategori']) ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($buku['stok'] > 10): ?>
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle me-1"></i>Stok Tersedia (<?= $buku['stok'] ?> unit)
                            </span>
                        <?php elseif ($buku['stok'] > 0): ?>
                            <span class="badge bg-warning-subtle text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>Stok Terbatas (<?= $buku['stok'] ?> unit)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">
                                <i class="bi bi-x-circle me-1"></i>Stok Habis
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="h2 fw-bold text-slate-900 mb-3" style="letter-spacing: -0.02em;">
                        <?= htmlspecialchars($buku['judul']) ?>
                    </h1>

                    <!-- Informasi Penulis & Penerbit -->
                    <div class="p-3 bg-slate-50 rounded-3 border border-slate-200 mb-4">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="small text-muted">Penulis</div>
                                <div class="fw-semibold text-slate-800">
                                    <i class="bi bi-person me-1 text-primary"></i><?= htmlspecialchars($buku['penulis']) ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="small text-muted">Penerbit</div>
                                <div class="fw-semibold text-slate-800">
                                    <i class="bi bi-building me-1 text-primary"></i><?= htmlspecialchars($buku['penerbit']) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Price Box -->
                    <div class="detail-price-box d-flex align-items-baseline justify-content-between mb-4">
                        <div>
                            <div class="small text-muted text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Harga Satuan</div>
                            <h2 class="h1 fw-bold text-primary mb-0">
                                Rp <?= number_format($buku['harga'], 0, ',', '.') ?>
                            </h2>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted">Termasuk PPN</span>
                        </div>
                    </div>

                    <!-- Sinopsis / Deskripsi -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-slate-900 mb-2">Deskripsi Buku</h5>
                        <div class="text-secondary lh-lg" style="font-size: 0.95rem;">
                            <?= nl2br(htmlspecialchars($buku['deskripsi'] ?? 'Tidak ada deskripsi tersedia untuk buku ini.')) ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 flex-wrap mt-auto pt-3 border-top border-slate-200">
                        <form action="proses/proses_cart.php" method="POST" class="flex-grow-1" style="max-width: 320px;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="aksi" value="tambah">
                            <input type="hidden" name="id_buku" value="<?= (int) $buku['id'] ?>">
                            <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2" <?= $buku['stok'] <= 0 ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus-fill fs-5"></i>
                                <?= $buku['stok'] > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' ?>
                            </button>
                        </form>

                        <a href="index.php" class="btn btn-outline-secondary btn-lg d-flex align-items-center">
                            <i class="bi bi-arrow-left me-2"></i>Kembali ke Katalog
                        </a>

                        <?php if (auth_is_admin()): ?>
                            <a href="admin/edit.php?id=<?= $buku['id'] ?>" class="btn btn-outline-dark btn-lg ms-auto" title="Edit di Panel Admin">
                                <i class="bi bi-pencil me-1"></i>Edit Buku
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="modern-footer">
        <div class="container text-center small text-slate-400">
            <p class="mb-0">&copy; <?= date('Y') ?> BukuKu Store &mdash; Digital Bookstore. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
