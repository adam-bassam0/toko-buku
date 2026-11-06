<?php
// ============================================
// File    : config/auth.php
// Deskripsi: Fondasi autentikasi & keamanan.
//            - Memulai session dengan cookie aman
//            - Helper CSRF token
//            - Helper pesan flash
//            - Helper cek login / role admin
//            - Penjaga halaman (require_login / require_admin)
// ============================================

// Mulai session sekali saja, dengan cookie yang lebih aman.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,   // JavaScript tidak bisa membaca cookie session
        'samesite' => 'Lax',  // Mengurangi risiko CSRF lintas situs
    ]);
    session_start();
}

// ---------- CSRF ----------

/**
 * Ambil token CSRF milik session; buat baru jika belum ada.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifikasi token CSRF dari form. Jika tidak cocok, redirect dengan pesan.
 */
function csrf_check(?string $token, string $redirect): void
{
    $tokenSesi = $_SESSION['csrf_token'] ?? '';

    if (!$tokenSesi || !is_string($token) || !hash_equals($tokenSesi, $token)) {
        flash('danger', 'Permintaan tidak valid (token CSRF tidak cocok). Silakan coba lagi.');
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Ganti token CSRF (dipakai setelah login/logout).
 */
function csrf_rotate(): void
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---------- Pesan Flash ----------

/**
 * Simpan pesan singkat yang akan ditampilkan sekali di halaman berikutnya.
 */
function flash(string $type, string $text): void
{
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
}

/**
 * Ambil sekaligus hapus pesan flash.
 */
function flash_take(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// ---------- Autentikasi ----------

/**
 * Data user yang sedang login, atau null jika belum login.
 */
function auth_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_check(): bool
{
    return auth_user() !== null;
}

function auth_is_admin(): bool
{
    return (auth_user()['role'] ?? '') === 'admin';
}

/**
 * Wajib login. Jika belum, arahkan ke halaman login.
 * Path redirect relatif terhadap file pemanggil.
 */
function require_login(string $redirect = 'login.php'): void
{
    if (!auth_check()) {
        flash('warning', 'Silakan masuk terlebih dahulu untuk melanjutkan.');
        header('Location: ' . $redirect);
        exit;
    }
}

/**
 * Wajib login DAN role admin. Dipakai di semua halaman admin.
 */
function require_admin(string $redirect = '../login.php'): void
{
    if (!auth_check()) {
        flash('warning', 'Halaman admin hanya bisa diakses setelah masuk.');
        header('Location: ' . $redirect);
        exit;
    }

    if (!auth_is_admin()) {
        flash('danger', 'Akun Anda bukan admin. Akses ditolak.');
        header('Location: ' . $redirect);
        exit;
    }
}
