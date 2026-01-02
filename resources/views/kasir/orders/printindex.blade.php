<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk {{ $order->order_code }}</title>

   <style>
    @page {
        margin: 5px;
    }

    body {
        font-family: monospace;
        font-size: 11px;
        color: #000;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .mb {
        margin-bottom: 6px;
    }


    table {
        width: 100%;
        border-collapse: collapse;
        page-break-inside: avoid;
    }

    tr {
        page-break-inside: avoid;
    }

    hr {
        page-break-after: avoid;
         border-top: 1px dashed #000;
    }
</style>

</head>

<body>

{{-- ================= STEMPEL LUNAS (HANYA JIKA SUDAH PAID) ================= --}}
{{-- @if(strtolower($order->payment_status ?? '') === 'paid')
    <div class="stamp-lunas">
        LUNAS
    </div>
@endif --}}

@php
    // ================= HITUNG NILAI =================
    $subtotal      = $order->subtotal;
    $discountTotal = $order->discount_total ?? 0;
    $grandTotalDb  = $order->grand_total;
    $dp            = $order->reserved?->total_dp ?? 0;

    // Sisa yang harus dibayar
    $payable = $order->reserved
        ? max(0, $grandTotalDb - $dp)
        : $grandTotalDb;
@endphp

{{-- ================= HEADER OUTLET ================= --}}
<div class="center mb">
    {{-- <strong>{{ $order->outlet->name ?? 'KEDAI KRIBO' }}</strong><br>
    {{ $order->outlet->address ?? '' }} --}}
    <img src="{{ public_path('assets/compiled/svg/logov1.png') }}"
     width="100" height="90">
</div>

{{-- ================= INFO ORDER ================= --}}
<div class="mb">
    Order : {{ $order->order_code }}<br>
    Tgl   : {{ $order->order_date?->format('d/m/Y H:i') }}<br>
    Kasir : {{ auth()->user()->name ?? '-' }}<br>
    Customer : {{ $order->customer->name ?? '-' }}
</div>

<hr>

{{-- ================= ITEM LIST ================= --}}
<table>
@foreach($order->items as $item)
<tr>
    <td>{{ $item->menuItem->name }}</td>
    <td class="right">{{ $item->qty }} x {{ rupiah($item->price) }}</td>
    <td class="right">{{ rupiah($item->total) }}</td>
</tr>
@endforeach
</table>

<hr>

{{-- ================= RINGKASAN TOTAL ================= --}}
<table>
<tr>
    <td>Subtotal</td>
    <td class="right">{{ rupiah($subtotal) }}</td>
</tr>

<tr>
    <td>Diskon</td>
    <td class="right">- {{ rupiah($discountTotal) }}</td>
</tr>

@if($order->reserved)
<tr>
    <td>DP</td>
    <td class="right">- {{ rupiah($dp) }}</td>
</tr>
@endif

<tr>
    <td><strong>Total Bayar</strong></td>
    <td class="right"><strong>{{ rupiah($payable) }}</strong></td>
</tr>
</table>

<hr>

{{-- ================= INFO PEMBAYARAN ================= --}}
<div class="mb">
    Metode  : {{ strtoupper($order->payments->first()->payment_method ?? '-') }}<br>
</div>

{{-- ================= FOOTER ================= --}}
<div class="center">
    === TERIMA KASIH ===<br>
    Selamat Menikmati 
</div>
<script>
(function () {
    function doPrint() {
        window.focus();
        window.print();
    }

    // Desktop Chrome
    window.addEventListener('load', function () {
        setTimeout(doPrint, 300);
    });

    // Android / Tablet Chrome (lebih sensitif)
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            setTimeout(doPrint, 300);
        }
    });
})();
</script>

</body>
</html>