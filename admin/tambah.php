<?php
// ============================================
// File    : admin/tambah.php
// Deskripsi: Form modern untuk menambah buku baru
// ============================================

require_once '../config/auth.php';
require_once '../config/koneksi.php';

require_admin('../login.php');

$error = $_GET['error'] ?? '';
$kategoriList = $koneksi->query("SELECT id, nama FROM kategori ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku Baru — Admin BukuKu</title>
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Modern Design System -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- ===== NAVBAR ADMIN ===== -->
    <?php require __DIR__ . '/../partials/navbar_admin.php'; ?>

    <main class="container my-5 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-plus-circle-fill text-success me-2"></i>Tambah Buku Baru
                </h4>
                <p class="text-muted small mb-0">Isi formulir berikut untuk menambahkan buku baru ke katalog toko.</p>
            </div>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Data Buku
            </a>
        </div>

        <?php if ($error === 'kolom_wajib'): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Judul, penulis, dan penerbit wajib diisi dengan benar.
            </div>
        <?php elseif ($error === 'csrf'): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-shield-x me-2"></i>Sesi formulir kadaluarsa atau token tidak valid. Silakan coba lagi.
            </div>
        <?php elseif ($error === 'db_error'): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-x-circle me-2"></i>Gagal menyimpan data ke database. Silakan coba kembali.
            </div>
        <?php endif; ?>

        <form action="../proses/proses_tambah.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div class="row g-4">
                <!-- Kolom Kiri: Informasi Utama -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                        <h5 class="fw-bold text-slate-900 mb-4 pb-2 border-bottom">Informasi Dasar Buku</h5>

                        <!-- Judul -->
                        <div class="mb-4">
                            <label for="judul" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="judul"
                                id="judul"
                                class="form-control form-control-lg fs-6"
                                placeholder="Contoh: Laskar Pelangi"
                                required
                                maxlength="150"
                                autofocus
                            >
                        </div>

                        <!-- Kategori -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="kategori_id" class="form-label mb-0">Kategori Buku</label>
                                <a href="kategori.php" class="small text-primary text-decoration-none" target="_blank">
                                    <i class="bi bi-plus-circle me-1"></i>Kelola Kategori
                                </a>
                            </div>
                            <select name="kategori_id" id="kategori_id" class="form-select">
                                <option value="">-- Pilih Kategori (Opsional) --</option>
                                <?php if ($kategoriList): ?>
                                    <?php while ($kat = $kategoriList->fetch_assoc()): ?>
                                        <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Penulis & Penerbit -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="penulis" class="form-label">Penulis <span class="text-danger">*</span></label>
                                <input type="text" name="penulis" id="penulis" class="form-control" placeholder="Contoh: Andrea Hirata" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label">Penerbit <span class="text-danger">*</span></label>
                                <input type="text" name="penerbit" id="penerbit" class="form-control" placeholder="Contoh: Bentang Pustaka" required maxlength="100">
                            </div>
                        </div>

                        <!-- Harga & Stok -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="harga" class="form-label">Harga Satuan <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="harga" id="harga" class="form-control" placeholder="89000" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="stok" class="form-label">Jumlah Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stok" id="stok" class="form-control" placeholder="10" required min="0">
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-2">
                            <label for="deskripsi" class="form-label">Sinopsis / Deskripsi Buku</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" placeholder="Tuliskan gambaran ringkas mengenai isi buku..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Cover & Pratinjau -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold text-slate-900 mb-3 pb-2 border-bottom">Gambar Sampul</h5>

                        <div class="mb-3">
                            <label for="cover" class="form-label">URL Gambar Cover</label>
                            <input
                                type="url"
                                name="cover"
                                id="cover"
                                class="form-control"
                                placeholder="https://domain.com/cover.jpg"
                                oninput="updateCoverPreview(this.value)"
                            >
                            <div class="form-text small">Masukkan tautan langsung gambar cover dari web.</div>
                        </div>

                        <!-- Box Pratinjau -->
                        <div class="text-center p-3 bg-slate-50 rounded-3 border border-slate-200">
                            <div class="small text-muted mb-2 fw-semibold">Pratinjau Cover:</div>
                            <div class="d-flex align-items-center justify-content-center bg-white rounded-3 border overflow-hidden mx-auto shadow-xs" style="width: 140px; height: 190px;">
                                <img
                                    id="coverPreview"
                                    src="https://via.placeholder.com/140x190?text=No+Cover"
                                    alt="Pratinjau Cover"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                    onerror="this.src='https://via.placeholder.com/140x190?text=No+Cover'"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Form -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <button type="submit" class="btn btn-success btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2 mb-2">
                            <i class="bi bi-floppy-fill"></i>Simpan Buku Baru
                        </button>
                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <!-- ===== FOOTER ===== -->
    <?php require __DIR__ . '/../partials/footer_admin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateCoverPreview(url) {
            const preview = document.getElementById('coverPreview');
            if (url && url.trim() !== '') {
                preview.src = url.trim();
            } else {
                preview.src = 'https://via.placeholder.com/140x190?text=No+Cover';
            }
        }
    </script>
</body>
</html>
