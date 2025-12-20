@extends('layouts.app')

@section('title', 'Stok IN / OUT')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')
    

    {{-- <form method="GET" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Outlet</label>
                <select name="outlet_id" class="form-select">
                    <option value="">Semua Outlet</option>
                    @foreach ($outlets as $o)
                        <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Bahan Baku</label>
                <select name="ingredient_id" class="form-select">
                    <option value="">Semua Bahan</option>
                    @foreach ($ingredients as $ing)
                        <option value="{{ $ing->id }}" @selected($ingredientId == $ing->id)>
                            {{ $ing->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Jenis</label>
                <select name="movement_type" class="form-select">
                    <option value="">Semua</option>
                    <option value="in" @selected($movementType === 'in')>IN</option>
                    <option value="out" @selected($movementType === 'out')>OUT</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
            </div>

            <div class="col-md-2">
                <label class="form-label">Sampai</label>
                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button class="btn btn-secondary me-2" type="submit">Filter</button>
            <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-light">Reset</a>
        </div>
    </form> --}}
    <section class="section">
        <div class="card">
            <div class="card-header">
               <a href="{{ route('admin.stock-movements.create') }}" class="btn btn-primary">
                    Tambah
                </a>
            </div>
            <div class="card-body">
                <table class="display" id="table1">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Outlet</th>
                            <th>Bahan</th>
                            <th>Jenis</th>
                            <th>Qty</th>
                            <th>Ref</th>
                            <th>Deskripsi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse($movements as $mv)
                            <tr>
                                <td>{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $mv->outlet->name ?? '-' }}</td>
                                <td>{{ $mv->ingredient->name ?? '-' }}</td>
                                <td>
                                    @if ($mv->movement_type === 'in')
                                        <span class="badge bg-success">IN</span>
                                    @else
                                        <span class="badge bg-danger">OUT</span>
                                    @endif
                                </td>
                                <td>{{ number_format($mv->qty, 1, ',', '.') }}</td>
                                <td>
                                    @if ($mv->reference_type || $mv->reference_no)
                                        <small class="text-muted">
                                            {{ $mv->reference_type }} {{ $mv->reference_no }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ Str::limit($mv->description, 60) }}</td>
                                <td>
                                    <a href="{{ route('admin.stock-movements.edit', $mv) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.stock-movements.destroy', $mv) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Hapus pergerakan stok ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-3">
                                    Belum ada pergerakan stok.
                                </td>
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
@endsection