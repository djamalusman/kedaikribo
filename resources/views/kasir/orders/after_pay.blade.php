<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting…</title>
</head>
<body>

<script>
    // Buka struk di TAB BARU
    window.open(
        "{{ route('kasir.orders.print', $order) }}",
        "_blank"
    );

    // Kembali ke halaman index
    window.location.href = "{{ route('kasir.orders.index') }}";
</script>

<noscript>
    <p>
        <a href="{{ route('kasir.orders.print', $order) }}" target="_blank">
            Cetak Struk
        </a>
    </p>
    <p>
        <a href="{{ route('kasir.orders.index') }}">
            Kembali ke daftar order
        </a>
    </p>
</noscript>

</body>
</html>
