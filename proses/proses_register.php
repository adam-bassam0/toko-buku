<?php
// ============================================
// File    : proses/proses_register.php
// Deskripsi: Validasi & simpan akun baru.
//            - Email divalidasi & dicek unik (prepared)
//            - Password di-hash bcrypt sebelum disimpan
//            - Role dipaksa 'user' dari sisi server
// ============================================

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

csrf_check($_POST['csrf_token'] ?? null, '../register.php');

$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$konfirmasi = $_POST['password_konfirmasi'] ?? '';

// ---------- Validasi ----------
if ($nama === '' || $email === '' || $password === '') {
    flash('danger', 'Semua kolom wajib diisi.');
    header('Location: ../register.php');
    exit;
}

if (mb_strlen($nama) > 100) {
    flash('danger', 'Nama maksimal 100 karakter.');
    header('Location: ../register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('danger', 'Format email tidak valid.');
    header('Location: ../register.php');
    exit;
}

if (strlen($password) < 8) {
    flash('danger', 'Password minimal 8 karakter.');
    header('Location: ../register.php');
    exit;
}

if ($password !== $konfirmasi) {
    flash('danger', 'Konfirmasi password tidak cocok.');
    header('Location: ../register.php');
    exit;
}

// ---------- Cek email sudah dipakai? ----------
$cek = $koneksi->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$cek->bind_param('s', $email);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    $cek->close();
    flash('warning', 'Email tersebut sudah terdaftar. Silakan masuk.');
    header('Location: ../register.php');
    exit;
}
$cek->close();

// ---------- Simpan akun ----------
$hash = password_hash($password, PASSWORD_DEFAULT); // bcrypt + salt otomatis
$role = 'user'; // dari server, bukan dari input form

$stmt = $koneksi->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $nama, $email, $hash, $role);

if (!$stmt->execute()) {
    $stmt->close();
    $koneksi->close();
    flash('danger', 'Pendaftaran gagal. Silakan coba lagi.');
    header('Location: ../register.php');
    exit;
}

$stmt->close();
$koneksi->close();

flash('success', 'Akun berhasil dibuat. Silakan masuk.');
header('Location: ../login.php');
exit;
