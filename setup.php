<?php
// ============================================
// File    : setup.php
// Deskripsi: Database Setup Assistant & Diagnostics
//            Alat pemeriksa status database dan
//            inisialisasi data baru dalam 1 klik.
// ============================================

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_toko_buku');
define('DB_PORT', 3306);

mysqli_report(MYSQLI_REPORT_OFF);

$pesanAksi = '';
$tipeAksi  = '';

// Tangani aksi inisialisasi ulang jika diminta pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'inisialisasi_ulang') {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
    if (!$conn->connect_error) {
        // Hapus dan buat database baru
        $conn->query("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
        $conn->query("CREATE DATABASE `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db(DB_NAME);

        // Impor schema.sql
        $schema = file_get_contents(__DIR__ . '/database/schema.sql');
        if ($conn->multi_query($schema)) {
            do {
                if ($res = $conn->store_result()) {
                    $res->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            $pesanAksi = 'Database ' . DB_NAME . ' berhasil dibuat baru dan diisi seluruh 57 buku, 6 kategori, dan akun demo!';
            $tipeAksi  = 'success';
        } else {
            $pesanAksi = 'Gagal mengimpor skema database: ' . $conn->error;
            $tipeAksi  = 'danger';
        }
        $conn->close();
    } else {
        $pesanAksi = 'Gagal terhubung ke MySQL: ' . $conn->connect_error;
        $tipeAksi  = 'danger';
    }
}

// Cek status koneksi saat ini
$koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
$statusDb = false;
$statusServer = false;
$tabelStatus = [];

$testServer = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);
if (!$testServer->connect_error) {
    $statusServer = true;
    $testServer->close();
}

if (!$koneksi->connect_error) {
    $statusDb = true;
    $koneksi->set_charset("utf8mb4");

    $listTabel = ['kategori', 'buku', 'users', 'pesanan', 'detail_pesanan', 'pembayaran'];
    foreach ($listTabel as $tbl) {
        $cek = $koneksi->query("SHOW TABLES LIKE '{$tbl}'");
        if ($cek && $cek->num_rows > 0) {
            $cnt = $koneksi->query("SELECT COUNT(*) AS total FROM `{$tbl}`");
            $totalBaris = $cnt ? (int) $cnt->fetch_assoc()['total'] : 0;
            $tabelStatus[$tbl] = ['ada' => true, 'total' => $totalBaris];
        } else {
            $tabelStatus[$tbl] = ['ada' => false, 'total' => 0];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Assistant — BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-slate-50 d-flex flex-column min-vh-100">

    <header class="py-3 px-4 border-bottom bg-white shadow-xs">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-dark" href="index.php">
                <span class="brand-icon-box">
                    <i class="bi bi-book-half"></i>
                </span>
                <span class="fw-bold text-slate-900">BukuKu</span>
            </a>
            <a href="index.php" class="btn btn-primary btn-sm">
                <i class="bi bi-shop me-1"></i>Buka Toko Buku
            </a>
        </div>
    </header>

    <main class="container my-5 flex-grow-1">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h3 class="fw-bold text-slate-900 mb-1">
                            <i class="bi bi-database-check text-primary me-2"></i>Database Assistant &amp; Diagnostics
                        </h3>
                        <p class="text-muted small mb-0">Status koneksi dan data tabel untuk website toko buku digital BukuKu.</p>
                    </div>
                    <?php if ($statusDb): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                            <i class="bi bi-check-circle-fill me-1"></i>Database Terhubung &amp; Siap
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fs-6">
                            <i class="bi bi-x-circle-fill me-1"></i>Belum Terhubung
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($pesanAksi): ?>
                    <div class="alert alert-<?= $tipeAksi ?> border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                        <i class="bi <?= $tipeAksi === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2 fs-5"></i>
                        <div><?= htmlspecialchars($pesanAksi) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Server Info Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card">
                            <div class="small text-muted mb-1">MySQL Server</div>
                            <div class="fw-bold <?= $statusServer ? 'text-success' : 'text-danger' ?>">
                                <i class="bi <?= $statusServer ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?> me-1"></i>
                                <?= $statusServer ? 'Online (Aktif)' : 'Offline' ?>
                            </div>
                            <div class="small text-muted mt-1 font-monospace"><?= DB_HOST ?>:<?= DB_PORT ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card">
                            <div class="small text-muted mb-1">Nama Database</div>
                            <div class="fw-bold <?= $statusDb ? 'text-success' : 'text-warning' ?>">
                                <i class="bi bi-database me-1"></i>
                                <?= DB_NAME ?>
                            </div>
                            <div class="small text-muted mt-1"><?= $statusDb ? 'Tersedia & Terpilih' : 'Belum Dibuat' ?></div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card">
                            <div class="small text-muted mb-1">Pengguna / User</div>
                            <div class="fw-bold text-slate-800">
                                <i class="bi bi-person-fill me-1"></i>
                                <?= DB_USER ?>
                            </div>
                            <div class="small text-muted mt-1">Default XAMPP</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="stat-card">
                            <div class="small text-muted mb-1">Total Koleksi Buku</div>
                            <div class="fw-bold text-primary fs-5">
                                <?= isset($tabelStatus['buku']) ? $tabelStatus['buku']['total'] : 0 ?> Judul
                            </div>
                            <div class="small text-muted mt-1">Siap dikatalogkan</div>
                        </div>
                    </div>
                </div>

                <!-- Status Tabel Database -->
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="p-4 bg-slate-50 border-bottom border-slate-200 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-slate-900 mb-0">
                            <i class="bi bi-table me-2 text-primary"></i>Status Tabel Database
                        </h5>
                        <span class="small text-muted">Diverifikasi secara langsung dari MySQL</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Tabel</th>
                                    <th>Kegunaan</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Jumlah Data (Baris)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $infoTabel = [
                                    'kategori'       => 'Klasifikasi & kategori tema buku',
                                    'buku'           => 'Data inventaris katalog buku (judul, harga, stok, cover)',
                                    'users'          => 'Akun pelanggan & administrator toko',
                                    'pesanan'        => 'Header transaksi checkout pelanggan',
                                    'detail_pesanan' => 'Rincian item buku dalam setiap pesanan',
                                    'pembayaran'     => 'Status pembayaran & kode bayar (QRIS / Transfer Bank)',
                                ];
                                foreach ($infoTabel as $tName => $tDesc):
                                    $info = $tabelStatus[$tName] ?? ['ada' => false, 'total' => 0];
                                ?>
                                    <tr>
                                        <td>
                                            <code class="fw-bold text-slate-900 fs-6"><?= $tName ?></code>
                                        </td>
                                        <td class="text-muted small">
                                            <?= $tDesc ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($info['ada']): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="bi bi-check-circle me-1"></i>Tersedia
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                    <i class="bi bi-x-circle me-1"></i>Belum Ada
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-slate-900">
                                            <?= $info['total'] ?> baris
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Card: Inisialisasi Ulang & Hubungkan -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
                    <div class="row align-items-center g-4">
                        <div class="col-md-8">
                            <h5 class="fw-bold text-slate-900 mb-2">
                                <i class="bi bi-magic text-warning me-2"></i>Reset &amp; Buat Database Baru Bersih
                            </h5>
                            <p class="text-muted small mb-0">
                                Tombol ini akan otomatis membuat ulang database <code><?= DB_NAME ?></code> dan mengimpor seluruh tabel buku, 6 kategori, dan akun contoh admin &amp; pelanggan dari nol dalam 1 kali klik.
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset dan membuat database baru? Seluruh data pesanan lama akan diperbarui ke kondisi awal.');">
                                <input type="hidden" name="aksi" value="inisialisasi_ulang">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-arrow-repeat"></i>Inisialisasi Database Baru
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Akun Demo -->
                <div class="p-4 bg-white rounded-4 border border-slate-200 shadow-sm mb-4">
                    <h6 class="fw-bold text-slate-900 mb-3"><i class="bi bi-key-fill text-primary me-2"></i>Kredensial Akun Demo Bawaan</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 bg-slate-50 border rounded-3">
                                <span class="badge bg-warning text-dark mb-2">Akun Administrator</span>
                                <div>Email: <code>admin@bukuku.test</code></div>
                                <div>Password: <code>admin123</code></div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-slate-50 border rounded-3">
                                <span class="badge bg-primary text-white mb-2">Akun Pelanggan</span>
                                <div>Email: <code>budi@bukuku.test</code></div>
                                <div>Password: <code>user123</code></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <a href="index.php" class="btn btn-primary btn-lg px-5 fw-bold shadow-sm">
                        <i class="bi bi-arrow-right-circle me-2"></i>Masuk ke Katalog Toko Buku
                    </a>
                </div>

            </div>
        </div>
    </main>

    <footer class="py-4 text-center small text-muted border-top bg-white mt-auto">
        &copy; <?= date('Y') ?> BukuKu &mdash; Digital Bookstore Database Assistant
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
