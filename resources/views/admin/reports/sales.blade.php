@extends('layouts.app') {{-- untuk admin & owner, masih pakai layout admin --}}

@section('title', 'Laporan Penjualan')

@section('content')
<h4 class="mb-3">
    @if($role === 'owner')
        (Owner)
    @else
        (Admin)
    @endif
</h4>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <label class="form-label">Dari</label>
        <input type="date" name="from_date" value="{{ $from }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Sampai</label>
        <input type="date" name="to_date" value="{{ $to }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Outlet</label>
        <select name="outlet_id" class="form-select">
            <option value="">Semua</option>
            @foreach($outlets as $o)
                <option value="{{ $o->id }}" @selected($outletId == $o->id)>
                    {{ $o->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary flex-fill">Tampilkan</button>

        {{-- Export Excel, pakai route sesuai role --}}
        @if($role === 'owner')
            <a href="{{ route('owner.reports.sales.export', request()->query()) }}"
               class="btn btn-success flex-fill">
                Export Excel
            </a>
        @else
            <a href="{{ route('admin.reports.sales.export', request()->query()) }}"
               class="btn btn-success flex-fill">
                Export Excel
            </a>
        @endif
    </div>
</form>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Total Transaksi</small>
                <h3 class="mb-0">{{ $totalTransactions }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Total Omzet</small>
                <h3 class="mb-0">Rp {{ number_format($totalRevenue,0,',','.') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-hover table-sm align-middle">
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>Order</th>
                <th>Outlet</th>
                <th>Kasir</th>
                <th>Pelanggan</th>
                <th class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td>{{ $o->order_date }}</td>
                    <td>{{ $o->order_code ?? $o->id }}</td>
                    <td>{{ $o->outlet->name ?? '-' }}</td>
                    <td>{{ $o->cashier->name ?? '-' }}</td>
                    <td>{{ $o->customer->name ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($o->grand_total,0,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>

        {{ $orders->links() }}
    </div>
</div>
@endsection
