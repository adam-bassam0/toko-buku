<?php
// ============================================
// File    : pembayaran.php
// Deskripsi: Halaman pembayaran pesanan
// ============================================

require_once 'config/auth.php';
require_once 'config/koneksi.php';

require_login('login.php');

$user      = auth_user();
$pesananId = (int) ($_GET['pesanan'] ?? 0);

if ($pesananId <= 0) {
    flash('warning', 'Pesanan tidak ditemukan.');
    header('Location: pesanan.php');
    exit;
}

$stmt = $koneksi->prepare(
    'SELECT p.id, p.total, p.status, p.created_at,
            b.metode, b.status AS status_bayar, b.kode_bayar, b.bukti, b.dibayar_pada, b.jumlah AS jumlah_tagihan
     FROM pesanan p
     LEFT JOIN pembayaran b ON b.pesanan_id = p.id
     WHERE p.id = ? AND p.user_id = ?
     LIMIT 1'
);
$stmt->bind_param('ii', $pesananId, $user['id']);
$stmt->execute();
$pesanan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pesanan) {
    flash('danger', 'Pesanan tidak ditemukan atau bukan milik akun Anda.');
    header('Location: pesanan.php');
    exit;
}

$stmt = $koneksi->prepare('SELECT judul, harga, jumlah, subtotal FROM detail_pesanan WHERE pesanan_id = ? ORDER BY id ASC');
$stmt->bind_param('i', $pesananId);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sudahLunas = in_array($pesanan['status'], ['dibayar', 'dikirim', 'selesai'], true)
    || ($pesanan['status_bayar'] ?? '') === 'lunas';

$metode     = $pesanan['metode'] ?? '';
$totalBayar = (float) ($pesanan['jumlah_tagihan'] ?? $pesanan['total']);

$qrData = 'BUKUKU|PESANAN:' . $pesananId
    . '|KODE:' . ($pesanan['kode_bayar'] ?? '-')
    . '|TOTAL:' . (int) $totalBayar;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=12&data=' . rawurlencode($qrData);

$rekening = [
    ['bank' => 'BCA', 'nomor' => '1234567890', 'nama' => 'BukuKu Store', 'color' => '#005bac'],
    ['bank' => 'Mandiri', 'nomor' => '9876543210', 'nama' => 'BukuKu Store', 'color' => '#003366'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan #<?= $pesananId ?> — BukuKu</title>
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

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-credit-card-2-front me-2 text-primary"></i>Pembayaran Pesanan #<?= $pesananId ?>
                </h2>
                <p class="text-muted mb-0">
                    Dibuat pada <?= date('d M Y, H:i', strtotime($pesanan['created_at'])) ?>
                </p>
            </div>
            <a href="pesanan.php" class="btn btn-outline-secondary">
                <i class="bi bi-receipt me-1"></i>Lihat Semua Pesanan
            </a>
        </div>

        <?php if ($sudahLunas): ?>
            <!-- Lunas State -->
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 my-4 bg-white">
                <div class="card-body">
                    <div class="d-inline-flex p-4 rounded-circle bg-success bg-opacity-10 text-success mb-3">
                        <i class="bi bi-check-circle-fill display-3"></i>
                    </div>
                    <h3 class="fw-bold text-slate-900 mb-2">Pembayaran Berhasil!</h3>
                    <p class="text-muted mb-3 mx-auto" style="max-width: 480px;">
                        Terima kasih, pesanan Anda telah lunas dan sedang disiapkan untuk pengiriman.
                    </p>
                    <div class="p-3 bg-slate-50 border rounded-3 d-inline-block text-start mb-4">
                        <div class="small text-muted">Metode: <strong><?= $metode === 'qris' ? 'QRIS' : 'Transfer Bank' ?></strong></div>
                        <div class="small text-muted">Kode Pembayaran: <code><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></code></div>
                        <?php if (!empty($pesanan['dibayar_pada'])): ?>
                            <div class="small text-muted">Waktu Bayar: <?= date('d M Y, H:i', strtotime($pesanan['dibayar_pada'])) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="pesanan.php" class="btn btn-primary px-4 py-2 fw-bold">
                            <i class="bi bi-receipt me-1"></i>Ke Riwayat Pesanan
                        </a>
                        <?php if (!empty($pesanan['bukti'])): ?>
                            <a href="bukti.php?pesanan=<?= $pesananId ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bi bi-image me-1"></i>Lihat Bukti Bayar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($metode === ''): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Data pembayaran untuk pesanan ini tidak ditemukan. Silakan hubungi admin toko.
            </div>
        <?php else: ?>

            <div class="row g-4">
                <!-- Kolom Instruksi Pembayaran -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="p-4 bg-slate-50 border-bottom border-slate-200 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-slate-900">
                                <?php if ($metode === 'qris'): ?>
                                    <i class="bi bi-qr-code text-primary me-2"></i>Instruksi Bayar QRIS
                                <?php else: ?>
                                    <i class="bi bi-bank text-success me-2"></i>Instruksi Transfer Bank
                                <?php endif; ?>
                            </h5>
                            <span class="badge bg-warning-subtle text-warning">Menunggu Bayar</span>
                        </div>

                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4 pb-3 border-bottom border-slate-200">
                                <div class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Total Tagihan</div>
                                <div class="display-6 fw-bold text-primary my-1">
                                    Rp <?= number_format($totalBayar, 0, ',', '.') ?>
                                </div>
                                <div class="small text-muted">
                                    Kode Bayar: <code class="fw-bold fs-6"><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></code>
                                </div>
                            </div>

                            <?php if ($metode === 'qris'): ?>
                                <div class="text-center mb-4">
                                    <div class="d-inline-block p-3 bg-white rounded-4 border shadow-sm mb-3">
                                        <img
                                            src="<?= htmlspecialchars($qrUrl) ?>"
                                            alt="Kode QRIS"
                                            class="img-fluid"
                                            style="max-width: 240px;"
                                            onerror="this.style.display='none';document.getElementById('qr-fallback').style.display='block';"
                                        >
                                    </div>
                                    <div id="qr-fallback" class="alert alert-warning" style="display: none;">
                                        Kode QR tidak dapat dimuat otomatis. Sebutkan kode bayar <strong><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></strong> ke admin.
                                    </div>
                                    <p class="small text-muted mx-auto" style="max-width: 380px;">
                                        Scan kode QR di atas menggunakan aplikasi e-wallet atau mobile banking favorit Anda (BCA, GoPay, OVO, Dana, dll.).
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-slate-900 mb-2">Rekening Tujuan Transfer:</label>
                                    <div class="row g-3">
                                        <?php foreach ($rekening as $r): ?>
                                            <div class="col-sm-6">
                                                <div class="p-3 bg-slate-50 border rounded-3 h-100">
                                                    <span class="badge bg-dark mb-2"><?= htmlspecialchars($r['bank']) ?></span>
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <code class="fs-6 fw-bold text-slate-900"><?= htmlspecialchars($r['nomor']) ?></code>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-secondary py-0 px-2"
                                                            onclick="copyText('<?= htmlspecialchars($r['nomor']) ?>', this)"
                                                            title="Salin nomor"
                                                        >
                                                            <i class="bi bi-copy"></i>
                                                        </button>
                                                    </div>
                                                    <div class="small text-muted">a.n. <?= htmlspecialchars($r['nama']) ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="alert alert-info border-0 shadow-xs rounded-3 small mt-3 mb-0">
                                        <i class="bi bi-info-circle-fill me-1"></i>
                                        Pastikan transfer <strong>tepat sebesar Rp <?= number_format($totalBayar, 0, ',', '.') ?></strong> dan sertakan kode <strong><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></strong> pada berita transfer.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Formulir Konfirmasi -->
                            <div class="p-4 bg-slate-50 rounded-4 border border-slate-200">
                                <h6 class="fw-bold text-slate-900 mb-1">Konfirmasi Pembayaran</h6>
                                <p class="small text-muted mb-3">
                                    Setelah menyelesaikan pembayaran, silakan tekan tombol di bawah ini.
                                </p>

                                <form action="proses/proses_bayar.php" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Konfirmasi bahwa pembayaran sudah dilakukan?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="pesanan" value="<?= $pesananId ?>">

                                    <?php if ($metode === 'transfer'): ?>
                                        <div class="mb-3">
                                            <label for="bukti" class="form-label small fw-semibold">Unggah Bukti Transfer (Opsional)</label>
                                            <input type="file" name="bukti" id="bukti" class="form-control" accept="image/jpeg,image/png,image/webp">
                                            <div class="form-text small">Maksimal 2 MB (format JPG, PNG, atau WebP).</div>
                                        </div>
                                    <?php endif; ?>

                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-check2-circle fs-5"></i>Saya Sudah Membayar
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Kolom Rincian Pesanan -->
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <h5 class="fw-bold text-slate-900 mb-3">Rincian Buku</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-3">
                                <thead>
                                    <tr>
                                        <th>Buku</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detail as $d): ?>
                                        <tr>
                                            <td class="small fw-semibold"><?= htmlspecialchars($d['judul']) ?></td>
                                            <td class="text-center small"><?= (int) $d['jumlah'] ?></td>
                                            <td class="text-end small text-nowrap">Rp <?= number_format((float) $d['subtotal'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end pt-3">Total Tagihan</th>
                                        <th class="text-end text-primary pt-3 fw-bold">Rp <?= number_format((float) $pesanan['total'], 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-3 border border-slate-200 small text-muted">
                            <div class="mb-1"><i class="bi bi-person me-1"></i>Pemesan: <strong><?= htmlspecialchars($user['nama']) ?></strong></div>
                            <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?></div>
                        </div>

                        <div class="alert alert-secondary small mt-3 mb-0 border-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Ini adalah sistem simulasi pembayaran untuk keperluan pembelajaran.
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
    <script>
        function copyText(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check text-success"></i>';
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        }
    </script>
</body>
</html>
