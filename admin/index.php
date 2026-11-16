<?php
// ============================================
// File    : admin/index.php
// Deskripsi: Dashboard admin untuk mengelola buku
// ============================================

require_once '../config/auth.php';
require_once '../config/koneksi.php';

require_admin('../login.php');

// Statistik ringkas admin
$totalBukuCount     = (int) ($koneksi->query("SELECT COUNT(*) AS total FROM buku")->fetch_assoc()['total'] ?? 0);
$totalStokSum       = (int) ($koneksi->query("SELECT SUM(stok) AS total FROM buku")->fetch_assoc()['total'] ?? 0);
$totalKategoriCount = (int) ($koneksi->query("SELECT COUNT(*) AS total FROM kategori")->fetch_assoc()['total'] ?? 0);
$totalStokRendah    = (int) ($koneksi->query("SELECT COUNT(*) AS total FROM buku WHERE stok <= 5")->fetch_assoc()['total'] ?? 0);

// Ambil semua kategori untuk filter
$kategoriList = $koneksi->query("SELECT id, nama FROM kategori ORDER BY nama ASC");

// Filter kategori jika dipilih
$kategoriFilter = isset($_GET['kategori']) ? (int) $_GET['kategori'] : 0;
$searchJudul    = isset($_GET['cari']) ? trim($_GET['cari']) : '';

if ($kategoriFilter > 0 && $searchJudul !== '') {
    $stmt = $koneksi->prepare(
        "SELECT b.*, k.nama AS nama_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE b.kategori_id = ? AND (b.judul LIKE ? OR b.penulis LIKE ?)
         ORDER BY b.id DESC"
    );
    $param = "%" . $searchJudul . "%";
    $stmt->bind_param("iss", $kategoriFilter, $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($kategoriFilter > 0) {
    $stmt = $koneksi->prepare(
        "SELECT b.*, k.nama AS nama_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE b.kategori_id = ?
         ORDER BY b.id DESC"
    );
    $stmt->bind_param("i", $kategoriFilter);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($searchJudul !== '') {
    $stmt = $koneksi->prepare(
        "SELECT b.*, k.nama AS nama_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE b.judul LIKE ? OR b.penulis LIKE ?
         ORDER BY b.id DESC"
    );
    $param = "%" . $searchJudul . "%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $koneksi->query(
        "SELECT b.*, k.nama AS nama_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         ORDER BY b.id DESC"
    );
}

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku — Admin BukuKu</title>
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

    <!-- ===== KONTEN UTAMA ===== -->
    <main class="container my-4 flex-grow-1">

        <!-- Header Dashboard -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-speedometer2 me-2 text-primary"></i>Manajemen Data Buku
                </h4>
                <p class="text-muted small mb-0">Kelola katalog inventaris buku toko online BukuKu.</p>
            </div>
            <a href="tambah.php" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Tambah Buku Baru</span>
            </a>
        </div>

        <!-- 4 KPI Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Judul</div>
                            <h3 class="fw-bold text-slate-900 mb-0"><?= $totalBukuCount ?></h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-book-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Fisik Stok</div>
                            <h3 class="fw-bold text-slate-900 mb-0"><?= number_format($totalStokSum, 0, ',', '.') ?></h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Stok Rendah (&le; 5)</div>
                            <h3 class="fw-bold text-danger mb-0"><?= $totalStokRendah ?></h3>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Kategori</div>
                            <h3 class="fw-bold text-slate-900 mb-0"><?= $totalKategoriCount ?></h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Notifikasi -->
        <?php if ($pesan === 'tambah_sukses'): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>Buku baru berhasil ditambahkan!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'edit_sukses'): ?>
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-pencil-fill me-2"></i>Data buku berhasil diperbarui!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'hapus_sukses'): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-trash-fill me-2"></i>Buku berhasil dihapus dari sistem.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'permintaan_tidak_valid'): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-shield-x me-2"></i>Token keamanan tidak valid. Silakan coba lagi.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'tidak_ditemukan'): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>Data buku tidak ditemukan.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($pesan === 'hapus_gagal'): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="bi bi-x-circle me-2"></i>Gagal menghapus buku. Pastikan tidak ada pesanan terkait.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Toolbar Pencarian & Filter -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <form action="index.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input
                            type="text"
                            name="cari"
                            class="form-control border-start-0 ps-0"
                            placeholder="Cari judul buku atau penulis..."
                            value="<?= htmlspecialchars($searchJudul) ?>"
                        >
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="kategori" class="form-select" onchange="this.form.submit()">
                        <option value="0">Semua Kategori</option>
                        <?php if ($kategoriList): ?>
                            <?php while ($kat = $kategoriList->fetch_assoc()): ?>
                                <option value="<?= $kat['id'] ?>" <?= $kategoriFilter === (int) $kat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kat['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        Filter
                    </button>
                    <?php if ($kategoriFilter > 0 || $searchJudul !== ''): ?>
                        <a href="index.php" class="btn btn-outline-secondary" title="Hapus filter">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tabel Data Buku Modern -->
        <div class="table-card shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">#</th>
                            <th width="70" class="text-center">Cover</th>
                            <th>Judul &amp; Penulis</th>
                            <th width="150">Kategori</th>
                            <th>Penerbit</th>
                            <th class="text-end">Harga</th>
                            <th class="text-center">Stok</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($buku = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center text-muted small"><?= $no++ ?></td>
                                    <td class="text-center">
                                        <img
                                            src="<?= $buku['cover'] ? htmlspecialchars($buku['cover']) : 'https://via.placeholder.com/55x70?text=N/A' ?>"
                                            class="cover-thumb"
                                            alt="Cover"
                                            onerror="this.src='https://via.placeholder.com/55x70?text=N/A'"
                                        >
                                    </td>
                                    <td>
                                        <div class="fw-bold text-slate-900"><?= htmlspecialchars($buku['judul']) ?></div>
                                        <div class="small text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($buku['penulis']) ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($buku['nama_kategori'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                                <?= htmlspecialchars($buku['nama_kategori']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">
                                                Tanpa Kategori
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-slate-600"><?= htmlspecialchars($buku['penerbit']) ?></td>
                                    <td class="text-end fw-semibold text-slate-900 text-nowrap">
                                        Rp <?= number_format($buku['harga'], 0, ',', '.') ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($buku['stok'] > 10): ?>
                                            <span class="badge bg-success-subtle text-success">
                                                <?= $buku['stok'] ?>
                                            </span>
                                        <?php elseif ($buku['stok'] > 0): ?>
                                            <span class="badge bg-warning-subtle text-warning">
                                                <?= $buku['stok'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger">
                                                Habis
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="edit.php?id=<?= $buku['id'] ?>" class="btn btn-outline-warning btn-sm p-1 px-2" title="Edit Buku">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="../proses/proses_hapus.php" method="POST" class="d-inline" onsubmit="return confirm('Hapus buku \'<?= addslashes(htmlspecialchars($buku['judul'])) ?>\'?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="id" value="<?= (int) $buku['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Hapus Buku">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 text-secondary"></i>
                                    Tidak ada data buku yang sesuai filter. <a href="tambah.php" class="fw-semibold">Tambahkan buku baru!</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="small text-muted">
            Menampilkan <strong><?= $result ? $result->num_rows : 0 ?></strong> dari total <strong><?= $totalBukuCount ?></strong> buku.
        </div>

    </main>

    <!-- ===== FOOTER ===== -->
    <?php require __DIR__ . '/../app/views/partials/footer_admin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
