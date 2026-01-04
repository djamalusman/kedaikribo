<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
    font-family: monospace;
    font-size: 10px;
    margin: 0;
    padding: 0;
}

* {
    page-break-inside: avoid !important;
    page-break-before: avoid !important;
    page-break-after: avoid !important;
}

#receipt {
    width: 100%;
    page-break-inside: avoid;
}

.center { text-align: center; }
.right  { text-align: right; }
.mb { margin-bottom: 4px; }

table {
    width: 100%;
    border-collapse: collapse;
}

td {
    padding: 1px 0;
}

.line {
    border-top: 1px dashed #000;
    margin: 4px 0;
}
</style>
</head>

<body>
<div id="receipt">

<div class="center mb">
    <strong>{{ $order->outlet->name }}</strong><br>
    {{ $order->outlet->address }}
</div>

<div class="mb">
    Order : {{ $order->order_code }}<br>
    Tgl   : {{ $order->created_at->format('d/m/Y H:i') }}<br>
    Kasir : {{ $order->payments->first()->created_by ?? '-' }}<br>
    Cust  : {{ $order->customer->name ?? '-' }}
</div>

<div class="line"></div>

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

<div class="line"></div>

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
    <td class="right">- {{ rupiah($order->reserved->total_dp) }}</td>
</tr>
@endif
<tr>
    <td><strong>TOTAL</strong></td>
    <td class="right"><strong>{{ rupiah($order->grand_total) }}</strong></td>
</tr>
</table>

<div class="line"></div>

<div class="mb">
    Metode : {{ strtoupper($order->payments->first()->payment_method) }}<br>
    Ref    : {{ $order->payments->first()->ref_no }}
</div>

<div class="center">
    === TERIMA KASIH ===<br>
    Selamat Menikmati
</div>

</div>
</body>
</html>
