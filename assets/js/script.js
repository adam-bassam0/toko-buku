/**
 * BukuKu - Global Interactive Scripts
 */

function pratinjauCover() {
    const urlInput = document.getElementById('cover');
    const previewImg = document.getElementById('preview-cover') || document.getElementById('coverPreview');

    if (urlInput && previewImg) {
        const url = urlInput.value.trim();
        if (url !== '') {
            previewImg.src = url;
            previewImg.style.display = 'block';
            previewImg.onerror = function () {
                this.src = 'https://via.placeholder.com/140x190?text=No+Cover';
            };
        } else {
            previewImg.src = 'https://via.placeholder.com/140x190?text=No+Cover';
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    pratinjauCover();

    // Auto-scroll active category pill into view if needed
    const activePill = document.querySelector('.category-pill.active');
    if (activePill) {
        activePill.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
});
