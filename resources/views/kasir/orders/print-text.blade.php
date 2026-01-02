{{ $order->outlet->name ?? 'KEDAI KRIBO' }}
{{ $order->outlet->address ?? '' }}

--------------------------------
Order : {{ $order->order_code }}
Tanggal : {{ $order->created_at->format('d/m/Y H:i') }}
Kasir   : {{ auth()->user()->name ?? '-' }}
--------------------------------

@foreach($order->items as $item)
{{ $item->menuItem->name }}
{{ $item->qty }} x {{ rupiah($item->price) }}   {{ rupiah($item->total) }}
@endforeach

--------------------------------
Subtotal : {{ rupiah($order->subtotal) }}
Diskon   : {{ rupiah($order->discount_total ?? 0) }}
TOTAL    : {{ rupiah($order->grand_total) }}
--------------------------------
Metode   : {{ strtoupper($order->payments->first()->payment_method ?? '-') }}

=== TERIMA KASIH ===
