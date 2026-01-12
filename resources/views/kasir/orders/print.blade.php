<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Struk {{ $order->order_code }}</title>

    <style>
        /* ================= PAGE ================= */
        @page {
            margin: 5px 5px 0 5px;
            /* bottom = 0 */
        }

        * {
            box-sizing: border-box;
            page-break-before: avoid;
            page-break-after: avoid;
        }

        body {
            font-family: monospace;
            font-size: 11px;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            color: #000;
        }

        /* ================= HELPERS ================= */
        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .mb {
            margin-bottom: 6px;
        }

        /* ================= TABLE ================= */
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        tr,
        td {
            page-break-inside: avoid;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
        }

        /* ================= HR ================= */
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        /* ================= FOOTER ================= */
        .footer {
            margin: 0;
            padding: 0;
            line-height: 1.1;
        }
    </style>
</head>

<body>

    @php
        $subtotal = $order->subtotal;
        $discountTotal = $order->discount_total ?? 0;
        $grandTotal = $order->grand_total;
    @endphp

    {{-- ================= LOGO ================= --}}
    <div class="center mb">
        <img src="{{ public_path('assets/compiled/svg/logov1.png') }}" width="100">
    </div>

    {{-- ================= INFO ================= --}}
    <div class="mb">
        Order : {{ $order->order_code }}<br>
        Tgl : {{ $order->order_date?->format('d/m/Y H:i') }}<br>
        Kasir : {{ auth()->user()->name ?? '-' }}<br>
        Customer : {{ $order->customer->name ?? '-' }}
    </div>

    <hr>

    {{-- ================= ITEMS ================= --}}
    <table>
        @foreach ($order->items as $item)
            <tr>
                <td>
                    {{ $item->menuItem->name }}
                </td>
                <td class="right">
                    {{ $item->qty }} x {{ rupiah($item->price) }}
                </td>
                <td class="right">
                    {{ rupiah($item->total) }}
                </td>
            </tr>
        @endforeach
    </table>

    <hr>

    {{-- ================= TOTAL ================= --}}
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ rupiah($subtotal) }}</td>
        </tr>
        <tr>
            <td>Diskon</td>
            <td class="right">- {{ rupiah($discountTotal) }}</td>
        </tr>
        <tr>
            <td><strong>Total Bayar</strong></td>
            <td class="right"><strong>{{ rupiah($grandTotal) }}</strong></td>
        </tr>
    </table>

    <hr>

    {{-- ================= METODE ================= --}}
    <div class="mb">
        Metode : @if ($order->payments->isNotEmpty())
            {{ strtoupper($order->payments->first()->payment_method) }}
        @else
            -
        @endif
    </div>

    {{-- ================= FOOTER (STOP DI SINI) ================= --}}
    <div class="center footer">
        === TERIMA KASIH ===<br>
        Selamat Menikmati
    </div>

</body>

</html>
