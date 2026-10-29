// ============================================
// File    : assets/js/script.js
// Deskripsi: JavaScript Native untuk
//            interaksi sisi klien.
// ============================================

/**
 * Fungsi konfirmasi sebelum menghapus data.
 * Dipanggil dari atribut onclick pada tombol Hapus.
 * @param {string} url - URL tujuan proses hapus
 */
function konfirmasiHapus(url) {
    // Tampilkan dialog konfirmasi bawaan browser
    if (confirm("Apakah Anda yakin ingin menghapus buku ini?\nTindakan ini tidak dapat dibatalkan.")) {
        // Jika pengguna mengklik OK, arahkan ke URL proses hapus
        window.location.href = url;
    }
}

/**
 * Pratinjau gambar cover saat URL diisi pada form.
 * Dipanggil saat input URL cover berubah (oninput).
 */
function pratinjauCover() {
    const urlInput = document.getElementById('cover');
    const previewImg = document.getElementById('preview-cover');

    if (urlInput && previewImg) {
        const url = urlInput.value.trim();
        if (url !== '') {
            previewImg.src = url;
            previewImg.style.display = 'block';
            // Sembunyikan gambar jika URL tidak valid
            previewImg.onerror = function () {
                this.style.display = 'none';
            };
        } else {
            previewImg.style.display = 'none';
        }
    }
}

// Jalankan pratinjau cover saat halaman dimuat (untuk halaman edit)
document.addEventListener('DOMContentLoaded', function () {
    pratinjauCover();
});

