<?php
// ============================================

require_once __DIR__ . '/../config/auth.php';
require_admin('../login.php');
// File    : proses/proses_edit.php
// Deskripsi: Memproses data dari form edit buku.
//            Menerima data POST, validasi,
//            lalu UPDATE data di database.
// ============================================

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/index.php");
    exit;
}

$tokenForm = $_POST['csrf_token'] ?? '';
$tokenSesi = $_SESSION['csrf_token'] ?? '';
if (!$tokenSesi || !hash_equals($tokenSesi, $tokenForm)) {
    header("Location: ../admin/index.php?pesan=permintaan_tidak_valid");
    exit;
}

require_once '../config/koneksi.php';

// --- Ambil dan Validasi ID ---
// ID disimpan di hidden input pada form edit
$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    // ID tidak valid, redirect kembali ke daftar admin
    header("Location: ../admin/index.php");
    exit;
}

// --- Ambil dan Bersihkan Data dari Form ---
$judul     = trim($_POST['judul']     ?? '');
$penulis   = trim($_POST['penulis']   ?? '');
$penerbit  = trim($_POST['penerbit']  ?? '');
$harga     = (int) ($_POST['harga']  ?? 0);
$stok      = (int) ($_POST['stok']   ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$cover     = trim($_POST['cover']     ?? '');

// --- Validasi: Field wajib tidak boleh kosong ---
if (
    $judul === '' || $penulis === '' || $penerbit === '' ||
    $harga < 0 || $stok < 0 ||
    ($cover !== '' && filter_var($cover, FILTER_VALIDATE_URL) === false)
) {
    header("Location: ../admin/edit.php?id={$id}&error=kolom_wajib");
    exit;
}

// --- Eksekusi Query UPDATE menggunakan Prepared Statement ---
// Klausa WHERE id = ? memastikan HANYA buku dengan ID tersebut yang diupdate
// Format string bind_param: s=string, i=integer.
// "sssiissi": judul, penulis, penerbit, harga, stok, deskripsi, cover, id
$stmt = $koneksi->prepare(
    "UPDATE buku
     SET judul = ?, penulis = ?, penerbit = ?, harga = ?, stok = ?, deskripsi = ?, cover = ?
     WHERE id = ?"
);

// Ikat semua parameter sesuai urutan placeholder "?"
// Tipe: s,s,s = judul,penulis,penerbit | i,i = harga,stok | s = deskripsi | s = cover | i = id
$stmt->bind_param("sssiissi",
    $judul, $penulis, $penerbit,
    $harga, $stok,
    $deskripsi, $cover,
    $id
);

// Jalankan query UPDATE
if ($stmt->execute()) {
    // Berhasil diupdate: redirect ke admin dengan pesan sukses
    header("Location: ../admin/index.php?pesan=edit_sukses");
} else {
    // Gagal: redirect ke form edit dengan pesan error
    header("Location: ../admin/edit.php?id={$id}&error=db_error");
}

$stmt->close();
$koneksi->close();
exit;
