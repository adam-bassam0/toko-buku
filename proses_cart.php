<?php
// Memproses perubahan keranjang belanja berbasis PHP Session.
require_once __DIR__ . '/config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart.php');
    exit;
}

// Validasi token agar form tidak dapat dipanggil dari situs lain.
$tokenForm = $_POST['csrf_token'] ?? '';
$tokenSesi = $_SESSION['csrf_token'] ?? '';
if (!$tokenSesi || !hash_equals($tokenSesi, $tokenForm)) {
    $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Permintaan tidak valid. Silakan coba kembali.'];
    header('Location: cart.php');
    exit;
}

require_once 'config/koneksi.php';
$_SESSION['cart'] ??= [];
$aksi = $_POST['aksi'] ?? '';
$idBuku = (int) ($_POST['id_buku'] ?? 0);

if ($aksi === 'kosongkan') {
    $_SESSION['cart'] = [];
    $_SESSION['cart_message'] = ['type' => 'success', 'text' => 'Keranjang berhasil dikosongkan.'];
} elseif ($aksi === 'hapus') {
    unset($_SESSION['cart'][$idBuku]);
    $_SESSION['cart_message'] = ['type' => 'success', 'text' => 'Buku berhasil dihapus dari keranjang.'];
} elseif ($aksi === 'tambah' || $aksi === 'ubah') {
    if ($idBuku <= 0) {
        $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Buku tidak valid.'];
    } else {
        $stmt = $koneksi->prepare('SELECT id, judul, stok FROM buku WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $idBuku);
        $stmt->execute();
        $buku = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$buku) {
            unset($_SESSION['cart'][$idBuku]);
            $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Buku tidak ditemukan.'];
        } else {
            $stok = (int) $buku['stok'];
            $jumlahLama = (int) ($_SESSION['cart'][$idBuku] ?? 0);
            $jumlahBaru = $aksi === 'tambah'
                ? $jumlahLama + 1
                : (int) ($_POST['jumlah'] ?? 1);

            if ($jumlahBaru < 1) {
                unset($_SESSION['cart'][$idBuku]);
                $_SESSION['cart_message'] = ['type' => 'success', 'text' => 'Buku dihapus dari keranjang.'];
            } elseif ($stok < 1) {
                unset($_SESSION['cart'][$idBuku]);
                $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Stok buku sedang habis.'];
            } elseif ($jumlahBaru > $stok) {
                $_SESSION['cart'][$idBuku] = min($jumlahLama, $stok);
                if ($_SESSION['cart'][$idBuku] < 1) unset($_SESSION['cart'][$idBuku]);
                $_SESSION['cart_message'] = ['type' => 'warning', 'text' => 'Jumlah melebihi stok tersedia (' . $stok . ' unit).'];
            } else {
                $_SESSION['cart'][$idBuku] = $jumlahBaru;
                $_SESSION['cart_message'] = ['type' => 'success', 'text' => $aksi === 'tambah' ? 'Buku ditambahkan ke keranjang.' : 'Jumlah buku berhasil diperbarui.'];
            }
        }
    }
} else {
    $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Aksi keranjang tidak dikenal.'];
}

$koneksi->close();
header('Location: cart.php');
exit;
