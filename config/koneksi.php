<?php
// ============================================
// File    : config/koneksi.php
// Deskripsi: Konfigurasi dan koneksi database
//            menggunakan MySQLi Object-Oriented.
// ============================================

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
    // Hentikan skrip dan tampilkan pesan error jika koneksi gagal
    die("Koneksi database gagal. Pastikan MySQL XAMPP sudah aktif dan database '" . DB_NAME . "' tersedia.");
}

// Atur encoding karakter agar mendukung karakter UTF-8 (huruf Indonesia, emoji, dsb.)
$koneksi->set_charset("utf8mb4");
