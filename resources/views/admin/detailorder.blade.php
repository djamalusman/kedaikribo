@extends('layouts.app')

@section('title', 'Detail Order')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | HITUNG NILAI DASAR (DARI DB)
    |--------------------------------------------------------------------------
    */
    $subtotal      = $order->subtotal ?? $order->items->sum('total');
    $discountTotal = $order->discount_total ?? 0;
    $grandTotalDb  = $order->grand_total ?? ($subtotal - $discountTotal);

    /*
    |--------------------------------------------------------------------------
    | DP (HANYA JIKA RESERVED)
    |--------------------------------------------------------------------------
    */
    $dp = $order->reserved?->total_dp ?? 0;

    /*
    |--------------------------------------------------------------------------
    | TOTAL YANG HARUS DIBAYAR
    |--------------------------------------------------------------------------
    */
    $grandTotalPayable = $order->reserved
        ? max(0, $grandTotalDb - $dp)
        : $grandTotalDb;
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
                {{ $order->table?->name ?? '-' }}
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <strong>Promo</strong><br>
                {{ $order->promotion->name ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Metode Pembayaran</strong><br>
                {{ $order->payment_method ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Jumlah Dibayar</strong><br>
                {{ $order->paid_amount !== null ? rupiah($order->paid_amount) : '-' }}
            </div>
        </div>

        @if($order->reserved)
            <div class="alert alert-warning py-2 mt-2">
                Order ini memiliki <strong>DP sebesar {{ rupiah($dp) }}</strong>
            </div>
        @endif
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

        @if($order->reserved)
            <div class="d-flex justify-content-between mb-1 text-danger">
                <span>DP</span>
                <span>- {{ rupiah($dp) }}</span>
            </div>
        @endif

        <hr class="my-2">

        <div class="d-flex justify-content-between">
            <strong>Grand Total</strong>
            <strong>{{ rupiah($grandTotalPayable) }}</strong>
        </div>
    </div>
</div>

{{-- FORM PEMBAYARAN --}}
@if($order->status === 'open')
    <div class="card">
        <div class="card-header">
            <strong>Pembayaran</strong>
        </div>
        <div class="card-body">
            @if($order->payment_status !="paid" && $user->role != 2)
                <form action="{{ route('kasir.orders.pay', $order) }}" method="POST" class="row g-2">
                    @csrf
                    <input type="hidden" name="is_reserved"
                            value="{{ $order->table?->status ?? '-' }}">
                    <div class="col-md-2">
                        <label class="form-label">Metode</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Jumlah Bayar</label>
                        <input type="text" class="form-control"
                            value="{{ rupiah($grandTotalPayable) }}" disabled>

                        <input type="hidden" name="paid_amount"
                            value="{{ $grandTotalPayable }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">No. Referensi (opsional)</label>
                        <input type="text" name="reference_no" class="form-control"
                            placeholder="No. transaksi bank / QRIS">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">
                            Tandai Lunas
                        </button>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                       <a href="{{ route('kasir.orders.print', $order) }}"
                            target="_blank"
                            class="btn btn-primary">
                            Cetak Struk
                        </a>
                    </div>
                    
                </form>
            @endif
        </div>
    </div>
@endif

@endsection
