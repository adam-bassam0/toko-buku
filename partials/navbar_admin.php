<?php
// ============================================
// File    : partials/navbar_admin.php
// Deskripsi: Navbar bersama untuk halaman panel admin.
//            Dipanggil dari file di dalam folder admin/.
//            Tampilan sengaja dibuat sama dengan navbar
//            pelanggan (bg-dark + container) agar konsisten,
//            dengan tambahan badge "Admin" dan menu kelola.
// ============================================

$navAdmin   = auth_user();
$flashAdmin = flash_take();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-book-half me-2"></i>BukuKu
            <span class="badge bg-warning text-dark align-middle ms-1">Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-journal-text me-1"></i>Data Buku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pesanan.php"><i class="bi bi-receipt me-1"></i>Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../index.php"><i class="bi bi-shop me-1"></i>Ke Toko</a>
                </li>

                <?php if ($navAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($navAdmin['nama']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li>
                                <span class="dropdown-item-text small text-muted">
                                    <?= htmlspecialchars($navAdmin['email']) ?><br>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($navAdmin['role']) ?></span>
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="../proses/proses_logout.php" method="POST" class="px-2">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="bi bi-box-arrow-right me-1"></i>Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if ($flashAdmin): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= htmlspecialchars($flashAdmin['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flashAdmin['text']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
<?php endif; ?>
