<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Struk {{ $order->order_code }}</title>

<style>
/* ================= RESET ================= */
* {
    box-sizing: border-box;
}

html, body {
    width: 58mm;
    margin: 0;
    padding: 0;
    font-family: monospace;
    font-size: 11px;
    color: #000;
}

/* ================= PRINT ================= */
@media print {
    @page {
        size: 58mm auto;   /* panjang otomatis */
        margin: 0;
    }

    html, body {
        width: 58mm;
        margin: 0;
        padding: 0;
        overflow: visible;
    }
}

/* ================= HELPER ================= */
.center { text-align: center; }
.right  { text-align: right; }
.mb     { margin-bottom: 6px; }

/* ================= TABLE ================= */
table {
    width: 100%;
    border-collapse: collapse;
}

td {
    font-family: monospace;
    font-size: 11px;
    padding: 1px 0;
    vertical-align: top;
}

/* kolom kiri */
td.name {
    width: 70%;
    word-wrap: break-word;
}

/* total harga */
td.price {
    width: 30%;
    text-align: right;
    white-space: nowrap;
}

/* qty x harga */
td.qty {
    text-align: right;
    font-size: 10px;
    white-space: nowrap;
}

</style>
</head>

<body>

@php
$subtotal = $order->subtotal;
$discount = $order->discount_total ?? 0;
$grand    = $order->grand_total;
$dp       = $order->reserved?->total_dp ?? 0;
$payable  = $order->reserved ? max(0, $grand - $dp) : $grand;
@endphp

<div id="receipt">

    {{-- ================= HEADER ================= --}}
    <div class="center mb">
        <strong>{{ $order->outlet->name ?? 'KEDAI KRIBO' }}</strong><br>
        =========================
    </div>

    {{-- ================= INFO ================= --}}
    <div class="mb">
        Order : {{ $order->order_code }}<br>
        Tgl   : {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Kasir : {{ auth()->user()->name ?? '-' }}<br>
        Cust  : {{ $order->customer->name ?? '-' }}
    </div>

    <hr>

    {{-- ================= ITEM LIST (INI KUNCI) ================= --}}
    <table>
    @foreach($order->items as $item)

        <!-- BARIS 1: Nama + Total -->
        <tr>
            <td class="name">
                {{ $item->menuItem->name }}
            </td>
            <td class="price">
                {{ rupiah($item->total) }}
            </td>
        </tr>

        <!-- BARIS 2: Qty x Harga (kanan) -->
        <tr>
            <td></td>
            <td class="qty">
                {{ $item->qty }} x {{ rupiah($item->price) }}
            </td>
        </tr>

    @endforeach
    </table>


    <hr>

    {{-- ================= TOTAL ================= --}}
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="price">{{ rupiah($subtotal) }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td class="price">- {{ rupiah($discount) }}</td>
        </tr>

        @if($order->reserved)
        <tr>
            <td>DP</td>
            <td class="price">- {{ rupiah($dp) }}</td>
        </tr>
        @endif

        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="price"><strong>{{ rupiah($payable) }}</strong></td>
        </tr>
    </table>

    <hr>

    {{-- ================= PAYMENT ================= --}}
    <div class="mb">
        Metode : {{ strtoupper($order->payments->first()->payment_method ?? '-') }}
    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="center">
        === TERIMA KASIH ===<br>
        Selamat Menikmati
    </div>

</div>

{{-- ================= AUTO PRINT ================= --}}
<script>
(function () {
    function doPrint() {
        window.focus();
        window.print();
    }

    window.onload = function () {
        setTimeout(doPrint, 300);
    };

    window.onafterprint = function () {
        window.location.href = "/kasir";
    };
})();
</script>

</body>
</html>
