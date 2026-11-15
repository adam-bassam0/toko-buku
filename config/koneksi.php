<?php
// ============================================
// File    : config/koneksi.php
// Deskripsi: Konfigurasi dan koneksi database cerdas
//            dengan Auto-Provisioning & Auto-Migration.
// ============================================

define('APP_ENV', 'dev');

if (APP_ENV === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_toko_buku');
define('DB_PORT', 3306);

mysqli_report(MYSQLI_REPORT_OFF);

/**
 * Eksekusi file SQL jamak (multi-query) secara aman dan bersih
 */
function db_jalankan_file_sql(mysqli $koneksi, string $filePath): bool
{
    if (!file_exists($filePath)) {
        return false;
    }
    $sql = file_get_contents($filePath);
    if (empty(trim($sql))) {
        return false;
    }

    if ($koneksi->multi_query($sql)) {
        do {
            if ($result = $koneksi->store_result()) {
                $result->free();
            }
        } while ($koneksi->more_results() && $koneksi->next_result());
        return true;
    }
    return false;
}

// 1. Coba koneksi langsung ke database yang ditentukan
$koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($koneksi->connect_error) {
    // 2. Jika gagal karena database belum ada (error 1049) atau belum dibuat,
    //    coba hubungkan ke server MySQL dan buat database otomatis!
    $koneksiServer = @new mysqli(DB_HOST, DB_USER, DB_PASS, '', DB_PORT);

    if (!$koneksiServer->connect_error) {
        // Buat database otomatis
        $koneksiServer->query(
            "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        $koneksiServer->close();

        // Hubungkan kembali ke database yang baru dibuat
        $koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    }
}

// 3. Jika koneksi tetap gagal (misal: MySQL XAMPP belum aktif)
if (!$koneksi || $koneksi->connect_error) {
    $errCode = $koneksi ? $koneksi->connect_errno : 2002;
    $errMsg  = $koneksi ? $koneksi->connect_error : 'Tidak dapat terhubung ke MySQL pada port 3306';

    // Tampilkan antarmuka panduan koneksi database yang modern
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Belum Terhubung — BukuKu</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #e2e8f0;
                padding: 20px;
            }
            .card-setup {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 20px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                max-width: 580px;
                width: 100%;
                overflow: hidden;
            }
            .step-card {
                background: rgba(255, 255, 255, 0.04);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 12px;
                padding: 14px 16px;
                margin-bottom: 12px;
            }
        </style>
    </head>
    <body>
        <div class="card-setup p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-20 text-warning mb-3" style="width: 64px; height: 64px;">
                    <i class="bi bi-database-exclamation fs-1"></i>
                </div>
                <h3 class="fw-bold text-white mb-2">Koneksi Database Membutuhkan MySQL</h3>
                <p class="text-white-50 small mb-0">Website BukuKu siap menghubungkan database otomatis setelah MySQL dijalankan.</p>
            </div>

            <div class="step-card">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-primary rounded-circle p-2 px-3 fw-bold">1</span>
                    <div>
                        <div class="fw-bold text-white">Jalankan MySQL di XAMPP</div>
                        <div class="small text-white-50">Buka <strong>XAMPP Control Panel</strong>, lalu klik tombol <strong>Start</strong> di sebelah modul <strong>MySQL</strong>.</div>
                    </div>
                </div>
            </div>

            <div class="step-card">
                <div class="d-flex align-items-start gap-3">
                    <span class="badge bg-primary rounded-circle p-2 px-3 fw-bold">2</span>
                    <div>
                        <div class="fw-bold text-white">Otomatis Terhubung &amp; Terisi</div>
                        <div class="small text-white-50">Setelah MySQL aktif, sistem akan langsung membuat database <code><?= DB_NAME ?></code> dan mengimpor seluruh tabel buku secara otomatis.</div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 my-4 small">
                <div class="text-muted mb-1"><i class="bi bi-info-circle me-1"></i>Info Konfigurasi:</div>
                <div>Host: <code><?= DB_HOST ?>:<?= DB_PORT ?></code> &bull; User: <code><?= DB_USER ?></code> &bull; Database: <code><?= DB_NAME ?></code></div>
                <?php if (APP_ENV === 'dev'): ?>
                    <div class="text-danger mt-1 small">Detail: <?= htmlspecialchars($errMsg) ?> (Kode: <?= $errCode ?>)</div>
                <?php endif; ?>
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-warning btn-lg fw-bold" onclick="window.location.reload();">
                    <i class="bi bi-arrow-clockwise me-2"></i>Coba Hubungkan Sekarang
                </button>
                <a href="setup.php" class="btn btn-outline-light btn-sm mt-1">
                    <i class="bi bi-sliders me-1"></i>Buka Database Setup Assistant
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Atur encoding karakter ke UTF-8mb4
$koneksi->set_charset("utf8mb4");

// 4. AUTO-INITIALIZATION: Periksa apakah tabel buku & kategori sudah ada
//    Jika belum, otomatis buat semua tabel dan isi data contoh!
$cekTabel = $koneksi->query("SHOW TABLES LIKE 'buku'");
if (!$cekTabel || $cekTabel->num_rows === 0) {
    $schemaFile = __DIR__ . '/../database/schema.sql';
    db_jalankan_file_sql($koneksi, $schemaFile);
} else {
    // Periksa apakah tabel kategori sudah ada
    $cekKat = $koneksi->query("SHOW TABLES LIKE 'kategori'");
    if (!$cekKat || $cekKat->num_rows === 0) {
        $migrasiKat = __DIR__ . '/../database/migrasi_kategori.sql';
        db_jalankan_file_sql($koneksi, $migrasiKat);
    }
}
