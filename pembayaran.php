<?php
// ============================================
// File    : pembayaran.php
// Deskripsi: Halaman pembayaran pesanan.
//            - QRIS: menampilkan kode QR untuk di-scan
//            - Transfer: instruksi rekening + konfirmasi
//            Hanya bisa dibuka oleh pemilik pesanan.
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

// Ambil pesanan milik user + data pembayarannya.
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

// Ambil rincian item.
$stmt = $koneksi->prepare('SELECT judul, harga, jumlah, subtotal FROM detail_pesanan WHERE pesanan_id = ? ORDER BY id ASC');
$stmt->bind_param('i', $pesananId);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sudahLunas = in_array($pesanan['status'], ['dibayar', 'dikirim', 'selesai'], true)
    || ($pesanan['status_bayar'] ?? '') === 'lunas';

$metode     = $pesanan['metode'] ?? '';
$totalBayar = (float) ($pesanan['jumlah_tagihan'] ?? $pesanan['total']);

// Payload QR (simulasi). Pada produksi, ini diganti string QRIS resmi dari payment gateway.
$qrData = 'BUKUKU|PESANAN:' . $pesananId
    . '|KODE:' . ($pesanan['kode_bayar'] ?? '-')
    . '|TOTAL:' . (int) $totalBayar;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=10&data=' . rawurlencode($qrData);

// Rekening tujuan transfer (data demo).
$rekening = [
    ['bank' => 'BCA', 'nomor' => '1234567890', 'nama' => 'BukuKu Store'],
    ['bank' => 'Mandiri', 'nomor' => '9876543210', 'nama' => 'BukuKu Store'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pesanan #<?= $pesananId ?> - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main class="container my-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-credit-card me-2"></i>Pembayaran Pesanan #<?= $pesananId ?></h2>
                <p class="text-muted mb-0">
                    Dibuat <?= date('d M Y, H:i', strtotime($pesanan['created_at'])) ?>
                </p>
            </div>
            <a href="pesanan.php" class="btn btn-outline-primary"><i class="bi bi-receipt me-1"></i>Pesanan Saya</a>
        </div>

        <?php if ($sudahLunas): ?>
            <div class="card border-0 shadow-sm text-center py-5 mb-4">
                <div class="card-body">
                    <i class="bi bi-check-circle-fill display-1 text-success"></i>
                    <h4 class="mt-3">Pembayaran Lunas</h4>
                    <p class="text-muted mb-1">
                        Metode: <strong><?= $metode === 'qris' ? 'QRIS' : 'Transfer Bank' ?></strong>
                        &middot; Kode: <code><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></code>
                    </p>
                    <?php if (!empty($pesanan['dibayar_pada'])): ?>
                        <p class="text-muted small">Dibayar pada <?= date('d M Y, H:i', strtotime($pesanan['dibayar_pada'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($pesanan['bukti'])): ?>
                        <p class="small mb-0">
                            <a href="<?= htmlspecialchars($pesanan['bukti']) ?>" target="_blank" rel="noopener">
                                <i class="bi bi-image me-1"></i>Lihat bukti pembayaran
                            </a>
                        </p>
                    <?php endif; ?>
                    <a href="pesanan.php" class="btn btn-success mt-2"><i class="bi bi-receipt me-1"></i>Lihat Pesanan</a>
                </div>
            </div>
        <?php elseif ($metode === ''): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Data pembayaran untuk pesanan ini tidak ditemukan. Silakan hubungi admin.
            </div>
        <?php else: ?>

        <div class="row g-4">
            <!-- Kolom instruksi pembayaran -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="fw-bold mb-0">
                            <?php if ($metode === 'qris'): ?>
                                <i class="bi bi-qr-code me-2 text-primary"></i>Bayar dengan QRIS
                            <?php else: ?>
                                <i class="bi bi-bank me-2 text-success"></i>Transfer Bank
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <div class="text-muted small">Total yang harus dibayar</div>
                            <div class="display-6 fw-bold text-primary">Rp <?= number_format($totalBayar, 0, ',', '.') ?></div>
                            <div class="text-muted small">Kode bayar: <code><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></code></div>
                        </div>

                        <?php if ($metode === 'qris'): ?>
                            <div class="text-center">
                                <img src="<?= htmlspecialchars($qrUrl) ?>"
                                     alt="QRIS Pembayaran Pesanan <?= $pesananId ?>"
                                     class="img-fluid border rounded-3 p-2 bg-white"
                                     style="max-width:260px;"
                                     onerror="this.style.display='none';document.getElementById('qr-fallback').style.display='block';">
                                <div id="qr-fallback" class="alert alert-warning mt-3" style="display:none;">
                                    Gambar QR tidak dapat dimuat (perlu koneksi internet).
                                    Tunjukkan kode bayar <strong><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></strong> ke kasir/admin.
                                </div>
                                <p class="text-muted small mt-2 mb-0">
                                    Buka aplikasi e-wallet / mobile banking yang mendukung QRIS,
                                    lalu scan kode di atas. Nominal sudah otomatis.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr><th>Bank</th><th>Nomor Rekening</th><th>Atas Nama</th></tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($rekening as $r): ?>
                                        <tr>
                                            <td><span class="badge bg-dark"><?= htmlspecialchars($r['bank']) ?></span></td>
                                            <td><code><?= htmlspecialchars($r['nomor']) ?></code></td>
                                            <td><?= htmlspecialchars($r['nama']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="alert alert-info small">
                                <i class="bi bi-info-circle me-1"></i>
                                Transfer <strong>tepat sebesar Rp <?= number_format($totalBayar, 0, ',', '.') ?></strong>
                                dan cantumkan kode <strong><?= htmlspecialchars($pesanan['kode_bayar'] ?? '-') ?></strong>
                                pada berita/keterangan transfer.
                            </div>
                        <?php endif; ?>

                        <hr>

                        <h6 class="fw-bold">Konfirmasi Pembayaran</h6>
                        <p class="small text-muted">
                            Setelah membayar, tekan tombol konfirmasi. Untuk transfer,
                            unggah bukti transfer (opsional, maks. 2 MB, JPG/PNG/WebP).
                        </p>

                        <form action="proses/proses_bayar.php" method="POST" enctype="multipart/form-data"
                              onsubmit="return confirm('Konfirmasi bahwa pembayaran sudah dilakukan?');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="pesanan" value="<?= $pesananId ?>">

                            <div class="mb-3">
                                <label for="bukti" class="form-label fw-semibold">Bukti Pembayaran (opsional)</label>
                                <input type="file" name="bukti" id="bukti" class="form-control" accept="image/jpeg,image/png,image/webp">
                            </div>

                            <button type="submit" class="btn btn-success fw-bold w-100">
                                <i class="bi bi-check2-circle me-1"></i>Saya Sudah Membayar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Kolom ringkasan pesanan -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Rincian Pesanan</h5>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-3">
                                <thead><tr><th>Buku</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
                                <tbody>
                                <?php foreach ($detail as $d): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($d['judul']) ?></td>
                                        <td class="text-center"><?= (int) $d['jumlah'] ?></td>
                                        <td class="text-end">Rp <?= number_format((float) $d['subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Total</th>
                                        <th class="text-end text-primary">Rp <?= number_format((float) $pesanan['total'], 0, ',', '.') ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="small text-muted">
                            <div><i class="bi bi-person me-1"></i>Pemesan: <?= htmlspecialchars($user['nama']) ?></div>
                            <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?></div>
                            <div class="mt-1"><i class="bi bi-shield-check me-1"></i>Status pesanan: <?= htmlspecialchars($pesanan['status']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-secondary small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Ini adalah simulasi pembayaran untuk keperluan pembelajaran.
                    Tidak ada uang yang benar-benar ditransfer.
                </div>
            </div>
        </div>

        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
