<?php
// ============================================
// File    : register.php
// Deskripsi: Form pendaftaran akun pelanggan.
//            Role selalu 'user' (tidak bisa memilih admin).
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
    <title>Daftar - BukuKu</title>
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
                <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
                <a class="nav-link active" href="register.php"><i class="bi bi-person-plus me-1"></i>Daftar</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <h4 class="fw-bold mb-1"><i class="bi bi-person-plus me-2 text-success"></i>Daftar Akun</h4>
                        <p class="text-muted mb-4">Buat akun untuk mempermudah checkout & riwayat pesanan.</p>

                        <?php if ($flash): ?>
                            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($flash['text']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                            </div>
                        <?php endif; ?>

                        <form action="proses/proses_register.php" method="POST" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                            <div class="mb-3">
                                <label for="nama" class="form-label fw-semibold">Nama Lengkap</label>
                                <input type="text" name="nama" id="nama" class="form-control" placeholder="Nama kamu" required maxlength="100" autofocus autocomplete="name">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="nama@email.com" required maxlength="150" autocomplete="email">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password">
                            </div>

                            <div class="mb-4">
                                <label for="password_konfirmasi" class="form-label fw-semibold">Ulangi Password</label>
                                <input type="password" name="password_konfirmasi" id="password_konfirmasi" class="form-control" placeholder="Ketik ulang password" required minlength="8" autocomplete="new-password">
                            </div>

                            <button type="submit" class="btn btn-success w-100 fw-bold">
                                <i class="bi bi-person-check me-1"></i>Daftar
                            </button>
                        </form>

                        <p class="text-center text-muted mt-4 mb-0">
                            Sudah punya akun? <a href="login.php">Masuk di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
