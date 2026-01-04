<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk {{ $order->order_code }}</title>

<style>
/* ================= DOMPDF SAFE STYLE ================= */
body {
    font-family: monospace;
    font-size: 10px;
    color: #000;
    margin: 0;
    padding: 0;
}

#receipt {
    width: 100%;
}

/* ================= HELPER ================= */
.center { text-align: center; }
.right  { text-align: right; }
.mb     { margin-bottom: 4px; }

/* ================= TABLE ================= */
table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 1px 0;
    vertical-align: top;
}

/* ================= LINE ================= */
hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 4px 0;
}
</style>
</head>

<body>

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
        Kasir : {{ $order->payments->first()->created_by ?? '-' }}<br>
        Cust  : {{ $order->customer->name ?? '-' }}
    </div>

    <hr>

    {{-- ITEMS --}}
    <table>
        @foreach($order->items as $item)
        <tr>
            <td>{{ $item->menuItem->name }}</td>
            <td class="right">{{ rupiah($item->total) }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="right">{{ $item->qty }} x {{ rupiah($item->price) }}</td>
        </tr>
        @endforeach
    </table>

    <hr>

    {{-- TOTAL --}}
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ rupiah($order->subtotal) }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td class="right">- {{ rupiah($order->discount_total ?? 0) }}</td>
        </tr>

        @if($order->reserved)
        <tr>
            <td>DP</td>
            <td class="right">- {{ rupiah($order->reserved->total_dp ?? 0) }}</td>
        </tr>
        @endif

        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="right"><strong>{{ rupiah($order->grand_total) }}</strong></td>
        </tr>
    </table>

    <hr>

    {{-- PAYMENT --}}
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

</body>
</html>
