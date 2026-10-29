<?php
// ============================================
// File    : proses/proses_tambah.php
// Deskripsi: Memproses data dari form tambah buku.
//            Menerima data POST, validasi,
//            lalu INSERT ke database.
// ============================================

// Skrip ini hanya boleh dipanggil via POST, bukan diakses langsung via browser
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/tambah.php");
    exit;
}

require_once '../config/koneksi.php';

// --- Ambil dan Bersihkan Data dari Form ---
// trim() menghapus spasi di awal/akhir string
$judul    = trim($_POST['judul']    ?? '');
$penulis  = trim($_POST['penulis']  ?? '');
$penerbit = trim($_POST['penerbit'] ?? '');
$harga    = (int) ($_POST['harga'] ?? 0); // Konversi ke integer
$stok     = (int) ($_POST['stok']  ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$cover    = trim($_POST['cover']    ?? '');

// --- Validasi Sederhana: Pastikan field wajib tidak kosong ---
if (empty($judul) || empty($penulis) || empty($penerbit)) {
    // Redirect kembali ke form jika validasi gagal
    header("Location: ../admin/tambah.php?error=kolom_wajib");
    exit;
}

// --- Eksekusi Query INSERT menggunakan Prepared Statement ---
// Tanda "?" adalah placeholder yang akan diisi oleh bind_param
$stmt = $koneksi->prepare(
    "INSERT INTO buku (judul, penulis, penerbit, harga, stok, deskripsi, cover)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

// Ikat parameter: s=string, i=integer
// Urutan tipe harus sesuai dengan urutan placeholder "?"
$stmt->bind_param("sssiisd", $judul, $penulis, $penerbit, $harga, $stok, $deskripsi, $cover);

// Jalankan query
if ($stmt->execute()) {
    // Berhasil: redirect ke admin dengan pesan sukses
    header("Location: ../admin/index.php?pesan=tambah_sukses");
} else {
    // Gagal: redirect kembali ke form dengan pesan error
    header("Location: ../admin/tambah.php?error=db_error");
}

// Tutup prepared statement dan koneksi
$stmt->close();
$koneksi->close();
exit;

