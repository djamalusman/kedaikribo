@extends('layouts.app')

@section('title', 'Detail Order')

@section('content')



@php
    // Pakai nilai dari DB; kalau null baru dihitung dari items
    $subtotal      = $order->subtotal ?? $order->items->sum('total');
    $discountTotal = $order->discount_total ?? 0;
    $grandTotal    = $order->grand_total ?? ($subtotal - $discountTotal);
@endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="row mb-2">
            <div class="col-md-6">
                <h4 class="mb-3">Detail Order #{{ $order->order_code }}</h4>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Outlet</strong><br>
                {{ $order->outlet->name ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Tanggal Order</strong><br>
                {{ $order->order_date?->format('d/m/Y H:i') }}
            </div>
            <div class="col-md-4">
                <strong>Status</strong><br>
                {{ strtoupper($order->status) }} / {{ strtoupper($order->payment_status) }}
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Customer</strong><br>
                {{ $order->customer->name ?? '-' }}
                @if($order->customer && $order->customer->phone)
                    ({{ $order->customer->phone }})
                @endif
            </div>
            <div class="col-md-4">
                <strong>Tipe Order</strong><br>
                {{ strtoupper(str_replace('_', ' ', $order->order_type)) }}
            </div>
            <div class="col-md-4">
                <strong>Meja</strong><br>
                @if($order->table)
                    {{ $order->table->name }} ({{ $order->table->status }})
                @else
                    -
                @endif
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Promo</strong><br>
                {{ $order->promotion->name ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Metode Pembayaran</strong><br>
                {{ $order->payments->payment_method ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Jumlah Dibayar</strong><br>
                {{ isset($order->paid_amount) ? rupiah($order->paid_amount) : '-' }}
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Item Order</strong>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>{{ $item->menuItem->name ?? '-' }}</td>
                            <td class="text-center">{{ $item->qty }}</td>
                            <td class="text-end">{{ rupiah($item->price) }}</td>
                            <td class="text-end">{{ rupiah($item->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Tidak ada item.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex justify-content-between mb-1">
            <span>Subtotal</span>
            <span>{{ rupiah($subtotal) }}</span>
        </div>
        <div class="d-flex justify-content-between mb-1">
            <span>Diskon</span>
            <span>{{ rupiah($discountTotal) }}</span>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between">
            <strong>Grand Total</strong>
            <strong>{{ rupiah($grandTotal) }}</strong>
        </div>
    </div>
</div>

{{-- Form pembayaran (kalau masih OPEN / UNPAID) --}}
@if($order->status === 'open')
    <div class="card">
        <div class="card-header">
            <strong>Pembayaran</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('kasir.orders.pay', $order) }}" method="POST" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Metode</label>
                    <select name="payment_method"  class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jumlah Bayar</label>
                    <input type="text" class="form-control"
                        value="{{ rupiah($grandTotal) }}" disabled>

                    <input type="hidden" name="paid_amount"
                        value="{{ $grandTotal }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. Referensi (opsional)</label>
                    <input type="text" name="reference_no" readonly class="form-control"
                           placeholder="No. transaksi bank / QRIS">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-success w-100">
                        Tandai Lunas
                    </button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('kasir.orders.index') }}" class="btn btn-secondary">
                        Simpan 
                    </a>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
