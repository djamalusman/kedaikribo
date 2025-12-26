<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk {{ $order->order_code }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
            position: relative;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .mb     { margin-bottom: 6px; }

        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* ================= STEMPEL LUNAS ================= */
        /* .stamp-lunas {
            position: absolute;
            top: 210px; /* area item list */
            left: 50%;
            transform: translateX(-50%) rotate(-20deg);
            border: 6px double #c00;
            color: #c00;
            padding: 20px 60px;
            font-size: 48px;
            font-weight: 900;
            letter-spacing: 6px;
            text-transform: uppercase;
            opacity: 0.25;
            z-index: 30;
            border-radius: 10px;
            text-align: center;
        } */
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
     <img src="{{ public_path('assets/compiled/svg/logov1.png') }}" alt="Logo" width="120" height="110">
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
    Metode  : {{ strtoupper($order->payment_method) }}<br>
</div>

{{-- ================= FOOTER ================= --}}
<div class="center">
    === TERIMA KASIH ===<br>
    Selamat Menikmati 🙏
</div>

</body>
</html>