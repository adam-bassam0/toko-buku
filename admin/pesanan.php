<?php
// ============================================
// File    : admin/pesanan.php
// Deskripsi: Daftar semua pesanan + status pembayaran.
//            Hanya bisa diakses admin yang login.
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
    <title>Pesanan - Admin BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- ===== NAVBAR ADMIN (sama dengan navbar pelanggan) ===== -->
    <?php require __DIR__ . '/../partials/navbar_admin.php'; ?>

    <main class="container my-4 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Pesanan Masuk</h4>
            <span class="badge bg-secondary"><?= $result->num_rows ?> pesanan</span>
        </div>

        <?php if ($pesan === 'permintaan_tidak_valid'): ?>
            <div class="alert alert-danger">Permintaan tidak valid.</div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-admin align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="70" class="text-center">#</th>
                                <th>Pemesan</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Status Pesanan</th>
                                <th class="text-center">Metode</th>
                                <th class="text-center">Pembayaran</th>
                                <th class="text-center">Bukti</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center fw-semibold"><?= (int) $row['id'] ?></td>
                                    <td>
                                        <?= htmlspecialchars($row['nama_user']) ?>
                                        <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                                    </td>
                                    <td class="text-end">Rp <?= number_format((float) $row['total'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $warnaPesanan[$row['status']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($row['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['metode'] === 'qris'): ?>
                                            <span class="badge bg-primary"><i class="bi bi-qr-code me-1"></i>QRIS</span>
                                        <?php elseif ($row['metode'] === 'transfer'): ?>
                                            <span class="badge bg-success"><i class="bi bi-bank me-1"></i>Transfer</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (($row['status_bayar'] ?? '') === 'lunas'): ?>
                                            <span class="badge bg-success">Lunas</span>
                                            <?php if (!empty($row['dibayar_pada'])): ?>
                                                <div class="small text-muted"><?= date('d M Y H:i', strtotime($row['dibayar_pada'])) ?></div>
                                            <?php endif; ?>
                                        <?php elseif (($row['status_bayar'] ?? '') === 'menunggu'): ?>
                                            <span class="badge bg-warning text-dark">Menunggu</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                        <?php if (!empty($row['kode_bayar'])): ?>
                                            <div class="small text-muted"><code><?= htmlspecialchars($row['kode_bayar']) ?></code></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($row['bukti'])): ?>
                                            <a href="../<?= htmlspecialchars($row['bukti']) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm" title="Lihat bukti">
                                                <i class="bi bi-image"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                    Belum ada pesanan.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- ===== FOOTER ===== -->
    <?php require __DIR__ . '/../partials/footer_admin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
