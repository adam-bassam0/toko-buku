<?php
// ============================================
// File    : proses/proses_login.php
// Deskripsi: Verifikasi email & password.
//            - Prepared statement (anti SQL Injection)
//            - password_verify() untuk cek hash
//            - session_regenerate_id() (anti session fixation)
// ============================================

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

// Hanya boleh diakses lewat POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

csrf_check($_POST['csrf_token'] ?? null, '../login.php');

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    flash('danger', 'Email dan password wajib diisi.');
    header('Location: ../login.php');
    exit;
}

// Prepared statement: input user TIDAK pernah disambung langsung ke query.
$stmt = $koneksi->prepare('SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Pesan sengaja dibuat umum agar tidak membocorkan email mana yang terdaftar.
if (!$user || !password_verify($password, $user['password'])) {
    flash('danger', 'Email atau password salah.');
    header('Location: ../login.php');
    exit;
}

// Cegah session fixation: ID session lama dibuang, diganti yang baru.
session_regenerate_id(true);
csrf_rotate();

// Simpan data minimal ke session (JANGAN simpan password).
$_SESSION['user'] = [
    'id'    => (int) $user['id'],
    'nama'  => $user['nama'],
    'email' => $user['email'],
    'role'  => $user['role'],
];

// Cek apakah hash perlu di-upgrade (algoritma password default berubah).
if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $hashBaru = password_hash($password, PASSWORD_DEFAULT);
    $update = $koneksi->prepare('UPDATE users SET password = ? WHERE id = ?');
    $update->bind_param('si', $hashBaru, $user['id']);
    $update->execute();
    $update->close();
}

$koneksi->close();

flash('success', 'Selamat datang, ' . $user['nama'] . '!');

// Admin langsung diarahkan ke panel admin; pelanggan ke katalog.
if ($user['role'] === 'admin') {
    header('Location: ../admin/index.php');
} else {
    header('Location: ../index.php');
}
exit;
