<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk {{ $order->order_code }}</title>

<style>
/* ================= RESET ================= */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

html, body {
    width: 57mm;
    font-family: monospace;
    font-size: 11px;
    color: #000;
}

/* ================= PRINT ================= */
@media print {
    @page {
        size: 57mm auto;   /* 🔥 AUTO HEIGHT = WAJIB */
        margin: 0;
    }

    html, body {
        width: 57mm;
        margin: 0;
        padding: 0;
    }
}

/* ================= CONTAINER ================= */
#receipt {
    width: 57mm;
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
    padding: 1px 0;
    vertical-align: top;
}

td.price,
td.qty {
    text-align: right;
    white-space: nowrap;
}

/* ================= LINE ================= */
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

    {{-- HEADER --}}
    <div class="center mb">
        <strong>{{ $order->outlet->name ?? 'TOKO' }}</strong><br>
        {{ $order->outlet->address ?? '' }}
    </div>

    {{-- INFO --}}
    <div class="mb">
        Order : {{ $order->order_code }}<br>
        Tgl   : {{ $order->created_at->format('d/m/Y H:i') }}<br>
        Kasir : {{ auth()->user()->name ?? '-' }}<br>
        Cust  : {{ $order->customer->name ?? '-' }}
    </div>

    <hr>

    {{-- ITEMS --}}
    <table>
    @foreach($order->items as $item)
        <tr>
            <td>{{ $item->menuItem->name }}</td>
            <td class="price">{{ rupiah($item->total) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="qty">{{ $item->qty }} x {{ rupiah($item->price) }}</td>
        </tr>
    @endforeach
    </table>

    <hr>

    {{-- TOTAL --}}
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

    {{-- PEMBAYARAN --}}
    <div class="mb">
        Metode : {{ strtoupper($order->payments->first()->payment_method ?? '-') }}<br>
        Ref    : {{ $order->payments->first()->ref_no ?? '-' }}
    </div>

    {{-- FOOTER --}}
    <div class="center">
        === TERIMA KASIH ===<br>
        Selamat Menikmati
    </div>

</div>

{{-- ================= AUTO PRINT (STABIL) ================= --}}
<script>
(function () {
    let printed = false;

    function doPrint() {
        if (printed) return;
        printed = true;
        window.print();
    }

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(doPrint, 150);
    });

    window.onafterprint = () => {
        window.close(); // aman Chrome & WebView
    };
})();
</script>

</body>
</html>
