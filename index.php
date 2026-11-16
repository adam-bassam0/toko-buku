<?php

require_once 'config/auth.php';
require_once 'config/koneksi.php';

// Jumlah seluruh buku di keranjang untuk badge navbar.
$jumlahCart = array_sum($_SESSION['cart'] ?? []);

// --- Ambil Kategori & Hitung Buku ---
$resKategori = $koneksi->query(
    "SELECT k.id, k.nama, k.slug, COUNT(b.id) AS total_buku
     FROM kategori k
     LEFT JOIN buku b ON b.kategori_id = k.id
     GROUP BY k.id
     ORDER BY k.nama ASC"
);
$daftarKategori = $resKategori ? $resKategori->fetch_all(MYSQLI_ASSOC) : [];

// Hitung total semua buku di database
$resTotalBuku = $koneksi->query("SELECT COUNT(*) AS total FROM buku");
$totalSemuaBuku = $resTotalBuku ? (int) $resTotalBuku->fetch_assoc()['total'] : 0;

// --- Parameter Pencarian, Kategori, & Pengurutan ---
$kataCari      = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$kategoriParam = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$urutkan       = isset($_GET['urutkan']) ? trim($_GET['urutkan']) : 'terbaru';

$orderClause = match ($urutkan) {
    'termurah' => 'b.harga ASC',
    'termahal' => 'b.harga DESC',
    'abjad'    => 'b.judul ASC',
    default    => 'b.id DESC'
};

// Cari data kategori aktif
$kategoriAktif = null;
if ($kategoriParam !== '') {
    foreach ($daftarKategori as $kat) {
        if ($kat['slug'] === $kategoriParam || (string) $kat['id'] === $kategoriParam) {
            $kategoriAktif = $kat;
            break;
        }
    }
}

// Icon helper untuk kategori
function getKategoriIcon(string $slug): string {
    return match($slug) {
        'fiksi-sastra'      => 'bi-magic',
        'pengembangan-diri' => 'bi-graph-up-arrow',
        'komik-manga'       => 'bi-emoji-smile',
        'pelajaran-sains'   => 'bi-mortarboard',
        'agama-filsafat'    => 'bi-brightness-high',
        'anak-anak'         => 'bi-balloon',
        default             => 'bi-tag-fill'
    };
}

// --- Query Buku Berdasarkan Filter & Sorting ---
if ($kataCari !== '' && $kategoriAktif !== null) {
    $stmt = $koneksi->prepare(
        "SELECT b.*, k.nama AS nama_kategori, k.slug AS slug_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE (b.judul LIKE ? OR b.penulis LIKE ?) AND b.kategori_id = ?
         ORDER BY {$orderClause}"
    );
    $paramCari = "%" . $kataCari . "%";
    $katId = (int) $kategoriAktif['id'];
    $stmt->bind_param("ssi", $paramCari, $paramCari, $katId);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($kataCari !== '') {
    $stmt = $koneksi->prepare(
        "SELECT b.*, k.nama AS nama_kategori, k.slug AS slug_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE b.judul LIKE ? OR b.penulis LIKE ?
         ORDER BY {$orderClause}"
    );
    $paramCari = "%" . $kataCari . "%";
    $stmt->bind_param("ss", $paramCari, $paramCari);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($kategoriAktif !== null) {
    $stmt = $koneksi->prepare(
        "SELECT b.*, k.nama AS nama_kategori, k.slug AS slug_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         WHERE b.kategori_id = ?
         ORDER BY {$orderClause}"
    );
    $katId = (int) $kategoriAktif['id'];
    $stmt->bind_param("i", $katId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $koneksi->query(
        "SELECT b.*, k.nama AS nama_kategori, k.slug AS slug_kategori
         FROM buku b
         LEFT JOIN kategori k ON b.kategori_id = k.id
         ORDER BY {$orderClause}"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BukuKu — Toko Buku Digital &amp; Inspirasi Bacaan Pilihan</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <?php require __DIR__ . '/app/views/partials/navbar.php'; ?>

    <!-- ===== HERO SECTION ===== -->
    <section class="hero-section text-center">
        <div class="container position-relative" style="z-index: 2;">
            <div class="hero-badge mb-3">
                <i class="bi bi-stars text-warning"></i>
                <span>Toko Buku Digital Terpercaya #1</span>
            </div>
            <h1 class="mb-3">Jelajahi Inspirasi, Beli Buku Berkualitas</h1>
            <p class="lead text-white-50 mx-auto mb-4" style="max-width: 640px; font-size: 1.05rem;">
                Temukan lebih dari <?= $totalSemuaBuku ?> judul buku pilihan mulai dari best-seller, sastra, bisnis, manga, hingga buku pelajaran dengan harga ramah kantong.
            </p>

            <!-- Search Bar Modern -->
            <div class="hero-search-box">
                <form action="index.php" method="GET" class="d-flex align-items-center">
                    <?php if ($kategoriAktif): ?>
                        <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategoriAktif['slug']) ?>">
                    <?php endif; ?>
                    <?php if ($urutkan !== 'terbaru'): ?>
                        <input type="hidden" name="urutkan" value="<?= htmlspecialchars($urutkan) ?>">
                    <?php endif; ?>
                    <i class="bi bi-search text-white-50 ms-3 fs-5"></i>
                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="<?= $kategoriAktif ? 'Cari dalam ' . htmlspecialchars($kategoriAktif['nama']) . '...' : 'Cari judul buku, penulis, atau kata kunci...' ?>"
                        value="<?= htmlspecialchars($kataCari) ?>"
                        autocomplete="off"
                    >
                    <button class="btn btn-search me-1" type="submit">
                        Cari Buku
                    </button>
                    <?php if ($kataCari !== '' || $kategoriAktif): ?>
                        <a href="index.php" class="btn btn-link text-white-50 p-2 me-1" title="Hapus pencarian">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Popular Quick Search Tags -->
            <div class="popular-tags-wrapper">
                <span class="small text-white-50"><i class="bi bi-fire text-warning me-1"></i>Pencarian Populer:</span>
                <a href="index.php?cari=Atomic+Habits" class="popular-tag-chip">#AtomicHabits</a>
                <a href="index.php?kategori=komik-manga" class="popular-tag-chip">#OnePiece</a>
                <a href="index.php?cari=Harry+Potter" class="popular-tag-chip">#HarryPotter</a>
                <a href="index.php?cari=Filosofi+Teras" class="popular-tag-chip">#FilosofiTeras</a>
                <a href="index.php?kategori=pelajaran-sains" class="popular-tag-chip">#Sains</a>
                <a href="index.php?kategori=anak-anak" class="popular-tag-chip">#TheLittlePrince</a>
            </div>
        </div>
    </section>

    <!-- ===== TRUST / HIGHLIGHTS BAR ===== -->
    <section class="trust-bar d-none d-md-block">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="bi bi-book"></i>
                        </div>
                        <div class="trust-text">
                            <h6><?= $totalSemuaBuku ?>+ Koleksi Buku</h6>
                            <p>Koleksi judul terlengkap &amp; update</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div class="trust-text">
                            <h6>Pengiriman Cepat</h6>
                            <p>Packing tebal &amp; aman sampai tujuan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <div class="trust-text">
                            <h6>QRIS &amp; Transfer Bank</h6>
                            <p>Bayar instan bebas ribet</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trust-item">
                        <div class="trust-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="trust-text">
                            <h6>100% Original</h6>
                            <p>Jaminan buku asli resmi penerbit</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FILTER KATEGORI (STICKY BAR) ===== -->
    <section class="category-filter-section py-3" id="katalog">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold small text-muted text-uppercase" style="letter-spacing: 0.05em;">
                        <i class="bi bi-grid-fill me-1 text-primary"></i>Kategori Pilihan
                    </span>
                    <?php if ($kategoriAktif): ?>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            <?= htmlspecialchars($kategoriAktif['nama']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($kategoriAktif || $kataCari !== ''): ?>
                    <a href="index.php" class="small text-danger fw-semibold text-decoration-none">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Hapus Semua Filter
                    </a>
                <?php endif; ?>
            </div>

            <!-- Horizontal Pills Navigasi Kategori -->
            <div class="category-pills-container d-flex flex-nowrap overflow-x-auto gap-2 pb-1">
                <!-- Tombol Semua -->
                <?php
                    $allQuery = [];
                    if ($kataCari !== '') $allQuery['cari'] = $kataCari;
                    if ($urutkan !== 'terbaru') $allQuery['urutkan'] = $urutkan;
                ?>
                <a
                    href="index.php<?= !empty($allQuery) ? '?' . http_build_query($allQuery) : '' ?>#katalog"
                    class="category-pill <?= !$kategoriAktif ? 'active' : '' ?>"
                >
                    <i class="bi bi-collection"></i>
                    <span>Semua Koleksi</span>
                    <span class="badge rounded-pill ms-1 bg-secondary-subtle text-dark"><?= $totalSemuaBuku ?></span>
                </a>

                <!-- Daftar Kategori -->
                <?php foreach ($daftarKategori as $kat): ?>
                    <?php
                        $isActive = ($kategoriAktif && $kategoriAktif['id'] == $kat['id']);
                        $iconClass = getKategoriIcon($kat['slug']);
                        $linkQuery = ['kategori' => $kat['slug']];
                        if ($kataCari !== '') $linkQuery['cari'] = $kataCari;
                        if ($urutkan !== 'terbaru') $linkQuery['urutkan'] = $urutkan;
                    ?>
                    <a
                        href="index.php?<?= http_build_query($linkQuery) ?>#katalog"
                        class="category-pill <?= $isActive ? 'active' : '' ?>"
                    >
                        <i class="bi <?= $iconClass ?>"></i>
                        <span><?= htmlspecialchars($kat['nama']) ?></span>
                        <span class="badge rounded-pill ms-1 <?= $isActive ? 'bg-warning text-dark' : 'bg-secondary-subtle text-dark' ?>">
                            <?= $kat['total_buku'] ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===== KATALOG BUKU ===== -->
    <main class="container my-5 flex-grow-1">

        <!-- Banner Informasi Filter / Pencarian -->
        <?php if ($kategoriAktif || $kataCari !== ''): ?>
            <div class="alert bg-white border border-slate-200 shadow-sm rounded-4 d-flex align-items-center justify-content-between p-3 mb-4 flex-wrap gap-2" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex p-2 rounded-3 bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-funnel-fill fs-5"></i>
                    </div>
                    <div>
                        <?php if ($kategoriAktif && $kataCari !== ''): ?>
                            <div class="fw-bold text-dark">Hasil Pencarian "<?= htmlspecialchars($kataCari) ?>"</div>
                            <div class="small text-muted">Kategori: <?= htmlspecialchars($kategoriAktif['nama']) ?> &bull; Ditemukan <?= $result->num_rows ?> buku</div>
                        <?php elseif ($kategoriAktif): ?>
                            <div class="fw-bold text-dark">Koleksi: <?= htmlspecialchars($kategoriAktif['nama']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($kategoriAktif['deskripsi'] ?? 'Ditemukan ' . $result->num_rows . ' buku') ?></div>
                        <?php else: ?>
                            <div class="fw-bold text-dark">Hasil Pencarian "<?= htmlspecialchars($kataCari) ?>"</div>
                            <div class="small text-muted">Ditemukan <?= $result->num_rows ?> buku sesuai kata kunci</div>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle me-1"></i>Hapus Filter
                </a>
            </div>
        <?php endif; ?>

        <!-- Toolbar Pengurutan & Jumlah Koleksi -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div class="small text-muted">
                Menampilkan <strong class="text-slate-900"><?= $result ? $result->num_rows : 0 ?></strong> judul buku pilihan
            </div>

            <!-- Sorting Dropdown -->
            <form action="index.php#katalog" method="GET" class="d-flex align-items-center gap-2">
                <?php if ($kategoriAktif): ?>
                    <input type="hidden" name="kategori" value="<?= htmlspecialchars($kategoriAktif['slug']) ?>">
                <?php endif; ?>
                <?php if ($kataCari !== ''): ?>
                    <input type="hidden" name="cari" value="<?= htmlspecialchars($kataCari) ?>">
                <?php endif; ?>
                <label for="urutkanSelect" class="small text-muted text-nowrap mb-0 d-none d-sm-inline">Urutkan:</label>
                <select name="urutkan" id="urutkanSelect" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                    <option value="terbaru" <?= $urutkan === 'terbaru' ? 'selected' : '' ?>>✨ Terbaru</option>
                    <option value="termurah" <?= $urutkan === 'termurah' ? 'selected' : '' ?>>🏷️ Harga: Terendah</option>
                    <option value="termahal" <?= $urutkan === 'termahal' ? 'selected' : '' ?>>💎 Harga: Tertinggi</option>
                    <option value="abjad" <?= $urutkan === 'abjad' ? 'selected' : '' ?>>🔤 Judul: A &ndash; Z</option>
                </select>
            </form>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <!-- Grid Koleksi Buku Modern -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php while ($buku = $result->fetch_assoc()): ?>
                    <?php
                        // Hitung harga normal fiktif untuk efek promo e-commerce
                        $hargaJual = (float) $buku['harga'];
                        $hargaCoret = $hargaJual > 0 ? (int) round($hargaJual * 1.15 / 1000) * 1000 : 0;
                        $ratingVal = number_format(4.7 + (($buku['id'] % 3) * 0.1), 1);
                        $ulasanCnt = 40 + (($buku['id'] * 7) % 110);
                    ?>
                    <div class="col">
                        <div class="book-card">
                            <!-- Image Container -->
                            <div class="book-card-image-wrapper">
                                <a href="detail.php?id=<?= $buku['id'] ?>">
                                    <img
                                        src="<?= $buku['cover'] ? htmlspecialchars($buku['cover']) : 'https://via.placeholder.com/300x400?text=No+Cover' ?>"
                                        alt="Cover <?= htmlspecialchars($buku['judul']) ?>"
                                        loading="lazy"
                                        onerror="this.src='https://via.placeholder.com/300x400?text=No+Cover'"
                                    >
                                </a>

                                <!-- Floating Category Badge -->
                                <?php if (!empty($buku['nama_kategori'])): ?>
                                    <a
                                        href="index.php?kategori=<?= urlencode($buku['slug_kategori'] ?? $buku['kategori_id']) ?>"
                                        class="card-badge-category text-decoration-none"
                                    >
                                        <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($buku['nama_kategori']) ?>
                                    </a>
                                <?php endif; ?>

                                <!-- Stok Habis Overlay -->
                                <?php if ($buku['stok'] <= 0): ?>
                                    <span class="card-badge-stock-out">
                                        Stok Habis
                                    </span>
                                <?php endif; ?>

                                <!-- Quick View Button on Hover -->
                                <button
                                    type="button"
                                    class="quick-view-btn"
                                    onclick="openQuickView(<?= htmlspecialchars(json_encode($buku), ENT_QUOTES, 'UTF-8') ?>)"
                                >
                                    <i class="bi bi-eye me-1"></i>Pratinjau Cepat
                                </button>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body">
                                <!-- Rating & Review Count -->
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="rating-badge">
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <span><?= $ratingVal ?></span>
                                    </span>
                                    <span class="text-muted small" style="font-size: 0.73rem;">(<?= $ulasanCnt ?> ulasan)</span>
                                </div>

                                <h6 class="card-title">
                                    <a href="detail.php?id=<?= $buku['id'] ?>" class="text-slate-900 text-decoration-none">
                                        <?= htmlspecialchars($buku['judul']) ?>
                                    </a>
                                </h6>
                                <div class="author-text">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($buku['penulis']) ?>
                                </div>
                                <div class="publisher-text">
                                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($buku['penerbit']) ?>
                                </div>

                                <div class="price-row">
                                    <div>
                                        <div class="d-flex align-items-baseline">
                                            <div class="price-tag">
                                                Rp <?= number_format($buku['harga'], 0, ',', '.') ?>
                                            </div>
                                            <?php if ($hargaCoret > $hargaJual): ?>
                                                <span class="price-struck">Rp <?= number_format($hargaCoret, 0, ',', '.') ?></span>
                                                <span class="discount-pill">-15%</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if ($buku['stok'] > 10): ?>
                                            <span class="badge bg-success-subtle text-success small">
                                                Stok <?= $buku['stok'] ?>
                                            </span>
                                        <?php elseif ($buku['stok'] > 0): ?>
                                            <span class="badge bg-warning-subtle text-warning small">
                                                Sisa <?= $buku['stok'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger small">
                                                Habis
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="card-actions">
                                    <a href="detail.php?id=<?= $buku['id'] ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center">
                                        <i class="bi bi-info-circle me-1"></i>Detail
                                    </a>
                                    <form action="proses/proses_cart.php" method="POST" class="m-0">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="aksi" value="tambah">
                                        <input type="hidden" name="id_buku" value="<?= (int) $buku['id'] ?>">
                                        <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                        <button
                                            type="submit"
                                            class="btn btn-warning btn-sm d-flex align-items-center justify-content-center px-3"
                                            <?= $buku['stok'] <= 0 ? 'disabled' : '' ?>
                                            title="<?= $buku['stok'] > 0 ? 'Tambah ke Keranjang' : 'Stok habis' ?>"
                                        >
                                            <i class="bi bi-cart-plus-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <!-- State Kosong / Tidak Ditemukan -->
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 my-4 bg-white">
                <div class="card-body">
                    <div class="d-inline-flex p-4 rounded-circle bg-light text-muted mb-3">
                        <i class="bi bi-inbox display-3 text-secondary"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Buku Tidak Ditemukan</h4>
                    <p class="text-muted mb-4 mx-auto" style="max-width: 480px;">
                        <?php if ($kategoriAktif): ?>
                            Belum ada koleksi buku untuk kategori <strong>"<?= htmlspecialchars($kategoriAktif['nama']) ?>"</strong>.
                        <?php else: ?>
                            Kata kunci yang Anda cari tidak cocok dengan koleksi yang tersedia. Coba gunakan kata kunci lain atau lihat seluruh katalog.
                        <?php endif; ?>
                    </p>
                    <a href="index.php" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-arrow-left me-1"></i>Lihat Semua Koleksi Buku
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Newsletter & Reader's Club Section -->
        <section class="mt-5 pt-4">
            <div class="card border-0 rounded-4 overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-7">
                            <span class="badge bg-warning text-dark mb-2 font-monospace">KLUB PEMBACA</span>
                            <h3 class="fw-bold mb-2">Dapatkan Rekomendasi Buku &amp; Promo Menarik</h3>
                            <p class="text-white-50 small mb-0">Daftarkan email Anda untuk menerima info buku terbaru, resensi pilihan, dan voucher diskon khusus setiap minggunya.</p>
                        </div>
                        <div class="col-lg-5">
                            <form onsubmit="alert('Terima kasih! Anda telah terdaftar dalam Klub Pembaca BukuKu.'); return false;" class="d-flex gap-2">
                                <input type="email" class="form-control" placeholder="Masukkan alamat email Anda..." required>
                                <button type="submit" class="btn btn-warning fw-bold text-nowrap px-3">
                                    Langganan
                                </button>
                            </form>
                            <div class="small text-white-50 mt-2" style="font-size: 0.75rem;">
                                <i class="bi bi-shield-lock me-1"></i>Kami menjaga privasi Anda. Bebas spam.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimoni Pelanggan -->
        <section class="mt-5">
            <div class="text-center mb-4">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-2">TESTIMONI</span>
                <h4 class="fw-bold text-slate-900">Kata Para Pembaca BukuKu</h4>
                <p class="text-muted small">Pengalaman nyata dari ribuan pelanggan di seluruh Indonesia</p>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="card border-0 shadow-xs rounded-4 p-4 bg-white h-100">
                        <div class="d-flex text-warning mb-2">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small text-slate-700 mb-3" style="line-height: 1.6;">
                            "Buku sampai hanya dalam 1 hari! Pengemasan bubble wrap-nya sangat tebal dan buku dalam kondisi segel rapat 100% original."
                        </p>
                        <div class="d-flex align-items-center gap-2 mt-auto">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">RS</div>
                            <div>
                                <div class="fw-bold small text-slate-900">Rina S.</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Verified Buyer &bull; Bandung</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-xs rounded-4 p-4 bg-white h-100">
                        <div class="d-flex text-warning mb-2">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small text-slate-700 mb-3" style="line-height: 1.6;">
                            "Koleksinya sangat lengkap, dari komik One Piece sampai novel klasik Buru karya Pramoedya ada semua. Web-nya juga sangat responsif dan enteng."
                        </p>
                        <div class="d-flex align-items-center gap-2 mt-auto">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">DP</div>
                            <div>
                                <div class="fw-bold small text-slate-900">Dimas Pratama</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Verified Buyer &bull; Jakarta</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-xs rounded-4 p-4 bg-white h-100">
                        <div class="d-flex text-warning mb-2">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="small text-slate-700 mb-3" style="line-height: 1.6;">
                            "Sistem checkout dan bayar pakai QRIS cepat banget. Tinggal scan langsung lunas tanpa konfirmasi manual berbelit-belit. Sangat direkomendasikan!"
                        </p>
                        <div class="d-flex align-items-center gap-2 mt-auto">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">FM</div>
                            <div>
                                <div class="fw-bold small text-slate-900">Farhan M.</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Verified Buyer &bull; Surabaya</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- ===== QUICK VIEW MODAL ===== -->
    <div class="modal fade" id="modalQuickView" tabindex="-1" aria-labelledby="modalQuickViewLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2" id="qvKategori">Kategori</span>
                    <h5 class="modal-title fw-bold text-slate-900" id="modalQuickViewLabel">Pratinjau Buku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5 text-center">
                            <div class="rounded-3 overflow-hidden shadow-sm border bg-slate-50 p-2 mx-auto" style="max-width: 240px;">
                                <img id="qvCover" src="" alt="Cover Buku" class="img-fluid rounded" style="max-height: 320px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h4 class="fw-bold text-slate-900 mb-2" id="qvJudul">Judul Buku</h4>
                            <div class="small text-muted mb-2">
                                <div><i class="bi bi-person me-1"></i>Penulis: <strong id="qvPenulis" class="text-slate-800"></strong></div>
                                <div><i class="bi bi-building me-1"></i>Penerbit: <strong id="qvPenerbit" class="text-slate-800"></strong></div>
                            </div>
                            <div class="my-3 p-3 bg-slate-50 rounded-3 border">
                                <div class="small text-muted">Harga Satuan</div>
                                <h3 class="fw-bold text-primary mb-0" id="qvHarga">Rp 0</h3>
                                <span class="badge bg-success-subtle text-success mt-1" id="qvStok">Stok Tersedia</span>
                            </div>
                            <div class="small text-secondary mb-3 lh-base" id="qvDeskripsi" style="max-height: 120px; overflow-y: auto;">
                                Deskripsi...
                            </div>

                            <div class="d-flex gap-2">
                                <form action="proses/proses_cart.php" method="POST" class="flex-grow-1" id="qvFormCart">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <input type="hidden" name="aksi" value="tambah">
                                    <input type="hidden" name="id_buku" id="qvIdBuku" value="">
                                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                                    <button type="submit" class="btn btn-warning w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-cart-plus-fill"></i>Tambah ke Keranjang
                                    </button>
                                </form>
                                <a href="#" id="qvDetailLink" class="btn btn-outline-secondary">
                                    Detail Penuh
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <footer class="modern-footer">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="brand-icon-box" style="width: 32px; height: 32px;">
                            <i class="bi bi-book-half"></i>
                        </span>
                        <span class="h5 mb-0 text-white fw-bold">BukuKu</span>
                    </div>
                    <p class="small text-slate-400 mb-3" style="line-height: 1.6;">
                        Platform toko buku digital modern yang menyediakan ribuan pilihan buku terlengkap dengan kemudahan transaksi dan pengalaman membaca terbaik.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-secondary text-white rounded-circle p-2" style="width: 34px; height: 34px;"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-secondary text-white rounded-circle p-2" style="width: 34px; height: 34px;"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-secondary text-white rounded-circle p-2" style="width: 34px; height: 34px;"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <h6>Navigasi</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="index.php">Katalog Buku</a></li>
                        <li class="mb-2"><a href="cart.php">Keranjang Belanja</a></li>
                        <li class="mb-2"><a href="pesanan.php">Riwayat Pesanan</a></li>
                        <li class="mb-2"><a href="login.php">Masuk Akun</a></li>
                        <li class="mb-2"><a href="setup.php">Database Assistant</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-3">
                    <h6>Kategori Populer</h6>
                    <ul class="list-unstyled small">
                        <?php foreach (array_slice($daftarKategori, 0, 5) as $fk): ?>
                            <li class="mb-2">
                                <a href="index.php?kategori=<?= urlencode($fk['slug']) ?>">
                                    <?= htmlspecialchars($fk['nama']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h6>Metode Pembayaran</h6>
                    <p class="small text-slate-400 mb-3">Mendukung pembayaran digital instan dan transfer bank nasional.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1">QRIS</span>
                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1">BCA</span>
                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1">Mandiri</span>
                    </div>
                </div>
            </div>

            <hr class="border-secondary opacity-25 my-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-slate-400 gap-2">
                <div>&copy; <?= date('Y') ?> BukuKu Store &mdash; Digital Bookstore. All rights reserved.</div>
                <div>Dirancang dengan antarmuka modern &amp; responsif</div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        function openQuickView(buku) {
            document.getElementById('qvJudul').innerText = buku.judul || '-';
            document.getElementById('qvPenulis').innerText = buku.penulis || '-';
            document.getElementById('qvPenerbit').innerText = buku.penerbit || '-';
            document.getElementById('qvKategori').innerText = buku.nama_kategori || 'Umum';
            document.getElementById('qvHarga').innerText = 'Rp ' + Number(buku.harga).toLocaleString('id-ID');
            document.getElementById('qvCover').src = buku.cover || 'https://via.placeholder.com/300x400?text=No+Cover';
            document.getElementById('qvDeskripsi').innerText = buku.deskripsi || 'Tidak ada deskripsi.';
            document.getElementById('qvIdBuku').value = buku.id;
            document.getElementById('qvDetailLink').href = 'detail.php?id=' + buku.id;

            const stokEl = document.getElementById('qvStok');
            if (Number(buku.stok) > 0) {
                stokEl.className = 'badge bg-success-subtle text-success mt-1';
                stokEl.innerText = 'Stok Tersedia (' + buku.stok + ' unit)';
            } else {
                stokEl.className = 'badge bg-danger-subtle text-danger mt-1';
                stokEl.innerText = 'Stok Habis';
            }

            const modal = new bootstrap.Modal(document.getElementById('modalQuickView'));
            modal.show();
        }
    </script>
</body>
</html>
