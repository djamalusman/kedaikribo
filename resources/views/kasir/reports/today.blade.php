@extends('layouts.app')

@section('title', 'Laporan Kasir Hari Ini')

@section('content')
<h4 class="mb-3">
    <small class="text-muted fs-6">
        ({{ $start->format('d/m/Y') }})
    </small>
</h4>

<div class="row g-3 mb-3">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Total Transaksi</p>
                <h4 class="mb-0">{{ $summary['total_tx'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Transaksi Paid</p>
                <h4 class="mb-0">{{ $summary['total_paid'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Open Bill</p>
                <h4 class="mb-0">{{ $summary['total_open'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted mb-1">Omzet Paid</p>
                <h4 class="mb-0">{{ rupiah($summary['total_amount']) }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Ringkasan per metode pembayaran --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h5 class="mb-3">Ringkasan per Metode Pembayaran</h5>

        @if($paymentByMethod->isEmpty())
            <p class="text-muted mb-0">Belum ada transaksi paid hari ini.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th class="text-center">Jumlah Tx</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentByMethod as $row)
                            <tr>
                                <td>{{ strtoupper($row->payment_method ?? '-') }}</td>
                                <td class="text-center">{{ $row->total_tx }}</td>
                                <td class="text-end">{{ rupiah($row->total_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Detail transaksi hari ini --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Detail Transaksi</h5>
        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Kode</th>
                        <th>Pelanggan</th>
                        <th>Meja</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Metode</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_date->format('H:i') }}</td>
                            <td>#{{ $order->order_code }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ $order->table->name ?? '-' }}</td>
                            <td class="text-center">
                                @if($order->status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($order->status === 'open')
                                    <span class="badge bg-warning text-dark">Open</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{ strtoupper($order->payment_method ?? '-') }}
                            </td>
                            <td class="text-end">
                                {{ rupiah($order->grand_total) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                Belum ada transaksi untuk kasir hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
