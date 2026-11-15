<?php
// ============================================
// File    : partials/navbar_admin.php
// Deskripsi: Navbar bersama untuk panel admin
// ============================================

$navAdmin   = auth_user();
$flashAdmin = flash_take();
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="index.php">
            <span class="brand-icon-box" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="bi bi-shield-lock-fill"></i>
            </span>
            <span class="fw-bold">BukuKu</span>
            <span class="badge bg-warning text-dark ms-1 font-monospace" style="font-size: 0.7rem;">ADMIN</span>
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse" id="navbarAdmin">
            <ul class="navbar-nav me-auto ms-lg-3">
                <li class="nav-item">
                    <a class="nav-link <?= in_array($currentFile, ['index.php', 'tambah.php', 'edit.php']) ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-journal-text me-1"></i>Data Buku
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentFile === 'kategori.php' ? 'active' : '' ?>" href="kategori.php">
                        <i class="bi bi-tags me-1"></i>Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentFile === 'pesanan.php' ? 'active' : '' ?>" href="pesanan.php">
                        <i class="bi bi-receipt me-1"></i>Pesanan
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                <!-- Database Health Pill -->
                <li class="nav-item">
                    <a href="../setup.php" class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 text-decoration-none py-2 px-3 d-inline-flex align-items-center gap-1" title="Status Database: Aktif & Terhubung">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 8px; height: 8px;" role="status"></span>
                        <span class="font-monospace small">MySQL OK</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="btn btn-sm btn-outline-light d-inline-flex align-items-center" href="../index.php" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>Kunjungi Toko
                    </a>
                </li>

                <?php if ($navAdmin): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning text-dark fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                <?= strtoupper(substr($navAdmin['nama'], 0, 1)) ?>
                            </span>
                            <span class="d-none d-sm-inline fw-semibold"><?= htmlspecialchars(explode(' ', $navAdmin['nama'])[0]) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" style="min-width: 200px;">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark"><?= htmlspecialchars($navAdmin['nama']) ?></div>
                                <div class="small text-muted text-truncate"><?= htmlspecialchars($navAdmin['email']) ?></div>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="../setup.php">
                                    <i class="bi bi-database-check me-2 text-primary"></i>Database Assistant
                                </a>
                            </li>
                            <li class="pt-2">
                                <form action="../proses/proses_logout.php" method="POST" class="px-2 mb-1">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-box-arrow-right"></i>Keluar
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
        <div class="alert alert-<?= htmlspecialchars($flashAdmin['type']) ?> alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div><?= htmlspecialchars($flashAdmin['text']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
<?php endif; ?>
