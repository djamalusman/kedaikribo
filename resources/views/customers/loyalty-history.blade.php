@extends('layouts.app')

@section('title', 'Loyalty ' . $customer->name)

@section('content')
<h5 class="mb-3">History Loyalty: {{ $customer->name }}</h5>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Earn</small>
                <h4 class="mb-0">{{ $totalEarned }} pts</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Total Redeem</small>
                <h4 class="mb-0">{{ $totalRedeemed }} pts</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small class="text-muted">Saldo Poin</small>
                <h4 class="mb-0">{{ $balance }} pts</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h6 class="card-title mb-3">Riwayat Poin</h6>

        <table class="table table-sm align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Order</th>
                    <th>Jenis</th>
                    <th class="text-end">Poin</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $item)
                    <tr>
                        <td>{{ $item->created_at }}</td>
                        <td>
                            @if($item->order)
                                {{ $item->order->order_code }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($item->type === 'earn')
                                <span class="badge bg-success">Earn</span>
                            @else
                                <span class="badge bg-warning text-dark">Redeem</span>
                            @endif
                        </td>
                        <td class="text-end">
                            {{ $item->type === 'redeem' ? '-' : '' }}{{ $item->points }} pts
                        </td>
                        <td>{{ $item->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada riwayat poin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $history->links() }}
    </div>
</div>
@endsection
