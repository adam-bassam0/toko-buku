<?php
// ============================================
// File    : admin/pesanan.php
// Deskripsi: Monitoring pesanan & status pembayaran
// ============================================

require_once '../config/auth.php';
require_once '../config/koneksi.php';

require_admin('../login.php');

$pesan = $_GET['pesan'] ?? '';

$result = $koneksi->query(
    'SELECT p.id, p.total, p.status, p.created_at,
            u.nama AS nama_user, u.email,
            b.metode, b.status AS status_bayar, b.kode_bayar, b.bukti, b.dibayar_pada
     FROM pesanan p
     INNER JOIN users u ON u.id = p.user_id
     LEFT JOIN pembayaran b ON b.pesanan_id = p.id
     ORDER BY p.id DESC'
);

$warnaPesanan = [
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
    <title>Monitoring Pesanan — Admin BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- ===== NAVBAR ADMIN ===== -->
    <?php require __DIR__ . '/../app/views/partials/navbar_admin.php'; ?>

    <main class="container my-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-receipt me-2 text-primary"></i>Monitoring Pesanan Masuk
                </h4>
                <p class="text-muted small mb-0">Pantau transaksi checkout, verifikasi bukti bayar, dan status pengiriman.</p>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6">
                Total: <?= $result ? $result->num_rows : 0 ?> Pesanan
            </span>
        </div>

        <?php if ($pesan === 'permintaan_tidak_valid'): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Permintaan tidak valid.
            </div>
        <?php endif; ?>

        <div class="table-card shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="80" class="text-center">ID Order</th>
                            <th>Pelanggan</th>
                            <th class="text-end">Total Nilai</th>
                            <th class="text-center">Status Pesanan</th>
                            <th class="text-center">Metode</th>
                            <th class="text-center">Status Bayar</th>
                            <th class="text-center">Bukti Transfer</th>
                            <th>Waktu Pemesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold text-slate-900">#<?= (int) $row['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-slate-900"><?= htmlspecialchars($row['nama_user']) ?></div>
                                    <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($row['email']) ?></div>
                                </td>
                                <td class="text-end fw-bold text-slate-900 text-nowrap">
                                    Rp <?= number_format((float) $row['total'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $warnaPesanan[$row['status']] ?? 'bg-secondary-subtle' ?> py-1 px-2">
                                        <?= htmlspecialchars(ucfirst($row['status'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['metode'] === 'qris'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                            <i class="bi bi-qr-code me-1"></i>QRIS
                                        </span>
                                    <?php elseif ($row['metode'] === 'transfer'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-bank me-1"></i>Transfer
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (($row['status_bayar'] ?? '') === 'lunas'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Lunas</span>
                                        <?php if (!empty($row['dibayar_pada'])): ?>
                                            <div class="small text-muted" style="font-size: 0.72rem;"><?= date('d M H:i', strtotime($row['dibayar_pada'])) ?></div>
                                        <?php endif; ?>
                                    <?php elseif (($row['status_bayar'] ?? '') === 'menunggu'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Menunggu</span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>

                                    <?php if (!empty($row['kode_bayar'])): ?>
                                        <div class="small text-muted" style="font-size: 0.72rem;"><code><?= htmlspecialchars($row['kode_bayar']) ?></code></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($row['bukti'])): ?>
                                        <a href="../bukti.php?pesanan=<?= (int) $row['id'] ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm p-1 px-2" title="Lihat Bukti Transfer">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt display-5 d-block mb-2 text-secondary"></i>
                                Belum ada pesanan masuk.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- ===== FOOTER ===== -->
    <?php require __DIR__ . '/../app/views/partials/footer_admin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
