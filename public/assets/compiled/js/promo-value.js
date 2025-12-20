document.addEventListener('DOMContentLoaded', function () {
    const typeSelect   = document.getElementById('promo_type');
    const valueHidden  = document.getElementById('promo_value');
    const valueDisplay = document.getElementById('promo_value_display');
    const label        = document.getElementById('label_promo_value');
    const help         = document.getElementById('help_promo_value');

    if (!typeSelect || !valueHidden || !valueDisplay) {
        return;
    }

    function formatRupiah(angka) {
        if (!angka) return '';

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

    function setupPercentMode() {
        label.textContent = 'Nilai (%)';
        help.textContent  = 'Contoh: 10 berarti diskon 10%.';

        // Tampilkan apa adanya (tanpa Rp, tanpa titik)
        valueDisplay.value = valueHidden.value || '';

        valueDisplay.oninput = function () {
            // hanya angka dan titik/koma
            let raw = this.value.replace(/[^0-9.,]/g, '').replace(',', '.');
            this.value = raw;
            valueHidden.value = raw || 0;
        };
    }

    function setupRupiahMode() {
        label.textContent = 'Nilai (Rp)';
        help.textContent  = 'Contoh: 5000 berarti diskon Rp 5.000.';

        // Inisialisasi tampilan dari nilai hidden
        if (valueHidden.value) {
            valueDisplay.value = formatRupiah(valueHidden.value.toString());
        } else {
            valueDisplay.value = '';
        }

        valueDisplay.oninput = function () {
            // ambil angka saja
            let numeric = this.value.replace(/[^,\d]/g, '').split(',')[0];

            // simpan ke hidden tanpa format
            valueHidden.value = numeric || 0;

            // tampilkan dengan format rupiah
            this.value = formatRupiah(this.value);
        };
    }

    function applyMode() {
        if (typeSelect.value === 'percent') {
            setupPercentMode();
        } else {
            setupRupiahMode();
        }
    }

    // Ketika halaman pertama kali load (create/edit)
    applyMode();

    // Saat tipe promo diubah
    typeSelect.addEventListener('change', function () {
        // reset tampilan tapi pertahankan value lama kalau mau
        // atau bisa juga kosongkan:
        // valueHidden.value = '';
        // valueDisplay.value = '';
        applyMode();
    });
});
