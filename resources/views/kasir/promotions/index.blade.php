@extends('layouts.app')

@section('title', 'Promo Aktif')

@section('content')

@if($promotions->isEmpty())
    <div class="alert alert-info">
        Tidak ada promo aktif untuk outlet ini.
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Nama Promo</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Min. Belanja</th>
                            <th>Periode</th>
                            <th>Loyalty</th>
                            <th>Menu Terkait</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($promotions as $promo)
                            <tr>
                                <td>{{ $promo->name }}</td>
                                <td>{{ $promo->type === 'percent' ? 'Persentase (%)' : 'Nominal (Rp)' }}</td>
                                <td>
                                    @if($promo->type === 'percent')
                                        {{ $promo->value }}%
                                    @else
                                        {{ rupiah($promo->value) }}
                                    @endif
                                </td>
                                <td>
                                    {{ $promo->min_amount ? rupiah($promo->min_amount) : '-' }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($promo->start_date)->format('d/m/Y') }}
                                    &ndash;
                                    {{ \Carbon\Carbon::parse($promo->end_date)->format('d/m/Y') }}
                                </td>
                                <td>
                                    @if($promo->is_loyalty)
                                        <span class="badge bg-success">Ya</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak</span>
                                    @endif
                                </td>
                                <td>
                                    @if($promo->menuItems->isEmpty())
                                        <span class="text-muted small">Semua menu (tidak dibatasi)</span>
                                    @else
                                        <small>
                                            {{ $promo->menuItems->pluck('name')->implode(', ') }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
