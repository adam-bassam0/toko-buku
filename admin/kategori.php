<?php
// ============================================
// File    : admin/kategori.php
// Deskripsi: Halaman Panel Admin untuk
//            mengelola (CRUD) kategori buku.
// ============================================

require_once '../config/auth.php';
require_once '../config/koneksi.php';

require_admin('../login.php');

// Ambil semua kategori beserta jumlah buku di dalamnya
$query = "SELECT k.id, k.nama, k.slug, k.deskripsi, k.created_at,
                 COUNT(b.id) AS total_buku
          FROM kategori k
          LEFT JOIN buku b ON b.kategori_id = k.id
          GROUP BY k.id
          ORDER BY k.nama ASC";
$result = $koneksi->query($query);

// Hitung buku yang belum punya kategori
$resTanpaKategori = $koneksi->query("SELECT COUNT(*) AS total FROM buku WHERE kategori_id IS NULL");
$totalTanpaKategori = $resTanpaKategori ? (int) $resTanpaKategori->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori — Admin BukuKu</title>
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

    <!-- ===== KONTEN UTAMA ===== -->
    <main class="container my-4 flex-grow-1">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bi bi-tags me-2 text-primary"></i>Manajemen Kategori Buku
                </h4>
                <p class="text-muted small mb-0">Kelola kategori untuk mengelompokkan koleksi buku di katalog toko.</p>
            </div>
            <!-- Tombol Tambah Kategori -->
            <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKategori">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Tambah Kategori Baru</span>
            </button>
        </div>

        <!-- Info Ringkasan -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Kategori</div>
                            <h3 class="fw-bold text-slate-900 mb-0"><?= $result ? $result->num_rows : 0 ?></h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-tags-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Buku Tanpa Kategori</div>
                            <h3 class="fw-bold text-warning mb-0"><?= $totalTanpaKategori ?></h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="bi bi-journal-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== TABEL DATA KATEGORI ===== -->
        <div class="table-card shadow-sm mb-4">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">#</th>
                            <th width="220">Nama Kategori</th>
                            <th width="180">Slug URL</th>
                            <th>Deskripsi</th>
                            <th width="140" class="text-center">Jumlah Buku</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php $no = 1; ?>
                            <?php while ($kat = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center text-muted small"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-bold text-slate-900"><?= htmlspecialchars($kat['nama']) ?></div>
                                    </td>
                                    <td>
                                        <code class="px-2 py-1 bg-light rounded text-primary small"><?= htmlspecialchars($kat['slug']) ?></code>
                                    </td>
                                    <td class="small text-muted">
                                        <?= htmlspecialchars($kat['deskripsi'] ?? '-') ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                                            <?= $kat['total_buku'] ?> buku
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button
                                                type="button"
                                                class="btn btn-outline-warning btn-sm p-1 px-2 btn-edit-kategori"
                                                data-id="<?= $kat['id'] ?>"
                                                data-nama="<?= htmlspecialchars($kat['nama'], ENT_QUOTES) ?>"
                                                data-deskripsi="<?= htmlspecialchars($kat['deskripsi'] ?? '', ENT_QUOTES) ?>"
                                                title="Edit Kategori"
                                            >
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="../proses/proses_kategori.php" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'<?= addslashes(htmlspecialchars($kat['nama'])) ?>\'?\n\nBuku yang berada dalam kategori ini tidak akan terhapus, melainkan menjadi \'Tanpa Kategori\'.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                <input type="hidden" name="aksi" value="hapus">
                                                <input type="hidden" name="id" value="<?= (int) $kat['id'] ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm p-1 px-2" title="Hapus Kategori">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-tags display-5 d-block mb-2 text-secondary"></i>
                                    Belum ada kategori buku. Klik <strong>Tambah Kategori Baru</strong> untuk membuatnya!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- ===== MODAL TAMBAH KATEGORI ===== -->
    <div class="modal fade" id="modalTambahKategori" tabindex="-1" aria-labelledby="modalTambahKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../proses/proses_kategori.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-slate-900" id="modalTambahKategoriLabel">
                            <i class="bi bi-plus-circle me-1 text-success"></i>Tambah Kategori Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="namaTambah" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="namaTambah" class="form-control" placeholder="Contoh: Novel Remaja" required maxlength="100">
                            <div class="form-text small">Slug URL akan dibuat secara otomatis dari nama kategori.</div>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsiTambah" class="form-label">Deskripsi Kategori</label>
                            <textarea name="deskripsi" id="deskripsiTambah" class="form-control" rows="3" placeholder="Deskripsi singkat jenis buku dalam kategori ini..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="bi bi-check-lg me-1"></i>Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL EDIT KATEGORI ===== -->
    <div class="modal fade" id="modalEditKategori" tabindex="-1" aria-labelledby="modalEditKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../proses/proses_kategori.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" id="editId" value="">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-slate-900" id="modalEditKategoriLabel">
                            <i class="bi bi-pencil-square me-1 text-warning"></i>Edit Kategori
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNama" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="editNama" class="form-control" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="editDeskripsi" class="form-label">Deskripsi Kategori</label>
                            <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning fw-bold">
                            <i class="bi bi-check-lg me-1"></i>Perbarui Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== FOOTER ===== -->
    <?php require __DIR__ . '/../partials/footer_admin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEditElement = document.getElementById('modalEditKategori');
            const modalEdit = new bootstrap.Modal(modalEditElement);

            document.querySelectorAll('.btn-edit-kategori').forEach(function (button) {
                button.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    const nama = this.getAttribute('data-nama');
                    const deskripsi = this.getAttribute('data-deskripsi');

                    document.getElementById('editId').value = id;
                    document.getElementById('editNama').value = nama;
                    document.getElementById('editDeskripsi').value = deskripsi;

                    modalEdit.show();
                });
            });
        });
    </script>
</body>
</html>
