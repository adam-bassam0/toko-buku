<?php
// ============================================
// File    : proses/proses_hapus.php
// Deskripsi: Memproses penghapusan data buku.
//            Menerima ID via parameter GET,
//            lalu DELETE dari database.
// ============================================

require_once '../config/koneksi.php';

// Ambil dan validasi ID dari parameter URL
// Contoh URL: proses_hapus.php?id=5
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Pastikan ID valid sebelum proses hapus
if ($id <= 0) {
    header("Location: ../admin/index.php");
    exit;
}

// --- Cek apakah buku benar-benar ada sebelum dihapus ---
$cek = $koneksi->prepare("SELECT id FROM buku WHERE id = ? LIMIT 1");
$cek->bind_param("i", $id);
$cek->execute();
$cek->store_result();

if ($cek->num_rows === 0) {
    // Buku tidak ditemukan, redirect dengan pesan
    $cek->close();
    header("Location: ../admin/index.php?pesan=tidak_ditemukan");
    exit;
}
$cek->close();

// --- Eksekusi Query DELETE menggunakan Prepared Statement ---
// WHERE id = ? memastikan hanya satu baris yang dihapus
$stmt = $koneksi->prepare("DELETE FROM buku WHERE id = ?");
$stmt->bind_param("i", $id); // "i" = tipe integer untuk ID
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Berhasil dihapus: redirect ke admin dengan pesan sukses
    header("Location: ../admin/index.php?pesan=hapus_sukses");
} else {
    // Tidak ada baris yang terpengaruh (kemungkinan error)
    header("Location: ../admin/index.php?pesan=hapus_gagal");
}

$stmt->close();
$koneksi->close();
exit;

