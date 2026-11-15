<?php
// ============================================
// File    : pesanan.php
// Deskripsi: Riwayat pesanan milik user yang login
// ============================================

require_once 'config/auth.php';
require_once 'config/koneksi.php';

require_login('login.php');

$user = auth_user();

$stmt = $koneksi->prepare(
    'SELECT p.id, p.total, p.status, p.created_at,
            b.metode, b.status AS status_bayar, b.kode_bayar
     FROM pesanan p
     LEFT JOIN pembayaran b ON b.pesanan_id = p.id
     WHERE p.user_id = ?
     ORDER BY p.id DESC'
);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$pesananResult = $stmt->get_result();

$daftarPesanan = [];
while ($row = $pesananResult->fetch_assoc()) {
    $row['items'] = [];
    $daftarPesanan[$row['id']] = $row;
}
$stmt->close();

if ($daftarPesanan) {
    $stmt = $koneksi->prepare(
        'SELECT d.pesanan_id, d.buku_id, d.judul, d.harga, d.jumlah, d.subtotal
         FROM detail_pesanan d
         INNER JOIN pesanan p ON p.id = d.pesanan_id
         WHERE p.user_id = ?
         ORDER BY d.id ASC'
    );
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $detailResult = $stmt->get_result();

    while ($d = $detailResult->fetch_assoc()) {
        $daftarPesanan[$d['pesanan_id']]['items'][] = $d;
    }
    $stmt->close();
}

$warnaStatus = [
    'pending' => 'bg-warning-subtle text-warning border border-warning-subtle',
    'dibayar' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
    'dikirim' => 'bg-primary-subtle text-primary border border-primary-subtle',
    'selesai' => 'bg-success-subtle text-success border border-success-subtle',
    'batal'   => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya — BukuKu</title>
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
                    <i class="bi bi-receipt me-2 text-primary"></i>Riwayat Pesanan Saya
                </h2>
                <p class="text-muted mb-0">Lihat status transaksi dan rincian pesanan buku Anda.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-compass me-1"></i>Jelajahi Buku Lain
            </a>
        </div>

        <?php if (!$daftarPesanan): ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 bg-white">
                <div class="card-body">
                    <div class="d-inline-flex p-4 rounded-circle bg-light text-muted mb-3">
                        <i class="bi bi-receipt-cutoff display-3 text-secondary"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Belum Ada Riwayat Pesanan</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 450px;">
                        Anda belum pernah membuat pesanan buku di BukuKu. Yuk, temukan bacaan seru sekarang!
                    </p>
                    <a href="index.php" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-compass me-1"></i>Mulai Berbelanja
                    </a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($daftarPesanan as $pesanan): ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                    <div class="p-4 bg-slate-50 border-bottom border-slate-200 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-bold text-slate-900 fs-5">Pesanan #<?= (int) $pesanan['id'] ?></span>
                            <span class="text-muted small">&bull; <?= date('d M Y, H:i', strtotime($pesanan['created_at'])) ?></span>
                            <?php if (!empty($pesanan['metode'])): ?>
                                <span class="badge bg-white border text-dark ms-1">
                                    <i class="bi <?= $pesanan['metode'] === 'qris' ? 'bi-qr-code' : 'bi-bank' ?> me-1 text-primary"></i>
                                    <?= $pesanan['metode'] === 'qris' ? 'QRIS' : 'Transfer Bank' ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge <?= $warnaStatus[$pesanan['status']] ?? 'bg-secondary-subtle' ?> py-2 px-3">
                                Status: <?= ucfirst(htmlspecialchars($pesanan['status'])) ?>
                            </span>

                            <?php if (($pesanan['status_bayar'] ?? '') === 'lunas'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3">
                                    <i class="bi bi-check-circle me-1"></i>Lunas
                                </span>
                                <a href="pembayaran.php?pesanan=<?= (int) $pesanan['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-file-earmark-text me-1"></i>Lihat Bukti
                                </a>
                            <?php elseif (!empty($pesanan['metode'])): ?>
                                <a href="pembayaran.php?pesanan=<?= (int) $pesanan['id'] ?>" class="btn btn-warning btn-sm fw-bold">
                                    <i class="bi bi-credit-card me-1"></i>Bayar Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Judul Buku</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pesanan['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-slate-900"><?= htmlspecialchars($item['judul']) ?></div>
                                            </td>
                                            <td class="text-end text-muted">
                                                Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center fw-semibold">
                                                <?= (int) $item['jumlah'] ?>
                                            </td>
                                            <td class="text-end fw-bold text-slate-900">
                                                Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-50">
                                        <th colspan="3" class="text-end text-muted small text-uppercase">Total Tagihan</th>
                                        <th class="text-end text-primary fw-bold fs-5">
                                            Rp <?= number_format((float) $pesanan['total'], 0, ',', '.') ?>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
