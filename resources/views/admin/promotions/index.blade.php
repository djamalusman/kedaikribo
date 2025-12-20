@extends('layouts.app')

@section('title', 'Promo')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')
    {{-- <div class="d-flex justify-content-between align-items-center mb-3">
    
    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Promo Baru
    </a>
</div>

@if (session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
@endif --}}
    <section class="section">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">Tambah</a>
            </div>
            <div class="card-body">
                <table class="display" id="table1">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Outlet</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Loyalty?</th>
                            <th>Periode</th>
                            <th>Aktif</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $promo)
                            <tr>
                                <td>{{ $promo->name }}</td>
                                <td>{{ $promo->outlet->name ?? 'Semua Outlet' }}</td>
                                <td>{{ ucfirst($promo->type) }}</td>
                                <td>
                                    @if ($promo->type === 'percent')
                                        {{ $promo->value }}%
                                    @else
                                        Rp {{ number_format($promo->value, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td>
                                    @if ($promo->is_loyalty)
                                        <span class="badge bg-info text-dark">Loyalty</span>
                                    @else
                                        <span class="badge bg-secondary">Reguler</span>
                                    @endif
                                </td>
                                <td>{{ $promo->start_date }} s/d {{ $promo->end_date }}</td>
                                <td>
                                    @if ($promo->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn icon btn-primary">
                                        Edit
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.promotions.destroy', $promo) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus promo ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn icon btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada promo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
@section('scripts')
<script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
<script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
<script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js')}}"></script>
<script src="{{ asset('assets/static/js/pages/form-element-select.js')}}"></script>
@endsection