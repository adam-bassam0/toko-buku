<?php

require_once 'config/auth.php';
require_once 'config/koneksi.php';

// Jumlah seluruh buku di keranjang untuk badge navbar.
$jumlahCart = array_sum($_SESSION['cart'] ?? []);

// --- Logika Pencarian ---
// Ambil kata kunci dari parameter GET (URL: index.php?cari=...)
// Gunakan trim() untuk menghapus spasi di awal/akhir
$kataCari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

if ($kataCari !== '') {
    // Jika ada kata kunci, gunakan prepared statement untuk keamanan (mencegah SQL Injection)
    $stmt = $koneksi->prepare("SELECT * FROM buku WHERE judul LIKE ? ORDER BY judul ASC");
    $paramCari = "%" . $kataCari . "%"; // Tambahkan wildcard untuk pencarian sebagian
    $stmt->bind_param("s", $paramCari);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Jika tidak ada kata kunci, tampilkan semua buku
    $result = $koneksi->query("SELECT * FROM buku ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BukuKu - Toko Buku Online</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero-section text-center">
        <div class="container">
            <h1><i class="bi bi-journal-bookmark-fill me-3"></i>Selamat Datang di BukuKu</h1>
            <p class="lead mt-2 mb-4 text-white-50">Temukan buku favoritmu dengan harga terbaik</p>
            <!-- Form Pencarian -->
            <div class="search-wrapper">
                <form action="index.php" method="GET" class="d-flex gap-2">
                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-lg"
                        placeholder="Cari judul buku..."
                        value="<?= htmlspecialchars($kataCari) ?>"
                        autocomplete="off"
                    >
                    <button class="btn btn-warning btn-lg fw-bold" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if ($kataCari !== ''): ?>
                        <!-- Tombol reset pencarian -->
                        <a href="index.php" class="btn btn-outline-light btn-lg" title="Hapus pencarian">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </section>

    <!-- ===== KATALOG BUKU ===== -->
    <main class="container my-5">

        <!-- Informasi hasil pencarian -->
        <?php if ($kataCari !== ''): ?>
            <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                Menampilkan hasil pencarian untuk: <strong class="ms-1">"<?= htmlspecialchars($kataCari) ?>"</strong>
                &nbsp;&mdash;&nbsp; Ditemukan <strong><?= $result->num_rows ?></strong> buku.
            </div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
                <?php while ($buku = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card book-card shadow-sm">
                            <!-- Gambar cover buku; gunakan placeholder jika kosong -->
                            <img
                                src="<?= $buku['cover'] ? htmlspecialchars($buku['cover']) : 'https://via.placeholder.com/300x400?text=No+Cover' ?>"
                                class="card-img-top"
                                alt="Cover <?= htmlspecialchars($buku['judul']) ?>"
                                onerror="this.src='https://via.placeholder.com/300x400?text=No+Cover'"
                            >
                            <div class="card-body p-3">
                                <h6 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h6>
                                <p class="card-text penulis mb-1">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($buku['penulis']) ?>
                                </p>
                                <p class="card-text penulis mb-0">
                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($buku['penerbit']) ?>
                                </p>
                                <p class="harga">
                                    Rp <?= number_format($buku['harga'], 0, ',', '.') ?>
                                </p>
                                <!-- Tombol detail mengirim ID buku via parameter URL -->
                                <a href="detail.php?id=<?= $buku['id'] ?>" class="btn btn-primary btn-sm w-100 mt-1">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                                <form action="proses_cart.php" method="POST" class="mt-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="aksi" value="tambah">
                                    <input type="hidden" name="id_buku" value="<?= (int) $buku['id'] ?>">
                                    <button type="submit" class="btn btn-warning btn-sm w-100" <?= $buku['stok'] <= 0 ? 'disabled' : '' ?>>
                                        <i class="bi bi-cart-plus me-1"></i><?= $buku['stok'] > 0 ? 'Tambah ke Keranjang' : 'Stok Habis' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <!-- Tampilkan pesan jika tidak ada buku ditemukan -->
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Buku tidak ditemukan</h4>
                <p class="text-muted">Coba kata kunci yang berbeda atau <a href="index.php">lihat semua buku</a>.</p>
            </div>
        <?php endif; ?>

    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="text-center mt-auto">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y') ?> BukuKu &mdash; Tugas Sistem Basis Data. Dibuat dengan <i class="bi bi-heart-fill text-danger"></i></p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (termasuk Popper) via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>

