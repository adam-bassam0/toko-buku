<?php
// ============================================
// File    : admin/tambah.php
// Deskripsi: Form HTML untuk menginput
//            data buku baru.
// ============================================

// Tidak perlu koneksi di sini; form ini hanya menampilkan
// elemen input dan mengirimkan data ke proses_tambah.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Admin BukuKu</title>
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

    <!-- ===== KONTEN FORM ===== -->
    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="card form-card p-4 p-md-5">
                    <h4 class="fw-bold mb-4">
                        <i class="bi bi-plus-circle-fill me-2 text-success"></i>Tambah Buku Baru
                    </h4>

                    <!--
                        Form dikirim via POST ke proses_tambah.php.
                        POST lebih aman dari GET untuk pengiriman data.
                    -->
                    <form action="../proses/proses_tambah.php" method="POST">

                        <!-- Judul -->
                        <div class="mb-3">
                            <label for="judul" class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                            <input type="text" name="judul" id="judul" class="form-control" placeholder="Contoh: Laskar Pelangi" required maxlength="150">
                        </div>

                        <!-- Penulis & Penerbit (sejajar) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="penulis" class="form-label fw-semibold">Penulis <span class="text-danger">*</span></label>
                                <input type="text" name="penulis" id="penulis" class="form-control" placeholder="Contoh: Andrea Hirata" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label for="penerbit" class="form-label fw-semibold">Penerbit <span class="text-danger">*</span></label>
                                <input type="text" name="penerbit" id="penerbit" class="form-control" placeholder="Contoh: Gramedia" required maxlength="100">
                            </div>
                        </div>

                        <!-- Harga & Stok (sejajar) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="harga" class="form-label fw-semibold">Harga (Rp) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="harga" id="harga" class="form-control" placeholder="89000" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="stok" class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                                <input type="number" name="stok" id="stok" class="form-control" placeholder="0" required min="0">
                            </div>
                        </div>

                        <!-- URL Cover -->
                        <div class="mb-3">
                            <label for="cover" class="form-label fw-semibold">URL Gambar Cover</label>
                            <input
                                type="url"
                                name="cover"
                                id="cover"
                                class="form-control"
                                placeholder="https://example.com/gambar-buku.jpg"
                                oninput="pratinjauCover()"
                            >
                            <div class="form-text">Opsional. Isi dengan URL gambar dari internet.</div>
                            <!-- Area pratinjau gambar cover (ditampilkan via JavaScript) -->
                            <img id="preview-cover" src="" alt="Pratinjau Cover" class="mt-2 rounded" style="display:none; max-height:180px; max-width:130px; object-fit:cover;">
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-semibold">Deskripsi / Sinopsis</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" placeholder="Tuliskan sinopsis buku..."></textarea>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-floppy me-1"></i>Simpan Buku
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

