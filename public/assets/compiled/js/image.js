document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('imageInput');
    const preview = document.getElementById('imagePreview');

    // kalau halaman tidak punya input image, stop
    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        // optional validasi
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            alert('File harus PNG atau JPG');
            this.value = '';
            preview.classList.add('d-none');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    });
});
