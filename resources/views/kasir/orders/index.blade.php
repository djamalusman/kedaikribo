@extends('layouts.app')

@section('title', 'POS - Daftar Order')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">

    <a href="{{ route('kasir.orders.create') }}" class="btn btn-primary">
        + Transaksi Baru
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    $tab = $activeTab ?? 'open';
@endphp

<ul class="nav nav-tabs" id="kasirOrderTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab === 'open' ? 'active' : '' }}"
                id="tab-open-bill"
                data-bs-toggle="tab"
                data-bs-target="#open-bill"
                type="button"
                role="tab">
            Open Bill
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $tab === 'history' ? 'active' : '' }}"
                id="tab-history"
                data-bs-toggle="tab"
                data-bs-target="#history"
                type="button"
                role="tab">
            Riwayat
        </button>
    </li>
</ul>

<div class="tab-content mt-3">

    {{-- ======================= TAB OPEN BILL ======================= --}}
    <div class="tab-pane fade {{ $tab === 'open' ? 'show active' : '' }}" id="open-bill" role="tabpanel">

        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <strong>Bill Belum Lunas (Status: OPEN)</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="display" id="table1">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Tanggal</th>
                                <th>Kode</th>
                                <th>Customer</th>
                                <th>Tipe / Meja</th>
                                <th>Total</th>
                                <th>Status Pembayaran</th>
                                <th class="text-center" style="width: 220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($openOrders as $order)
                                <tr>
                                    <td>{{ $order->order_date?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $order->order_code }}</td>
                                    <td>
                                        {{ $order->customer->name ?? '-' }}
                                        @if($order->customer && $order->customer->phone)
                                            <br>
                                            <small class="text-muted">{{ $order->customer->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ strtoupper(str_replace('_', ' ', $order->order_type)) }}
                                        @if($order->table)
                                            <br>
                                            <small class="text-muted">Meja: {{ $order->table->name }}</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ rupiah($order->grand_total ?? $order->subtotal) }}
                                    </td>
                                    <td class="text-end">
                                        {{$order->status}}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Detail + Bayar --}}
                                            <a href="{{ route('kasir.orders.show', $order) }}"
                                               class="btn btn-outline-primary">
                                                Detail / Bayar
                                            </a>
                                            @if($order->status =="paid")
                                                <a href="{{ route('kasir.orders.print', $order) }}"
                                                    target="_blank"
                                                    class="btn btn-outline-secondary">
                                                    Cetak Struk
                                                </a>
                                            @else
                                                 {{-- Edit isi order (customer + item) --}}
                                                <a href="{{ route('kasir.orders.edit', $order) }}"
                                                class="btn btn-outline-secondary">
                                                Edit
                                                </a>
                                            @endif
                                           
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        Tidak ada bill open untuk kasir ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================= TAB RIWAYAT ======================= --}}
    <div class="tab-pane fade {{ $tab === 'history' ? 'show active' : '' }}" id="history" role="tabpanel">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header">
                <strong>Filter Riwayat Transaksi</strong>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('kasir.orders.index') }}" class="row g-2 align-items-end">
                    {{-- Supaya tab tetap di history setelah filter --}}
                    <input type="hidden" name="tab" value="history">

                    <div class="col-md-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="from_date" class="form-control"
                               value="{{ $from }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="to_date" class="form-control"
                               value="{{ $to }}">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ringkasan --}}
        

        {{-- Tabel Riwayat --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <strong>Riwayat Transaksi (Paid)</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 120px;">Tanggal</th>
                                <th>Kode</th>
                                <th>Customer</th>
                                <th>Tipe / Meja</th>
                                <th>Metode</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historyOrders as $order)
                                <tr>
                                    <td>{{ $order->order_date?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $order->order_code }}</td>
                                    <td>
                                        {{ $order->customer->name ?? '-' }}
                                        @if($order->customer && $order->customer->phone)
                                            <br>
                                            <small class="text-muted">{{ $order->customer->phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ strtoupper(str_replace('_', ' ', $order->order_type)) }}
                                        @if($order->table)
                                            <br>
                                            <small class="text-muted">Meja: {{ $order->table->name }}</small>
                                        @endif
                                    </td>
                                    <td>{{ strtoupper($order->payments->first()->payment_method ?? '-') }}</td>
                                    <td class="text-end">{{ rupiah($order->grand_total) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('kasir.orders.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Detail / Bayar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">
                                        Tidak ada transaksi paid pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($historyOrders->hasPages())
                <div class="card-footer">
                    {{ $historyOrders->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
<script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        if (tab === 'history') {
            const historyTabBtn = document.getElementById('tab-history');
            if (historyTabBtn) {
                const tabInstance = new bootstrap.Tab(historyTabBtn);
                tabInstance.show();
            }
        }
    });
</script>
@endsection