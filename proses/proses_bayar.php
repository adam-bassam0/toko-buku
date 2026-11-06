<?php
// ============================================
// File    : proses/proses_bayar.php
// Deskripsi: Konfirmasi pembayaran pesanan.
//            - Hanya pemilik pesanan (cek user_id)
//            - Update pembayaran & pesanan dalam satu transaksi
//            - Opsional upload bukti (divalidasi tipe & ukuran)
// ============================================

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

require_login('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pesanan.php');
    exit;
}

csrf_check($_POST['csrf_token'] ?? null, '../pesanan.php');

$user      = auth_user();
$pesananId = (int) ($_POST['pesanan'] ?? 0);

if ($pesananId <= 0) {
    flash('danger', 'Pesanan tidak valid.');
    header('Location: ../pesanan.php');
    exit;
}

// Pastikan pesanan memang milik user yang login.
$stmt = $koneksi->prepare(
    'SELECT p.id, p.status, b.id AS pembayaran_id, b.status AS status_bayar, b.metode
     FROM pesanan p
     LEFT JOIN pembayaran b ON b.pesanan_id = p.id
     WHERE p.id = ? AND p.user_id = ?
     LIMIT 1'
);
$stmt->bind_param('ii', $pesananId, $user['id']);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data || !$data['pembayaran_id']) {
    flash('danger', 'Data pembayaran tidak ditemukan.');
    header('Location: ../pesanan.php');
    exit;
}

// Sudah lunas? Tidak perlu diproses lagi.
if ($data['status_bayar'] === 'lunas' || in_array($data['status'], ['dibayar', 'dikirim', 'selesai'], true)) {
    flash('info', 'Pesanan ini sudah lunas.');
    header('Location: ../pembayaran.php?pesanan=' . $pesananId);
    exit;
}

$buktiRelatif = null;
$fileDibuat   = null;

try {
    // ---------- Upload bukti (opsional) ----------
    if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['bukti'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload bukti gagal. Silakan coba lagi.');
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            throw new RuntimeException('Ukuran bukti maksimal 2 MB.');
        }

        // Cek tipe file dari isinya, bukan hanya nama file.
        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($file['tmp_name']);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowed[$mime])) {
            throw new RuntimeException('Format bukti harus JPG, PNG, atau WebP.');
        }

        $folder = __DIR__ . '/../assets/uploads/bukti';
        if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
            throw new RuntimeException('Folder upload tidak dapat dibuat.');
        }

        $namaFile = 'bukti_' . $pesananId . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];

        if (!move_uploaded_file($file['tmp_name'], $folder . '/' . $namaFile)) {
            throw new RuntimeException('Gagal menyimpan bukti pembayaran.');
        }

        $fileDibuat   = $folder . '/' . $namaFile;
        $buktiRelatif = 'assets/uploads/bukti/' . $namaFile;
    }

    // ---------- Update status dalam transaksi ----------
    $koneksi->begin_transaction();

    // Kunci baris pembayaran agar tidak diproses dua kali bersamaan.
    $lock = $koneksi->prepare('SELECT status FROM pembayaran WHERE pesanan_id = ? LIMIT 1 FOR UPDATE');
    if (!$lock) {
        throw new RuntimeException('Gagal menyiapkan query pembayaran.');
    }
    $lock->bind_param('i', $pesananId);
    if (!$lock->execute()) {
        throw new RuntimeException('Gagal membaca status pembayaran.');
    }
    $statusSaatIni = $lock->get_result()->fetch_assoc();
    $lock->close();

    if (!$statusSaatIni) {
        throw new RuntimeException('Data pembayaran tidak ditemukan.');
    }

    if ($statusSaatIni['status'] === 'lunas') {
        // Sudah diproses (misalnya dua kali klik) — transaksi tetap aman.
        $koneksi->commit();
        if ($fileDibuat) {
            @unlink($fileDibuat);
        }
        flash('info', 'Pesanan ini sudah lunas.');
        header('Location: ../pembayaran.php?pesanan=' . $pesananId);
        exit;
    }

    $statusLunas = 'lunas';
    $updBayar = $koneksi->prepare(
        'UPDATE pembayaran
         SET status = ?, dibayar_pada = NOW(), bukti = COALESCE(?, bukti)
         WHERE pesanan_id = ?'
    );
    if (!$updBayar) {
        throw new RuntimeException('Gagal menyiapkan update pembayaran.');
    }
    $updBayar->bind_param('ssi', $statusLunas, $buktiRelatif, $pesananId);
    if (!$updBayar->execute()) {
        throw new RuntimeException('Gagal memperbarui status pembayaran.');
    }
    $updBayar->close();

    $statusPesanan = 'dibayar';
    $updPesanan = $koneksi->prepare('UPDATE pesanan SET status = ? WHERE id = ? AND user_id = ?');
    if (!$updPesanan) {
        throw new RuntimeException('Gagal menyiapkan update pesanan.');
    }
    $userId = (int) $user['id'];
    $updPesanan->bind_param('sii', $statusPesanan, $pesananId, $userId);
    if (!$updPesanan->execute()) {
        throw new RuntimeException('Gagal memperbarui status pesanan.');
    }
    $updPesanan->close();

    $koneksi->commit();

    flash('success', 'Terima kasih! Pembayaran pesanan #' . $pesananId . ' berhasil dikonfirmasi.');
    header('Location: ../pembayaran.php?pesanan=' . $pesananId);
    exit;

} catch (Throwable $e) {
    // Batalkan transaksi bila sudah dimulai.
    try {
        $koneksi->rollback();
    } catch (Throwable $ignored) {
        // Tidak ada transaksi aktif.
    }

    // Hapus file yang sudah terlanjur diunggah.
    if ($fileDibuat && file_exists($fileDibuat)) {
        @unlink($fileDibuat);
    }

    flash('danger', 'Konfirmasi pembayaran gagal: ' . $e->getMessage());
    header('Location: ../pembayaran.php?pesanan=' . $pesananId);
    exit;
}
