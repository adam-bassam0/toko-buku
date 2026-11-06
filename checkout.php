<?php
// ============================================
// File    : checkout.php
// Deskripsi: Halaman konfirmasi pesanan.
//            Menampilkan ulang isi keranjang dari database
//            sebelum membuat pesanan (transaksi ACID).
// ============================================

require_once 'config/auth.php';
require_once 'config/koneksi.php';

// Checkout wajib login (agar pesanan punya pemilik).
require_login('login.php');

// Hitung ulang item dari database — jangan percaya data di browser.
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
    <title>Checkout - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-bag-check me-2"></i>Konfirmasi Pesanan</h2>
                <p class="text-muted mb-0">Periksa kembali pesanan sebelum dibuat.</p>
            </div>
            <a href="cart.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Kembali ke Keranjang</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <?php if (!$items): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-cart-x display-1 text-muted"></i>
                                <h4 class="mt-3">Keranjang kosong</h4>
                                <p class="text-muted">Tambahkan buku terlebih dahulu sebelum checkout.</p>
                                <a href="index.php" class="btn btn-primary">Jelajahi Buku</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
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
                                            <td style="width:70px">
                                                <img src="<?= htmlspecialchars($item['cover'] ?: 'https://via.placeholder.com/60x80?text=No+Cover') ?>"
                                                     alt="Cover" class="rounded" style="width:50px;height:70px;object-fit:cover;"
                                                     onerror="this.src='https://via.placeholder.com/60x80?text=No+Cover'">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['judul']) ?></strong>
                                                <div class="small text-muted"><?= htmlspecialchars($item['penulis']) ?></div>
                                            </td>
                                            <td class="text-end">Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?></td>
                                            <td class="text-center"><?= (int) $item['jumlah'] ?></td>
                                            <td class="text-end fw-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Ringkasan</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pemesan</span>
                            <span><?= htmlspecialchars(auth_user()['nama']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total item</span>
                            <span><?= array_sum($_SESSION['cart'] ?? []) ?> buku</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Total bayar</strong>
                            <strong class="text-primary fs-5">Rp <?= number_format($total, 0, ',', '.') ?></strong>
                        </div>

                        <?php if ($items): ?>
                            <form action="proses/proses_checkout.php" method="POST"
                                  onsubmit="return confirm('Buat pesanan sekarang? Stok buku akan dikurangi.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                                <hr>
                                <h6 class="fw-bold mb-2">Metode Pembayaran</h6>

                                <div class="form-check border rounded-3 p-3 mb-2">
                                    <input class="form-check-input" type="radio" name="metode" id="metode-qris" value="qris" checked>
                                    <label class="form-check-label w-100" for="metode-qris">
                                        <i class="bi bi-qr-code me-1 text-primary"></i><strong>QRIS</strong>
                                        <span class="d-block small text-muted">Scan kode QR dengan aplikasi e-wallet / mobile banking.</span>
                                    </label>
                                </div>

                                <div class="form-check border rounded-3 p-3 mb-3">
                                    <input class="form-check-input" type="radio" name="metode" id="metode-transfer" value="transfer">
                                    <label class="form-check-label w-100" for="metode-transfer">
                                        <i class="bi bi-bank me-1 text-success"></i><strong>Transfer Bank</strong>
                                        <span class="d-block small text-muted">Transfer ke rekening BCA / Mandiri, lalu konfirmasi.</span>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-bold">
                                    <i class="bi bi-check2-circle me-1"></i>Buat Pesanan & Lanjut Bayar
                                </button>
                            </form>
                        <?php endif; ?>

                        <p class="small text-muted mt-3 mb-0">
                            <i class="bi bi-shield-check me-1"></i>
                            Pesanan disimpan dalam satu transaksi database (ACID):
                            jika satu item gagal, semua dibatalkan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
