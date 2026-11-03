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

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_toko_buku');

// Jangan biarkan kegagalan koneksi langsung menjadi fatal error pada PHP 8+.
mysqli_report(MYSQLI_REPORT_OFF);

// Buat objek koneksi MySQLi
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Periksa apakah koneksi berhasil atau tidak
if ($koneksi->connect_error) {
    if (APP_ENV === 'dev') {
        die("Koneksi database GAGAL: " . $koneksi->connect_error);
    }
    die("Koneksi database gagal. Pastikan MySQL XAMPP sudah aktif dan database tersedia.");
}

// Atur encoding karakter agar mendukung karakter UTF-8 (huruf Indonesia, emoji, dsb.)
$koneksi->set_charset("utf8mb4");

