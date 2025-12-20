@extends('layouts.app')

@section('title', $role === 'owner' ? 'Laporan Penjualan (Owner)' : 'Laporan Penjualan (Admin)')

@section('content')

<form method="GET" class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" name="to_date" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Outlet</label>
                <select name="outlet_id" class="form-select">
                    <option value="">Semua Outlet</option>
                    @foreach($outlets as $o)
                        <option value="{{ $o->id }}" @selected($outletId == $o->id)>
                            {{ $o->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ $role === 'owner' ? route('owner.reports.sales.export', request()->query()) : route('admin.reports.sales.export', request()->query()) }}"
                   class="btn btn-success flex-fill">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Transaksi</small>
                <h4 class="mb-0">{{ $totalTransactions }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Omzet</small>
                <h4 class="mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Outlet</th>
                    <th>Kasir</th>
                    <th>Pelanggan</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Diskon</th>
                    <th class="text-end">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_date }}</td>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->outlet->name ?? '-' }}</td>
                        <td>{{ $order->cashier->name ?? '-' }}</td>
                        <td>{{ $order->customer->name ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($order->discount_total, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $orders->appends(request()->query())->links() }}
    </div>
</div>
@endsection
