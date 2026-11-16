<?php
// ============================================
// File    : checkout.php
// Deskripsi: Halaman konfirmasi pesanan.
// ============================================

require_once 'config/auth.php';
require_once 'config/koneksi.php';

require_login('login.php');

$items = [];
$total = 0;
foreach ($_SESSION['cart'] ?? [] as $id => $jumlah) {
    $id     = (int) $id;
    $jumlah = (int) $jumlah;
    if ($id <= 0 || $jumlah <= 0) {
        continue;
    }

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
    $buku['jumlah']   = $jumlah;
    $buku['subtotal'] = $subtotal;
    $items[] = $buku;
    $total  += $subtotal;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Checkout — BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- ===== NAVBAR ===== -->
    <?php require __DIR__ . '/app/views/partials/navbar.php'; ?>

    <main class="container my-5 flex-grow-1">

        <!-- Checkout Header & Step Indicator -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-bag-check-fill me-2 text-primary"></i>Konfirmasi Pesanan
                </h2>
                <p class="text-muted mb-0">Periksa kembali daftar buku dan pilih metode pembayaran Anda.</p>
            </div>
            <a href="cart.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Keranjang
            </a>
        </div>

        <!-- Step Indicator -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 col-lg-6">
                <div class="d-flex justify-content-between align-items-center position-relative">
                    <div class="text-center" style="z-index: 2;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white fw-bold mb-1" style="width: 36px; height: 36px;">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="small fw-semibold text-dark">Keranjang</div>
                    </div>
                    <div class="text-center" style="z-index: 2;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold mb-1" style="width: 36px; height: 36px; box-shadow: 0 0 0 4px var(--primary-light);">
                            2
                        </div>
                        <div class="small fw-bold text-primary">Checkout</div>
                    </div>
                    <div class="text-center" style="z-index: 2;">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light border text-muted fw-bold mb-1" style="width: 36px; height: 36px;">
                            3
                        </div>
                        <div class="small text-muted">Pembayaran</div>
                    </div>
                    <div class="position-absolute w-100 top-50 start-0 translate-middle-y" style="height: 2px; background: #e2e8f0; z-index: 1;">
                        <div style="width: 50%; height: 100%; background: var(--primary);"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$items): ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                <div class="card-body">
                    <div class="d-inline-flex p-4 rounded-circle bg-light text-muted mb-3">
                        <i class="bi bi-cart-x display-3 text-secondary"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Keranjang Anda Kosong</h4>
                    <p class="text-muted mb-4">Tambahkan buku terlebih dahulu sebelum melakukan proses checkout.</p>
                    <a href="index.php" class="btn btn-primary px-4">
                        <i class="bi bi-compass me-1"></i>Jelajahi Buku
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <!-- Rincian Buku -->
                <div class="col-lg-8">
                    <div class="table-card shadow-sm mb-4">
                        <div class="p-3 bg-slate-50 border-bottom border-slate-200 fw-bold text-slate-800">
                            <i class="bi bi-journal-check me-2 text-primary"></i>Daftar Buku Dipesan
                        </div>
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th colspan="2">Buku</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td style="width: 70px;">
                                                <img
                                                    src="<?= htmlspecialchars($item['cover'] ?: 'https://via.placeholder.com/60x80?text=No+Cover') ?>"
                                                    alt="Cover"
                                                    class="cart-cover"
                                                    onerror="this.src='https://via.placeholder.com/60x80?text=No+Cover'"
                                                >
                                            </td>
                                            <td>
                                                <div class="fw-bold text-slate-900"><?= htmlspecialchars($item['judul']) ?></div>
                                                <div class="small text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($item['penulis']) ?></div>
                                            </td>
                                            <td class="text-end text-nowrap">
                                                Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center fw-semibold">
                                                <?= (int) $item['jumlah'] ?>
                                            </td>
                                            <td class="text-end fw-bold text-slate-900 text-nowrap">
                                                Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Formulir Checkout & Metode Pembayaran -->
                <div class="col-lg-4">
                    <div class="cart-summary-card">
                        <h5 class="fw-bold text-slate-900 mb-3">Ringkasan Pesanan</h5>

                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Nama Pemesan</span>
                            <span class="text-slate-900 fw-semibold"><?= htmlspecialchars(auth_user()['nama']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Email Pemesan</span>
                            <span class="text-slate-900 fw-semibold"><?= htmlspecialchars(auth_user()['email']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>Total Item</span>
                            <span class="text-slate-900 fw-semibold"><?= array_sum($_SESSION['cart'] ?? []) ?> buku</span>
                        </div>

                        <hr class="border-slate-200 my-3">

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold text-slate-900">Total Pembayaran</span>
                            <span class="h4 fw-bold text-primary mb-0">
                                Rp <?= number_format($total, 0, ',', '.') ?>
                            </span>
                        </div>

                        <form action="proses/proses_checkout.php" method="POST" onsubmit="return confirm('Proses pesanan Anda sekarang? Stok buku akan langsung dikurangi.');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-900 mb-2">Pilih Metode Pembayaran</label>

                                <!-- Radio QRIS -->
                                <div class="form-check p-0 mb-2">
                                    <input class="d-none payment-method-input" type="radio" name="metode" id="metode-qris" value="qris" checked>
                                    <label class="payment-method-card" for="metode-qris">
                                        <div class="d-flex align-items-center justify-content-center bg-white rounded-3 p-2 border" style="width: 48px; height: 48px;">
                                            <i class="bi bi-qr-code text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-900">QRIS Instan</div>
                                            <div class="small text-muted">Scan via GoPay, OVO, Dana, BCA, dll.</div>
                                        </div>
                                    </label>
                                </div>

                                <!-- Radio Transfer Bank -->
                                <div class="form-check p-0 mb-3">
                                    <input class="d-none payment-method-input" type="radio" name="metode" id="metode-transfer" value="transfer">
                                    <label class="payment-method-card" for="metode-transfer">
                                        <div class="d-flex align-items-center justify-content-center bg-white rounded-3 p-2 border" style="width: 48px; height: 48px;">
                                            <i class="bi bi-bank text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-slate-900">Transfer Bank</div>
                                            <div class="small text-muted">Rekening BCA / Mandiri BukuKu</div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2 mb-3">
                                <i class="bi bi-shield-check"></i>Buat Pesanan Sekarang
                            </button>
                        </form>

                        <div class="small text-muted p-3 bg-slate-50 rounded-3 border border-slate-200">
                            <i class="bi bi-database-check text-success me-1"></i>
                            Transaksi diproteksi oleh ACID database transactions untuk menjamin integritas stok dan pemesanan.
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
</body>
</html>
