@extends('layouts.kasir')

@section('title', 'Detail Loyalty Customer')

@section('content')

<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-2">{{ $customer->name }}</h5>
                <p class="mb-1"><strong>Telepon:</strong> {{ $customer->phone ?? '-' }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $customer->email ?? '-' }}</p>
                <p class="mb-1"><strong>Alamat:</strong> {{ $customer->address ?? '-' }}</p>
                <hr>
                <h6>Total Poin Saat Ini</h6>
                <p class="display-6 mb-0">{{ $totalPoints }}</p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Riwayat Loyalty</h5>

                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Order</th>
                                <th class="text-center">Jenis</th>
                                <th class="text-center">Poin</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($histories as $row)
                            <tr>
                                <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($row->order_id)
                                        #{{ $row->order_id }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($row->type === 'earn')
                                        <span class="badge bg-success">Earn</span>
                                    @else
                                        <span class="badge bg-danger">Redeem</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ $row->points > 0 ? '+'.$row->points : $row->points }}
                                </td>
                                <td>{{ $row->description ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Belum ada riwayat loyalty.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
