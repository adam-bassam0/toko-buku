<?php
// ============================================
// File    : partials/navbar.php
// Deskripsi: Navbar bersama untuk halaman pelanggan.
//            Menampilkan menu berbeda tergantung status login
//            dan role (pelanggan vs admin).
// Catatan : file ini dipanggil dari halaman root (index.php,
//           detail.php, cart.php), jadi semua link relatif root.
// ============================================

$jumlahCart = array_sum($_SESSION['cart'] ?? []);
$navUser    = auth_user();
$flashNav   = flash_take();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-book-half me-2"></i>BukuKu
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Beranda</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3 me-1"></i>Keranjang
                        <span class="badge bg-warning text-dark"><?= $jumlahCart ?></span>
                    </a>
                </li>

                <?php if (auth_is_admin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php"><i class="bi bi-gear me-1"></i>Panel Admin</a>
                    </li>
                <?php endif; ?>

                <?php if ($navUser): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($navUser['nama']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= htmlspecialchars($navUser['email']) ?><br>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($navUser['role']) ?></span>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="pesanan.php"><i class="bi bi-receipt me-1"></i>Pesanan Saya</a></li>
                            <?php if (auth_is_admin()): ?>
                                <li><a class="dropdown-item" href="admin/index.php"><i class="bi bi-gear me-1"></i>Panel Admin</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="proses/proses_logout.php" method="POST" class="px-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-box-arrow-right me-1"></i>Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php"><i class="bi bi-person-plus me-1"></i>Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flashNav): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($flashNav['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flashNav['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
<?php endif; ?>
