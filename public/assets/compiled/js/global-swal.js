document.addEventListener('DOMContentLoaded', function () {
  const flash = window.__FLASH__ || {};

  // 1) Toast sukses
  if (flash.success) {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: flash.success,
      timer: 1800,
      showConfirmButton: false
    });
  }

  // 2) Alert gagal
  if (flash.error) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: flash.error
    });
  }

  // 3) Validasi (optional)
  if (Array.isArray(flash.errors) && flash.errors.length) {
    Swal.fire({
      icon: 'error',
      title: 'Validasi gagal',
      html: flash.errors.map(e => `${e}`).join('<br>')
    });
  }

  // 4) Confirm delete (global, pakai class js-delete-form)
  document.addEventListener('submit', function (e) {
    const form = e.target;

    if (!form.classList.contains('js-delete-form')) return;

    // cegah loop submit kedua
    if (form.dataset.confirmed === '1') return;

    e.preventDefault();

    Swal.fire({
      title: 'Hapus data ini?',
      text: 'Tindakan ini tidak bisa dibatalkan.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        form.dataset.confirmed = '1';
        form.submit();
      }
    });
  }, true);
});
