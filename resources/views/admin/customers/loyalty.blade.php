@extends('layouts.admin')

@section('title', 'Loyalty Customer')

@section('content')
<h4 class="mb-3">History Loyalty - {{ $customer->name }}</h4>

<div class="mb-3">
    <a href="javascript:history.back()" class="btn btn-sm btn-outline-secondary">
        &laquo; Kembali
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Total Poin Didapat</small>
                <h3 class="mb-0">{{ $totalEarned }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Total Poin Terpakai</small>
                <h3 class="mb-0">{{ $totalRedeemed }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Saldo Poin Saat Ini</small>
                <h3 class="mb-0">{{ $currentBalance }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- Filter periode history poin --}}
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <label class="form-label">Dari</label>
        <input type="date" name="from_date" value="{{ $from }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Sampai</label>
        <input type="date" name="to_date" value="{{ $to }}" class="form-control">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
</form>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Riwayat Poin</h6>

                <table class="table table-sm table-hover align-middle">
                    <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th class="text-end">Poin</th>
                        <th>Keterangan</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($history as $row)
                        <tr>
                            <td>{{ $row->created_at }}</td>
                            <td>
                                @if($row->type === 'earn')
                                    <span class="badge bg-success">Earn</span>
                                @else
                                    <span class="badge bg-danger">Redeem</span>
                                @endif
                            </td>
                            <td class="text-end">{{ $row->points }}</td>
                            <td>{{ $row->description }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada transaksi poin.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                {{ $history->links() }}
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="mb-3">Info Customer</h6>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nama</dt>
                    <dd class="col-sm-8">{{ $customer->name }}</dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $customer->email ?? '-' }}</dd>

                    <dt class="col-sm-4">Telepon</dt>
                    <dd class="col-sm-8">{{ $customer->phone ?? '-' }}</dd>

                    <dt class="col-sm-4">Terakhir Order</dt>
                    <dd class="col-sm-8">
                        @if($recentOrders->first())
                            {{ $recentOrders->first()->order_date }}
                        @else
                            -
                        @endif
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">Order Terakhir</h6>
                <table class="table table-sm align-middle">
                    <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Outlet</th>
                        <th class="text-end">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentOrders as $o)
                        <tr>
                            <td>{{ $o->order_date }}</td>
                            <td>{{ $o->outlet->name ?? '-' }}</td>
                            <td class="text-end">Rp {{ number_format($o->grand_total,0,',','.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada order.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
