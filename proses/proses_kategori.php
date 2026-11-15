<?php
// ============================================
// File    : proses/proses_kategori.php
// Deskripsi: Menangani aksi CRUD kategori buku
//            (tambah, edit, hapus) khusus admin.
// ============================================

require_once __DIR__ . '/../config/auth.php';
require_admin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/kategori.php');
    exit;
}

$tokenForm = $_POST['csrf_token'] ?? '';
$tokenSesi = $_SESSION['csrf_token'] ?? '';
if (!$tokenSesi || !hash_equals($tokenSesi, $tokenForm)) {
    flash('danger', 'Permintaan tidak valid (token CSRF tidak cocok).');
    header('Location: ../admin/kategori.php');
    exit;
}

require_once __DIR__ . '/../config/koneksi.php';

$aksi = $_POST['aksi'] ?? '';

/**
 * Fungsi helper untuk menghasilkan URL slug yang bersih dan SEO-friendly
 */
function buat_slug(string $teks): string
{
    $slug = preg_replace('~[^\pL\d]+~u', '-', $teks);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
    $slug = preg_replace('~[^-\w]+~', '', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('~-+~', '-', $slug);
    $slug = strtolower($slug);

    return !empty($slug) ? $slug : 'kategori-' . substr(bin2hex(random_bytes(4)), 0, 6);
}

// -------------------------------------------------------------
// AKSI: TAMBAH KATEGORI
// -------------------------------------------------------------
if ($aksi === 'tambah') {
    $nama      = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '') {
        flash('danger', 'Nama kategori wajib diisi.');
        header('Location: ../admin/kategori.php');
        exit;
    }

    $slugBase = buat_slug($nama);
    $slug     = $slugBase;
    $counter  = 1;

    // Pastikan slug unik
    while (true) {
        $cek = $koneksi->prepare("SELECT id FROM kategori WHERE slug = ? LIMIT 1");
        $cek->bind_param("s", $slug);
        $cek->execute();
        $resCek = $cek->get_result();
        if ($resCek->num_rows === 0) {
            $cek->close();
            break;
        }
        $cek->close();
        $slug = $slugBase . '-' . (++$counter);
    }

    $stmt = $koneksi->prepare("INSERT INTO kategori (nama, slug, deskripsi) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nama, $slug, $deskripsi);

    if ($stmt->execute()) {
        flash('success', "Kategori '{$nama}' berhasil ditambahkan.");
    } else {
        flash('danger', 'Gagal menambahkan kategori. Silakan coba lagi.');
    }

    $stmt->close();
    header('Location: ../admin/kategori.php');
    exit;
}

// -------------------------------------------------------------
// AKSI: EDIT KATEGORI
// -------------------------------------------------------------
if ($aksi === 'edit') {
    $id        = (int) ($_POST['id'] ?? 0);
    $nama      = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($id <= 0 || $nama === '') {
        flash('danger', 'Data kategori tidak valid atau nama kategori kosong.');
        header('Location: ../admin/kategori.php');
        exit;
    }

    $slugBase = buat_slug($nama);
    $slug     = $slugBase;
    $counter  = 1;

    // Pastikan slug unik terhadap kategori selain ID ini
    while (true) {
        $cek = $koneksi->prepare("SELECT id FROM kategori WHERE slug = ? AND id != ? LIMIT 1");
        $cek->bind_param("si", $slug, $id);
        $cek->execute();
        $resCek = $cek->get_result();
        if ($resCek->num_rows === 0) {
            $cek->close();
            break;
        }
        $cek->close();
        $slug = $slugBase . '-' . (++$counter);
    }

    $stmt = $koneksi->prepare("UPDATE kategori SET nama = ?, slug = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nama, $slug, $deskripsi, $id);

    if ($stmt->execute()) {
        flash('success', "Kategori '{$nama}' berhasil diperbarui.");
    } else {
        flash('danger', 'Gagal memperbarui kategori.');
    }

    $stmt->close();
    header('Location: ../admin/kategori.php');
    exit;
}

// -------------------------------------------------------------
// AKSI: HAPUS KATEGORI
// -------------------------------------------------------------
if ($aksi === 'hapus') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        flash('danger', 'Kategori tidak valid.');
        header('Location: ../admin/kategori.php');
        exit;
    }

    // Ambil nama kategori untuk notifikasi
    $stmtGet = $koneksi->prepare("SELECT nama FROM kategori WHERE id = ? LIMIT 1");
    $stmtGet->bind_param("i", $id);
    $stmtGet->execute();
    $resGet = $stmtGet->get_result();
    $kategori = $resGet->fetch_assoc();
    $stmtGet->close();

    if (!$kategori) {
        flash('warning', 'Kategori tidak ditemukan.');
        header('Location: ../admin/kategori.php');
        exit;
    }

    // Hapus kategori. Relasi FK ON DELETE SET NULL akan otomatis mengosongkan kategori_id buku terkait tanpa menghapus bukunya
    $stmt = $koneksi->prepare("DELETE FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        flash('success', "Kategori '{$kategori['nama']}' berhasil dihapus. Buku terkait kini berstatus tanpa kategori.");
    } else {
        flash('danger', 'Gagal menghapus kategori.');
    }

    $stmt->close();
    header('Location: ../admin/kategori.php');
    exit;
}

// Aksi tidak dikenal
header('Location: ../admin/kategori.php');
exit;
