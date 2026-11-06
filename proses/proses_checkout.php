<?php
// ============================================
// File    : proses/proses_checkout.php
// Deskripsi: Membuat pesanan + mengurangi stok
//            DALAM SATU TRANSAKSI (ACID).
//
// A - Atomicity : semua INSERT/UPDATE berhasil atau dibatalkan semua.
// C - Consistency: stok tidak boleh minus; foreign key dijaga.
// I - Isolation  : baris buku dikunci (SELECT ... FOR UPDATE)
//                  agar dua pembeli tidak mengambil stok yang sama.
// D - Durability : setelah COMMIT, InnoDB menyimpan permanen.
// ============================================

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/koneksi.php';

require_login('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cart.php');
    exit;
}

csrf_check($_POST['csrf_token'] ?? null, '../cart.php');

$user = auth_user();

// Metode pembayaran hanya boleh salah satu dari dua nilai ini.
$metode = $_POST['metode'] ?? '';
if (!in_array($metode, ['qris', 'transfer'], true)) {
    flash('danger', 'Metode pembayaran tidak valid.');
    header('Location: ../checkout.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    flash('warning', 'Keranjang masih kosong.');
    header('Location: ../cart.php');
    exit;
}

// Mulai transaksi.
$koneksi->begin_transaction();

try {
    $items = [];
    $total = 0;

    // ---------- Fase 1: baca & kunci stok (Isolation) ----------
    foreach ($_SESSION['cart'] as $id => $jumlah) {
        $id     = (int) $id;
        $jumlah = (int) $jumlah;

        if ($id <= 0 || $jumlah <= 0) {
            continue;
        }

        $stmt = $koneksi->prepare('SELECT id, judul, harga, stok FROM buku WHERE id = ? LIMIT 1 FOR UPDATE');
        if (!$stmt) {
            throw new RuntimeException('Gagal menyiapkan query stok.');
        }
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new RuntimeException('Gagal membaca data stok.');
        }
        $buku = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$buku) {
            throw new RuntimeException('Ada buku di keranjang yang sudah tidak tersedia.');
        }

        if ((int) $buku['stok'] < $jumlah) {
            throw new RuntimeException('Stok "' . $buku['judul'] . '" tidak mencukupi (tersisa ' . (int) $buku['stok'] . ').');
        }

        $harga    = (float) $buku['harga'];
        $subtotal = $harga * $jumlah;

        $items[] = [
            'id'       => (int) $buku['id'],
            'judul'    => $buku['judul'],
            'harga'    => $harga,
            'jumlah'   => $jumlah,
            'subtotal' => $subtotal,
        ];
        $total += $subtotal;
    }

    if (!$items) {
        throw new RuntimeException('Tidak ada item valid untuk dipesan.');
    }

    // ---------- Fase 2: buat header pesanan ----------
    $status = 'pending';
    $stmtPesanan = $koneksi->prepare('INSERT INTO pesanan (user_id, total, status) VALUES (?, ?, ?)');
    if (!$stmtPesanan) {
        throw new RuntimeException('Gagal menyiapkan query pesanan.');
    }
    $userId = (int) $user['id'];
    $stmtPesanan->bind_param('ids', $userId, $total, $status);
    if (!$stmtPesanan->execute()) {
        throw new RuntimeException('Gagal membuat pesanan.');
    }
    $pesananId = (int) $koneksi->insert_id;
    $stmtPesanan->close();

    // ---------- Fase 3: buat tagihan pembayaran ----------
    $kodeBayar   = 'BK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    $statusBayar = 'menunggu';

    $stmtBayar = $koneksi->prepare(
        'INSERT INTO pembayaran (pesanan_id, metode, jumlah, status, kode_bayar)
         VALUES (?, ?, ?, ?, ?)'
    );
    if (!$stmtBayar) {
        throw new RuntimeException('Gagal menyiapkan data pembayaran.');
    }
    $stmtBayar->bind_param('isdss', $pesananId, $metode, $total, $statusBayar, $kodeBayar);
    if (!$stmtBayar->execute()) {
        throw new RuntimeException('Gagal membuat tagihan pembayaran.');
    }
    $stmtBayar->close();

    // ---------- Fase 4: rincian pesanan + kurangi stok ----------
    $stmtDetail = $koneksi->prepare(
        'INSERT INTO detail_pesanan (pesanan_id, buku_id, judul, harga, jumlah, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmtUpdateStok = $koneksi->prepare('UPDATE buku SET stok = stok - ? WHERE id = ? AND stok >= ?');

    if (!$stmtDetail || !$stmtUpdateStok) {
        throw new RuntimeException('Gagal menyiapkan query rincian.');
    }

    foreach ($items as $item) {
        $bukuId   = $item['id'];
        $judul    = $item['judul'];
        $harga    = $item['harga'];
        $jumlah   = $item['jumlah'];
        $subtotal = $item['subtotal'];

        $stmtDetail->bind_param('iisdid', $pesananId, $bukuId, $judul, $harga, $jumlah, $subtotal);
        if (!$stmtDetail->execute()) {
            throw new RuntimeException('Gagal menyimpan rincian pesanan.');
        }

        // Syarat "stok >= jumlah" membuat stok tidak mungkin minus (Consistency).
        $stmtUpdateStok->bind_param('iii', $jumlah, $bukuId, $jumlah);
        if (!$stmtUpdateStok->execute()) {
            throw new RuntimeException('Gagal memperbarui stok.');
        }
        if ($stmtUpdateStok->affected_rows !== 1) {
            throw new RuntimeException('Stok berubah saat checkout. Silakan coba lagi.');
        }
    }

    $stmtDetail->close();
    $stmtUpdateStok->close();

    // ---------- COMMIT: semua perubahan disimpan permanen ----------
    $koneksi->commit();

    $_SESSION['cart'] = [];
    csrf_rotate();

    flash('success', 'Pesanan #' . $pesananId . ' berhasil dibuat. Silakan selesaikan pembayaran.');
    header('Location: ../pembayaran.php?pesanan=' . $pesananId);
    exit;

} catch (Throwable $e) {
    // ---------- ROLLBACK: batalkan semua perubahan ----------
    $koneksi->rollback();

    flash('danger', 'Checkout dibatalkan: ' . $e->getMessage());
    header('Location: ../cart.php');
    exit;
}
