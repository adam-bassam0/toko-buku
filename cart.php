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
    <title>Keranjang Belanja - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php require __DIR__ . '/partials/navbar.php'; ?>

<main class="container my-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja</h2>
            <p class="text-muted mb-0"><?= $jumlahCart ?> buku dalam keranjang</p>
        </div>
        <a href="index.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Lanjut Belanja</a>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= htmlspecialchars($pesan['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($pesan['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>

    <?php if (!$items): ?>
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-cart-x display-1 text-muted"></i>
                <h4 class="mt-3">Keranjang masih kosong</h4>
                <p class="text-muted">Yuk, temukan buku yang ingin kamu baca.</p>
                <a href="index.php" class="btn btn-primary">Jelajahi Buku</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 cart-table">
                            <thead><tr><th colspan="2">Buku</th><th>Harga</th><th style="min-width:150px">Jumlah</th><th>Subtotal</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="width:75px"><img class="cart-cover" src="<?= htmlspecialchars($item['cover'] ?: 'https://via.placeholder.com/70x90?text=No+Cover') ?>" alt="Cover buku" onerror="this.src='https://via.placeholder.com/70x90?text=No+Cover'"></td>
                                    <td><strong><?= htmlspecialchars($item['judul']) ?></strong><div class="small text-muted"><?= htmlspecialchars($item['penulis']) ?></div><div class="small text-muted">Stok: <?= (int) $item['stok'] ?></div></td>
                                    <td>Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?></td>
                                    <td>
                                        <form action="proses_cart.php" method="POST" class="d-flex gap-1">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="ubah">
                                            <input type="hidden" name="id_buku" value="<?= (int) $item['id'] ?>">
                                            <input type="number" name="jumlah" class="form-control form-control-sm" min="1" max="<?= (int) $item['stok'] ?>" value="<?= (int) $item['jumlah'] ?>" aria-label="Jumlah <?= htmlspecialchars($item['judul']) ?>">
                                            <button class="btn btn-outline-primary btn-sm" type="submit" title="Perbarui jumlah"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    </td>
                                    <td class="fw-semibold">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                    <td>
                                        <form action="proses_cart.php" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_buku" value="<?= (int) $item['id'] ?>">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" title="Hapus buku"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <form action="proses_cart.php" method="POST" class="mt-3" onsubmit="return confirm('Kosongkan seluruh keranjang?')">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="aksi" value="kosongkan">
                    <button class="btn btn-outline-danger btn-sm" type="submit"><i class="bi bi-trash me-1"></i>Kosongkan Keranjang</button>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Ringkasan Belanja</h5>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total item</span><span><?= $jumlahCart ?> buku</span></div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center"><strong>Total</strong><strong class="text-primary fs-5">Rp <?= number_format($total, 0, ',', '.') ?></strong></div>

                        <a href="checkout.php" class="btn btn-success w-100 fw-bold mt-3">
                            <i class="bi bi-bag-check me-1"></i>Checkout
                        </a>

                        <p class="small text-muted mt-3 mb-0">
                            Keranjang disimpan sementara selama sesi browser berlangsung.
                            Checkout memerlukan akun (login) agar pesanan tercatat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
<footer class="text-center mt-auto"><div class="container"><p class="mb-0">&copy; <?= date('Y') ?> BukuKu &mdash; Tugas Sistem Basis Data</p></div></footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
