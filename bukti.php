<?php
// ============================================
// File    : bukti.php
// Deskripsi: Penyaji bukti pembayaran yang aman.
//            File asli berada di storage/ (tidak bisa diakses langsung).
//            Hanya admin atau pemilik pesanan yang boleh melihat.
// ============================================

require_once __DIR__ . '/app/bootstrap.php';

require_login('login.php');

$user      = auth_user();
$pesananId = (int) ($_GET['pesanan'] ?? 0);

if ($pesananId <= 0) {
    http_response_code(400);
    exit('Permintaan tidak valid.');
}

$stmt = $koneksi->prepare(
    'SELECT p.user_id, b.bukti
     FROM pesanan p
     INNER JOIN pembayaran b ON b.pesanan_id = p.id
     WHERE p.id = ?
     LIMIT 1'
);

if (!$stmt) {
    http_response_code(500);
    exit('Gagal membaca data pembayaran.');
}

$stmt->bind_param('i', $pesananId);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data || empty($data['bukti'])) {
    http_response_code(404);
    exit('Bukti pembayaran tidak ditemukan.');
}

// Hanya admin atau pemilik pesanan.
$pemilik = (int) $data['user_id'] === (int) $user['id'];
if (!auth_is_admin() && !$pemilik) {
    http_response_code(403);
    exit('Akses ditolak.');
}

$relative = str_replace('\\', '/', (string) $data['bukti']);
$prefix   = 'storage/uploads/bukti/';

if (!str_starts_with($relative, $prefix)) {
    http_response_code(400);
    exit('Path bukti tidak valid.');
}

$baseDir = realpath(APP_ROOT . '/storage/uploads/bukti');
$file    = realpath(APP_ROOT . '/' . $relative);

if ($baseDir === false || $file === false) {
    http_response_code(404);
    exit('File bukti tidak ditemukan.');
}

$baseDir = str_replace('\\', '/', $baseDir);
$file    = str_replace('\\', '/', $file);

// Cegah path traversal keluar dari folder bukti.
if (!str_starts_with($file, $baseDir . '/')) {
    http_response_code(400);
    exit('Path bukti tidak valid.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file);
$allowed = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($mime, $allowed, true)) {
    http_response_code(415);
    exit('Format bukti tidak didukung.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file));
header('X-Content-Type-Options: nosniff');
header('Content-Disposition: inline; filename="' . basename($file) . '"');
readfile($file);
exit;
