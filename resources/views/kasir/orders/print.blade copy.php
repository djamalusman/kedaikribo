<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Struk {{ $order->order_code }}</title>

<style>
/* ================= RESET TOTAL ================= */
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

/* ================= PRINT SETTING ================= */
@media print {
    @page {
        size: 58mm auto;   /* 🔥 panjang otomatis */
        margin: 0;         /* 🔥 tanpa margin */
    }

    html, body {
        width: 58mm;
        margin: 0;
        padding: 0;
        overflow: visible;
    }
}

/* ================= CONTAINER ================= */
#receipt {
    width: 58mm;
    overflow: visible;
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
    vertical-align: top;
    padding: 1px 0;
}

/* kolom harga kanan */
td.price {
    text-align: right;
    white-space: nowrap;
}

/* qty x harga */
td.qty {
    text-align: right;
    font-size: 10px;
    white-space: nowrap;
}

/* ================= HR ================= */
hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 6px 0;
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

    {{-- ========== HEADER TOKO ========== --}}
    <div class="center mb">
        <strong>{{ $order->outlet->name ?? 'KEDAI KRIBO' }}</strong><br>
        {{ $order->outlet->address ?? '' }}
    </div>

    {{-- ========== INFO ORDER ========== --}}
    <div class="mb">
        Order : {{ $order->order_code }}<br>
        Tgl   : {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Kasir : {{ auth()->user()->name ?? '-' }}<br>
        Cust  : {{ $order->customer->name ?? '-' }}
    </div>

    <hr>

    {{-- ========== ITEM LIST (FORMAT YANG KAMU MAU) ========== --}}
    <table>
    @foreach($order->items as $item)

        <!-- Baris 1: Nama + Total -->
        <tr>
            <td>{{ $item->menuItem->name }}</td>
            <td class="price">{{ rupiah($item->total) }}</td>
        </tr>

        <!-- Baris 2: Qty x Harga (kanan) -->
        <tr>
            <td></td>
            <td class="qty">{{ $item->qty }} x {{ rupiah($item->price) }}</td>
        </tr>

    @endforeach
    </table>

    <hr>

    {{-- ========== RINGKASAN TOTAL ========== --}}
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

    {{-- ========== PEMBAYARAN ========== --}}
    <div class="mb">
        Metode : {{ strtoupper($order->payments->first()->payment_method ?? '-') }}<br>
        Ref    : {{ $order->payments->first()->ref_no ?? '-' }}
    </div>

    {{-- ========== FOOTER ========== --}}
    <div class="center">
        === TERIMA KASIH ===<br>
        Selamat Menikmati
    </div>

</div>

{{-- ========== AUTO PRINT (ANTI DOUBEL & ANTI 404) ========== --}}
<script>
let printed = false;

function doPrint() {
    if (printed) return;
    printed = true;
    window.focus();
    window.print();
}

window.onload = function () {
    setTimeout(doPrint, 300);
};

/**
 * 🔥 KUNCI UTAMA
 * - Cancel print
 * - Ganti printer
 * - Print selesai
 * → TAB DITUTUP, BUKAN REDIRECT
 */
window.onafterprint = function () {
    window.close();
};
</script>


</body>
</html>
