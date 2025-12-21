@extends('layouts.app')

@section('title', 'Dashboard Kasir')

@section('content')
<div class="row g-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Transaksi Hari Ini</h6>
                <h3 class="mb-0">{{ $totalTransactions }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-1">Omzet Hari Ini</h6>
                <h3 class="mb-0">{{ rupiah($totalRevenue) }}</h3>
            </div>
        </div>
    </div>
    @if($tablesSummary)
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Status Meja (Outlet Anda)</h6>
                    <div class="d-flex gap-3">
                        <span class="badge bg-success">Available: {{ $tablesSummary->available_count }}</span>
                        <span class="badge bg-danger">Occupied: {{ $tablesSummary->occupied_count }}</span>
                        <span class="badge bg-warning text-dark">Reserved: {{ $tablesSummary->reserved_count }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <strong>Metode Pembayaran Hari Ini</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Metode</th>
                            <th class="text-end">Transaksi</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($paymentByMethod as $p)
                        <tr>
                            <td>{{ strtoupper($p->payment_method ?? '-') }}</td>
                            <td class="text-end">{{ $p->total_tx }}</td>
                            <td class="text-end">{{ rupiah($p->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <strong>Menu Terlaris Hari Ini</strong>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-end">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($topMenus as $m)
                        <tr>
                            <td>{{ $m->name }}</td>
                            <td class="text-end">{{ $m->qty_sold }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
