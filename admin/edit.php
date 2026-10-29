<?php
// ============================================
// File    : admin/edit.php
// Deskripsi: Form untuk mengedit data buku.
//            Data lama diambil dari database
//            berdasarkan ID dan diisi ke form.
// ============================================

require_once '../config/koneksi.php';

// Ambil dan validasi ID dari parameter URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    // Jika ID tidak valid, redirect ke halaman admin
    header("Location: index.php");
    exit;
}

// Cari data buku berdasarkan ID menggunakan prepared statement
$stmt = $koneksi->prepare("SELECT * FROM buku WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Jika buku tidak ditemukan, redirect ke admin
    header("Location: index.php");
    exit;
}

// Simpan data buku ke variabel untuk ditampilkan di form
$buku = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Admin BukuKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- ===== NAVBAR ADMIN ===== -->
    <nav class="navbar navbar-dark shadow-sm" style="background-color: #0f3460;">
        <div class="container-fluid px-4">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi bi-shield-lock me-2"></i>Admin BukuKu
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </nav>

    <!-- ===== KONTEN FORM EDIT ===== -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card form-card p-4 p-md-5">
                    <h4 class="fw-bold mb-1">
                        <i class="bi bi-pencil-square me-2 text-warning"></i>Edit Data Buku
                    </h4>
                    <!-- Tampilkan judul buku yang sedang diedit -->
                    <p class="text-muted mb-4">Mengedit: <em><?= htmlspecialchars($buku['judul']) ?></em></p>

                    <!--
                        Form dikirim via POST ke proses_edit.php.
                        ID disertakan dalam hidden input agar proses tahu
                        buku mana yang harus diupdate.
                    -->
                    <form action="../proses/proses_edit.php" method="POST">

                        <!-- Input tersembunyi untuk menyimpan ID buku -->
                        <input type="hidden" name="id" value="<?= $buku['id'] ?>">

                        <!-- Judul: value diisi dari data database -->
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                            <input
                                type="text" name="judul" id="judul"
                                class="form-control"
                                value="<?= htmlspecialchars($buku['judul']) ?>"
                                required maxlength="150"
                            >
                        </div>

                        <!-- Penulis & Penerbit -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-semibold">Penulis <span class="text-danger">*</span></label>
                                <input
                                    type="text" name="penulis" id="penulis"
                                    class="form-control"
                                    value="<?= htmlspecialchars($buku['penulis']) ?>"
                                    required maxlength="100"
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-semibold">Penerbit <span class="text-danger">*</span></label>
                                <input
                                    type="text" name="penerbit" id="penerbit"
                                    class="form-control"
                                    value="<?= htmlspecialchars($buku['penerbit']) ?>"
                                    required maxlength="100"
                                >
                            </div>
                        </div>

                        <!-- Harga & Stok -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="harga" class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input
                                        type="number" name="harga" id="harga"
                                        class="form-control"
                                        value="<?= $buku['harga'] ?>"
                                        required min="0"
                                    >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                                <input
                                    type="number" name="stok" id="stok"
                                    class="form-control"
                                    value="<?= $buku['stok'] ?>"
                                    required min="0"
                                >
                            </div>
                        </div>

                        <!-- URL Cover -->
                        <div class="mb-3">
                            <label for="cover" class="form-label fw-semibold">URL Gambar Cover</label>
                            <input
                                type="url" name="cover" id="cover"
                                class="form-control"
                                value="<?= htmlspecialchars($buku['cover'] ?? '') ?>"
                                oninput="pratinjauCover()"
                            >
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah cover.</div>
                            <!-- Pratinjau cover yang sudah ada -->
                            <img
                                id="preview-cover"
                                src="<?= htmlspecialchars($buku['cover'] ?? '') ?>"
                                alt="Pratinjau Cover"
                                class="mt-2 rounded"
                                style="<?= $buku['cover'] ? 'display:block;' : 'display:none;' ?> max-height:180px; max-width:130px; object-fit:cover;"
                            >
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-semibold">Deskripsi / Sinopsis</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"><?= htmlspecialchars($buku['deskripsi'] ?? '') ?></textarea>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-warning px-4 fw-bold">
                                <i class="bi bi-floppy me-1"></i>Perbarui Data
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x me-1"></i>Batal
                            </a>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>

