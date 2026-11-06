<?php
// ============================================
// File    : pesanan.php
// Deskripsi: Riwayat pesanan milik user yang login.
//            Hanya menampilkan pesanan miliknya sendiri
//            (WHERE user_id = ?), sehingga tidak bisa
//            melihat pesanan orang lain.
// ============================================

require_once 'config/auth.php';
require_once 'config/koneksi.php';

require_login('login.php');

$user = auth_user();

// Ambil header pesanan + status pembayaran milik user ini.
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

// Ambil semua rincian sekaligus, lalu kelompokkan per pesanan.
if ($daftarPesanan) {
    $stmt = $koneksi->prepare(
        'SELECT d.pesanan_id, d.judul, d.harga, d.jumlah, d.subtotal
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

// Warna badge untuk tiap status.
$warnaStatus = [
    'pending' => 'warning text-dark',
    'dibayar' => 'info text-dark',
    'dikirim' => 'primary',
    'selesai' => 'success',
    'batal'   => 'secondary',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i>Pesanan Saya</h2>
                <p class="text-muted mb-0">Riwayat pesanan untuk akun <?= htmlspecialchars($user['email']) ?>.</p>
            </div>
            <a href="index.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left me-1"></i>Lanjut Belanja</a>
        </div>

        <?php if (!$daftarPesanan): ?>
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="bi bi-receipt-cutoff display-1 text-muted"></i>
                    <h4 class="mt-3">Belum ada pesanan</h4>
                    <p class="text-muted">Pesanan yang kamu buat akan muncul di sini.</p>
                    <a href="index.php" class="btn btn-primary">Jelajahi Buku</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($daftarPesanan as $pesanan): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong>Pesanan #<?= (int) $pesanan['id'] ?></strong>
                            <span class="text-muted small ms-2">
                                <?= date('d M Y, H:i', strtotime($pesanan['created_at'])) ?>
                            </span>
                            <?php if (!empty($pesanan['metode'])): ?>
                                <span class="badge bg-light text-dark border ms-1">
                                    <i class="bi <?= $pesanan['metode'] === 'qris' ? 'bi-qr-code' : 'bi-bank' ?> me-1"></i>
                                    <?= $pesanan['metode'] === 'qris' ? 'QRIS' : 'Transfer' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-<?= $warnaStatus[$pesanan['status']] ?? 'secondary' ?>">
                                <?= htmlspecialchars(ucfirst($pesanan['status'])) ?>
                            </span>

                            <?php if (($pesanan['status_bayar'] ?? '') === 'lunas'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                                <a href="pembayaran.php?pesanan=<?= (int) $pesanan['id'] ?>" class="btn btn-outline-success btn-sm">
                                    Lihat Pembayaran
                                </a>
                            <?php elseif (!empty($pesanan['metode'])): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu Pembayaran</span>
                                <a href="pembayaran.php?pesanan=<?= (int) $pesanan['id'] ?>" class="btn btn-warning btn-sm fw-bold">
                                    <i class="bi bi-credit-card me-1"></i>Bayar Sekarang
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Buku</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pesanan['items'] as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['judul']) ?></td>
                                        <td class="text-end">Rp <?= number_format((float) $item['harga'], 0, ',', '.') ?></td>
                                        <td class="text-center"><?= (int) $item['jumlah'] ?></td>
                                        <td class="text-end">Rp <?= number_format((float) $item['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end text-primary">Rp <?= number_format((float) $pesanan['total'], 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
