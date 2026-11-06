<?php
// ============================================
// File    : proses/proses_logout.php
// Deskripsi: Keluar dari akun (POST + CSRF),
//            menghapus session & cookie session.
// ============================================

require_once __DIR__ . '/../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

csrf_check($_POST['csrf_token'] ?? null, '../index.php');

// Kosongkan data session.
$_SESSION = [];

// Hapus cookie session di browser.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Session baru hanya untuk membawa pesan flash.
session_start();
flash('success', 'Anda telah keluar. Sampai jumpa!');

header('Location: ../index.php');
exit;
