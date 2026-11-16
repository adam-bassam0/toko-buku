<?php
// ============================================
// File    : app/config/app.php
// Deskripsi: Konfigurasi umum aplikasi.
// ============================================

define('APP_NAME', (string) env('APP_NAME', 'BukuKu'));
define('APP_ENV', (string) env('APP_ENV', 'dev'));

// Tampilkan error hanya saat pengembangan.
if (APP_ENV === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}
