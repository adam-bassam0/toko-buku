<?php
// ============================================
// File    : login.php
// Deskripsi: Form masuk untuk pelanggan & admin
// ============================================

require_once 'config/auth.php';

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
    <title>Masuk — BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">

    <!-- Simple Modern Top Bar -->
    <header class="py-3 px-4 border-bottom bg-white shadow-xs">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand text-dark" href="index.php">
                <span class="brand-icon-box">
                    <i class="bi bi-book-half"></i>
                </span>
                <span class="fw-bold text-slate-900">BukuKu</span>
            </a>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Ke Katalog
            </a>
        </div>
    </header>

    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <div class="brand-icon-box mx-auto mb-3" style="width: 48px; height: 48px; font-size: 1.3rem;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h4 class="fw-bold text-slate-900 mb-1">Selamat Datang Kembali</h4>
                            <p class="text-muted small">Masuk untuk melanjutkan pesanan Anda di BukuKu</p>
                        </div>

                        <?php if ($flash): ?>
                            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3 small" role="alert">
                                <?= htmlspecialchars($flash['text']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                            </div>
                        <?php endif; ?>

                        <form action="proses/proses_login.php" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        class="form-control border-start-0 ps-0"
                                        placeholder="nama@email.com"
                                        required
                                        autofocus
                                        autocomplete="email"
                                    >
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        class="form-control border-start-0 border-end-0 ps-0"
                                        placeholder="••••••••"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold mb-3 shadow-sm">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                            </button>
                        </form>

                        <div class="text-center small text-muted mb-4">
                            Belum punya akun? <a href="register.php" class="fw-semibold text-primary">Daftar sekarang</a>
                        </div>

                        <!-- Demo Account Quick Selector Chips -->
                        <div class="p-3 bg-slate-50 rounded-3 border border-slate-200">
                            <div class="small fw-bold text-slate-700 mb-2 d-flex align-items-center">
                                <i class="bi bi-lightning-charge-fill text-warning me-1"></i>Klik Cepat Akun Demo:
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="fillDemo('admin@bukuku.test', 'admin123')">
                                    <i class="bi bi-shield-lock me-1"></i>Admin
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="fillDemo('budi@bukuku.test', 'user123')">
                                    <i class="bi bi-person me-1"></i>Pelanggan
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-3 text-center small text-muted">
        &copy; <?= date('Y') ?> BukuKu &mdash; Digital Bookstore
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fillDemo(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
        }

        const toggleBtn = document.getElementById('togglePassword');
        const passInput = document.getElementById('password');
        toggleBtn.addEventListener('click', function () {
            const isPassword = passInput.type === 'password';
            passInput.type = isPassword ? 'text' : 'password';
            this.querySelector('i').className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>
</html>
