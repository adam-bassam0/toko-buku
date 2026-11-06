<?php
// ============================================
// File    : config/koneksi.php
// Deskripsi: Konfigurasi dan koneksi database
//            menggunakan MySQLi Object-Oriented.
// ============================================

// ===== Environment =====
// 'dev' saat pengembangan (error ditampilkan untuk debug),
// 'prod' saat sudah online (error disembunyikan dari pengguna).
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

// Jangan biarkan kegagalan koneksi langsung menjadi fatal error pada PHP 8+.
mysqli_report(MYSQLI_REPORT_OFF);

// Buat objek koneksi MySQLi.
// Pakai 127.0.0.1 (bukan "localhost") agar tidak salah resolusi ke IPv6 (::1)
// yang sering menyebabkan "connection refused" di Windows.
$koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Periksa apakah koneksi berhasil atau tidak
if ($koneksi->connect_error) {
    if (APP_ENV === 'dev') {
        die(
            'Koneksi database GAGAL: ' . $koneksi->connect_error . '<br>'
            . 'Periksa: (1) MySQL sudah dijalankan dari XAMPP Control Panel, '
            . '(2) host ' . DB_HOST . ' port ' . DB_PORT . ' dapat diakses, '
            . '(3) database \'' . DB_NAME . '\' sudah dibuat.'
        );
    }
    die("Koneksi database gagal. Pastikan MySQL XAMPP sudah aktif dan database '" . DB_NAME . "' tersedia.");
}

// Atur encoding karakter agar mendukung karakter UTF-8 (huruf Indonesia, emoji, dsb.)
$koneksi->set_charset("utf8mb4");
