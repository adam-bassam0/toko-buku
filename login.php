<?php
// ============================================
// File    : login.php
// Deskripsi: Form masuk untuk pelanggan & admin.
// ============================================

require_once 'config/auth.php';

// Sudah login? Tidak perlu melihat form login lagi.
if (auth_check()) {
    header('Location: index.php');
    exit;
}

$flash = flash_take();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-book-half me-2"></i>BukuKu</a>
            <div class="navbar-nav ms-auto flex-row gap-3">
                <a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Beranda</a>
                <a class="nav-link active" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
                <a class="nav-link" href="register.php"><i class="bi bi-person-plus me-1"></i>Daftar</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-1"><i class="bi bi-box-arrow-in-right me-2 text-primary"></i>Masuk</h4>
                        <p class="text-muted mb-4">Masuk untuk melanjutkan belanja dan melihat pesanan.</p>

                        <?php if ($flash): ?>
                            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($flash['text']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                            </div>
                        <?php endif; ?>

                        <form action="proses/proses_login.php" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" id="email" class="form-control" placeholder="nama@email.com" required autofocus autocomplete="email">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required autocomplete="current-password">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                            </button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Belum punya akun? <a href="register.php">Daftar di sini</a>
                        </p>

                        <hr class="my-4">
                        <div class="small text-muted">
                            <div class="fw-semibold mb-1"><i class="bi bi-info-circle me-1"></i>Akun demo:</div>
                            Admin: <code>admin@bukuku.test</code> / <code>admin123</code><br>
                            Pelanggan: <code>budi@bukuku.test</code> / <code>user123</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
