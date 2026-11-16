<?php
// ============================================
// File    : app/views/partials/navbar.php
// Deskripsi: Navbar bersama pelanggan & umum.
//            $koneksi sudah dimuat oleh app/bootstrap.php.
// ============================================

$jumlahCart = array_sum($_SESSION['cart'] ?? []);
$navUser    = auth_user();
$flashNav   = flash_take();
$cartToast  = $_SESSION['cart_toast'] ?? null;
unset($_SESSION['cart_toast']);

$navKategori = [];
if (isset($koneksi) && !$koneksi->connect_error) {
    $resKat = $koneksi->query("SELECT id, nama, slug FROM kategori ORDER BY nama ASC");
    if ($resKat) {
        $navKategori = $resKat->fetch_all(MYSQLI_ASSOC);
    }
}
$currentScript = basename($_SERVER['PHP_SELF']);
?>

<!-- Promo Top Bar -->
<div class="promo-top-bar d-none d-sm-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="mx-auto">
            <span>✨ Flash Sale Pembaca: Dapatkan potongan harga s.d 20% &amp; Bebas Ongkir se-Indonesia!</span>
            <a href="index.php">Jelajahi Sekarang &rarr;</a>
        </div>
        <button type="button" class="btn-close btn-close-white p-1" style="font-size: 0.65rem;" onclick="this.closest('.promo-top-bar').style.display='none';"></button>
    </div>
</div>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="index.php">
            <span class="brand-icon-box">
                <i class="bi bi-book-half"></i>
            </span>
            <span class="fw-bold tracking-tight">BukuKu</span>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Items -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= $currentScript === 'index.php' ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid me-1"></i>Kategori
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark bg-dark border-secondary shadow-lg">
                        <li>
                            <a class="dropdown-item py-2" href="index.php">
                                <i class="bi bi-collection me-2 text-warning"></i>Semua Buku
                            </a>
                        </li>
                        <?php if (!empty($navKategori)): ?>
                            <li><hr class="dropdown-divider border-secondary"></li>
                            <?php foreach ($navKategori as $nk): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="index.php?kategori=<?= urlencode($nk['slug']) ?>">
                                        <i class="bi bi-tag-fill me-2 text-info small"></i><?= htmlspecialchars($nk['nama']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <!-- Keranjang Belanja -->
                <li class="nav-item">
                    <a class="nav-link nav-cart-btn d-inline-flex align-items-center <?= $currentScript === 'cart.php' ? 'active' : '' ?>" href="cart.php">
                        <i class="bi bi-cart3 me-1"></i>Keranjang
                        <span class="badge rounded-pill bg-warning text-dark ms-2 fw-bold"><?= $jumlahCart ?></span>
                    </a>
                </li>

                <?php if (auth_is_admin()): ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-warning d-inline-flex align-items-center" href="admin/index.php">
                            <i class="bi bi-speedometer2 me-1"></i>Panel Admin
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($navUser): ?>
                    <!-- Dropdown User -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                <?= strtoupper(substr($navUser['nama'], 0, 1)) ?>
                            </span>
                            <span class="d-none d-sm-inline fw-semibold"><?= htmlspecialchars(explode(' ', $navUser['nama'])[0]) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 220px;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($navUser['nama']) ?></div>
                                <div class="small text-muted text-truncate"><?= htmlspecialchars($navUser['email']) ?></div>
                                <span class="badge bg-secondary-subtle text-secondary border mt-1">
                                    <i class="bi bi-shield-check me-1"></i><?= ucfirst(htmlspecialchars($navUser['role'])) ?>
                                </span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="pesanan.php">
                                    <i class="bi bi-receipt me-2 text-primary"></i>Pesanan Saya
                                </a>
                            </li>
                            <?php if (auth_is_admin()): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="admin/index.php">
                                        <i class="bi bi-speedometer2 me-2 text-warning"></i>Dashboard Admin
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="admin/kategori.php">
                                        <i class="bi bi-tags me-2 text-info"></i>Kelola Kategori
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item py-2" href="setup.php">
                                    <i class="bi bi-database-check me-2 text-success"></i>Database Assistant
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="proses/proses_logout.php" method="POST" class="px-2 mb-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-box-arrow-right"></i>Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Auth Buttons -->
                    <li class="nav-item">
                        <a class="nav-link <?= $currentScript === 'login.php' ? 'active' : '' ?>" href="login.php">Masuk</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-primary px-3 text-white" href="register.php">
                            Daftar Akun
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobile Floating Bottom Nav -->
<div class="mobile-bottom-nav d-md-none">
    <a href="index.php" class="mobile-bottom-nav-item <?= $currentScript === 'index.php' ? 'active' : '' ?>">
        <i class="bi bi-house-door"></i>
        <span>Beranda</span>
    </a>
    <a href="index.php#katalog" class="mobile-bottom-nav-item">
        <i class="bi bi-grid"></i>
        <span>Kategori</span>
    </a>
    <a href="cart.php" class="mobile-bottom-nav-item <?= $currentScript === 'cart.php' ? 'active' : '' ?>">
        <i class="bi bi-cart3"></i>
        <span>Keranjang</span>
        <?php if ($jumlahCart > 0): ?>
            <span class="mobile-cart-badge"><?= $jumlahCart ?></span>
        <?php endif; ?>
    </a>
    <a href="pesanan.php" class="mobile-bottom-nav-item <?= $currentScript === 'pesanan.php' ? 'active' : '' ?>">
        <i class="bi bi-receipt"></i>
        <span>Pesanan</span>
    </a>
    <?php if ($navUser): ?>
        <a href="<?= auth_is_admin() ? 'admin/index.php' : 'pesanan.php' ?>" class="mobile-bottom-nav-item">
            <i class="bi bi-person-circle"></i>
            <span>Akun</span>
        </a>
    <?php else: ?>
        <a href="login.php" class="mobile-bottom-nav-item <?= in_array($currentScript, ['login.php', 'register.php']) ? 'active' : '' ?>">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Masuk</span>
        </a>
    <?php endif; ?>
</div>

<!-- Flash Message Container -->
<?php if ($flashNav): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($flashNav['type']) ?> alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($flashNav['text']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Floating Cart Toast Notification -->
<?php if ($cartToast): ?>
    <div class="toast-container-fixed">
        <div class="toast toast-modern show" role="alert" aria-live="assertive" aria-atomic="true" id="cartSuccessToast">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle-fill me-2"></i>
                <strong class="me-auto"><?= htmlspecialchars($cartToast['title']) ?></strong>
                <small class="text-white-50">Baru saja</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body p-3">
                <p class="small text-slate-700 mb-2"><?= htmlspecialchars($cartToast['text']) ?></p>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Total: <strong><?= $cartToast['total'] ?> buku</strong></span>
                    <a href="cart.php" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="bi bi-cart3 me-1"></i>Lihat Keranjang
                    </a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('cartSuccessToast');
            if (toastEl) {
                setTimeout(() => {
                    const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl);
                    bsToast.hide();
                }, 5000);
            }
        });
    </script>
<?php endif; ?>
