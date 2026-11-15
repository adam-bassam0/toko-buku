<?php
require_once 'config/auth.php';
require_once 'config/koneksi.php';

$_SESSION['cart'] ??= [];
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$jumlahCart = array_sum($_SESSION['cart']);
$pesan = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

$items = [];
$total = 0;
// Ambil ulang harga dan stok dari database, bukan mempercayai data dari browser/session.
foreach ($_SESSION['cart'] as $id => $jumlah) {
    $id = (int) $id;
    $jumlah = (int) $jumlah;
    $stmt = $koneksi->prepare('SELECT id, judul, penulis, harga, stok, cover FROM buku WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $buku = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$buku || (int) $buku['stok'] < 1) {
        unset($_SESSION['cart'][$id]);
        continue;
    }
    if ($jumlah > (int) $buku['stok']) {
        $jumlah = (int) $buku['stok'];
        $_SESSION['cart'][$id] = $jumlah;
    }
    $subtotal = (int) $buku['harga'] * $jumlah;
    $buku['jumlah'] = $jumlah;
    $buku['subtotal'] = $subtotal;
    $items[] = $buku;
    $total += $subtotal;
}
$jumlahCart = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja — BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- ===== NAVBAR ===== -->
    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-cart3 me-2 text-primary"></i>Keranjang Belanja
                </h2>
                <p class="text-muted mb-0">Kelola daftar buku yang ingin Anda beli sebelum melakukan checkout.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Lanjut Belanja
            </a>
        </div>

        <?php if ($pesan): ?>
            <div class="alert alert-<?= htmlspecialchars($pesan['type']) ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= htmlspecialchars($pesan['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>

        <?php if (!$items): ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                <div class="card-body">
                    <div class="d-inline-flex p-4 rounded-circle bg-light text-muted mb-3">
                        <i class="bi bi-cart-x display-3 text-secondary"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Keranjang Belanja Masih Kosong</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 450px;">
                        Anda belum menambahkan buku apa pun ke keranjang. Jelajahi ribuan koleksi buku menarik kami!
                    </p>
                    <a href="index.php" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-compass me-1"></i>Mulai Jelajahi Buku
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Daftar Item Keranjang -->
                <div class="col-lg-8">
                    <div class="table-card shadow-sm mb-3">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th colspan="2">Buku</th>
                                        <th>Harga</th>
                                        <th style="min-width: 140px;">Jumlah</th>
                                        <th>Subtotal</th>
                                        <th width="50" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td style="width: 70px;">
                                                <a href="detail.php?id=<?= $item['id'] ?>">
                                                    <img
                                                        class="cart-cover"
                                                        src="<?= htmlspecialchars($item['cover'] ?: 'https://via.placeholder.com/70x90?text=No+Cover') ?>"
                                                        alt="Cover buku"
                                                        onerror="this.src='https://via.placeholder.com/70x90?text=No+Cover'"
                                                    >
                                                </a>
                                            </td>
                                            <td>
                                                <a href="detail.php?id=<?= $item['id'] ?>" class="text-dark fw-bold text-decoration-none d-block mb-1">
                                                    <?= htmlspecialchars($item['judul']) ?>
                                                </a>
                                                <div class="small text-muted mb-1">
                                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($item['penulis']) ?>
                                                </div>
                                                <span class="badge bg-secondary-subtle text-secondary small">
                                                    Stok: <?= (int) $item['stok'] ?>
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?>
                                            </td>
                                            <td>
                                                <form action="proses_cart.php" method="POST" class="d-flex align-items-center gap-1">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="aksi" value="ubah">
                                                    <input type="hidden" name="id_buku" value="<?= (int) $item['id'] ?>">
                                                    <input
                                                        type="number"
                                                        name="jumlah"
                                                        class="form-control form-control-sm text-center"
                                                        min="1"
                                                        max="<?= (int) $item['stok'] ?>"
                                                        value="<?= (int) $item['jumlah'] ?>"
                                                        style="max-width: 65px;"
                                                    >
                                                    <button class="btn btn-outline-primary btn-sm" type="submit" title="Perbarui jumlah">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="fw-bold text-slate-900 text-nowrap">
                                                Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center">
                                                <form action="proses_cart.php" method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="aksi" value="hapus">
                                                    <input type="hidden" name="id_buku" value="<?= (int) $item['id'] ?>">
                                                    <button class="btn btn-outline-danger btn-sm p-1 px-2" type="submit" title="Hapus dari keranjang">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <form action="proses_cart.php" method="POST" onsubmit="return confirm('Kosongkan seluruh buku di keranjang?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="aksi" value="kosongkan">
                            <button class="btn btn-outline-danger btn-sm" type="submit">
                                <i class="bi bi-trash3 me-1"></i>Kosongkan Keranjang
                            </button>
                        </form>
                        <span class="small text-muted">Total: <strong><?= $jumlahCart ?></strong> buku</span>
                    </div>
                </div>

                <!-- Ringkasan Pesanan (Sidebar) -->
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <h5 class="fw-bold text-slate-900 mb-3">Ringkasan Belanja</h5>

                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Total Item</span>
                            <span class="text-dark fw-semibold"><?= $jumlahCart ?> buku</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Estimasi Ongkir</span>
                            <span class="badge bg-success-subtle text-success">Gratis</span>
                        </div>

                        <hr class="border-slate-200 my-3">

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold text-slate-900">Total Tagihan</span>
                            <span class="h4 fw-bold text-primary mb-0">
                                Rp <?= number_format($total, 0, ',', '.') ?>
                            </span>
                        </div>

                        <a href="checkout.php" class="btn btn-primary btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2 mb-3">
                            <i class="bi bi-lock-fill"></i>Lanjut ke Checkout
                        </a>

                        <div class="p-3 bg-slate-50 rounded-3 border border-slate-200 small text-muted">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-shield-check text-success fs-5"></i>
                                <span>Transaksi Aman &amp; Terenkripsi</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-qr-code-scan text-primary fs-5"></i>
                                <span>Mendukung QRIS &amp; Transfer Bank</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="modern-footer">
        <div class="container text-center small text-slate-400">
            <p class="mb-0">&copy; <?= date('Y') ?> BukuKu Store &mdash; Digital Bookstore. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
