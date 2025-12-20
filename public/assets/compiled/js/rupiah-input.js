document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.rupiah-display');

    if (!inputs.length) {
        return;
    }

    function formatRupiah(angka) {
        if (!angka) return '';

        // Hapus semua selain angka dan koma
        let numberString = angka.replace(/[^,\d]/g, '');
        let split = numberString.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        if (split[1] !== undefined) {
            rupiah += ',' + split[1].substring(0, 2);
        }

        return 'Rp ' + rupiah;
    }

    inputs.forEach(function (displayInput) {
        const targetName = displayInput.dataset.target;
        if (!targetName) return;

        const hiddenInput = document.querySelector(`input[name="${targetName}"]`);
        if (!hiddenInput) return;

        // Inisialisasi dari nilai hidden (misal saat edit)
        if (hiddenInput.value) {
            displayInput.value = formatRupiah(hiddenInput.value.toString());
        }

        displayInput.addEventListener('input', function () {
            // Ambil angka saja
            const numeric = this.value.replace(/[^,\d]/g, '').split(',')[0];

            // Set ke hidden (tanpa titik)
            hiddenInput.value = numeric || '';

            // Tampilkan format Rupiah di input tampilan
            this.value = formatRupiah(this.value);
        });

        // Agar saat user fokus, kursor di akhir
        displayInput.addEventListener('focus', function () {
            const val = this.value;
            this.value = '';
            this.value = val;
        });
    });
});
