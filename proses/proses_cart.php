<?php
// Memproses perubahan keranjang belanja berbasis PHP Session.
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart.php');
    exit;
}

// Validasi token agar form tidak dapat dipanggil dari situs lain.
$tokenForm = $_POST['csrf_token'] ?? '';
$tokenSesi = $_SESSION['csrf_token'] ?? '';
if (!$tokenSesi || !hash_equals($tokenSesi, $tokenForm)) {
    $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Permintaan tidak valid. Silakan coba kembali.'];
    header('Location: ../cart.php');
    exit;
}

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
                if ($aksi === 'tambah') {
                    $_SESSION['cart_toast'] = [
                        'title' => 'Berhasil Ditambahkan',
                        'text'  => 'Buku "' . htmlspecialchars($buku['judul']) . '" berhasil dimasukkan ke keranjang belanja!',
                        'total' => array_sum($_SESSION['cart'])
                    ];
                }
            }
        }
    }
} else {
    $_SESSION['cart_message'] = ['type' => 'danger', 'text' => 'Aksi keranjang tidak dikenal.'];
}

$koneksi->close();

// Dukung redirect kembali ke halaman pemanggil (misal: index.php atau detail.php).
// Hanya izinkan path internal agar tidak menjadi open redirect.
$returnUrl = trim((string) ($_POST['return_url'] ?? ''));
$returnValid = $returnUrl !== ''
    && !preg_match('#^[a-z][a-z0-9+.-]*:#i', $returnUrl) // tolak http:, javascript:, dll.
    && !str_starts_with($returnUrl, '//')                 // tolak //domain-luar
    && !str_contains($returnUrl, '\\');                    // tolak /\domain-luar

if ($returnValid) {
    // Form berada di root, sedangkan file ini ada di proses/.
    // Tambahkan ../ untuk path relatif dari root.
    if (!str_starts_with($returnUrl, '../') && !str_starts_with($returnUrl, '/')) {
        $returnUrl = '../' . $returnUrl;
    }
    header('Location: ' . $returnUrl);
    exit;
}

header('Location: ../cart.php');
exit;
