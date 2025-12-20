@extends('layouts.app')
@section('title', 'Bahan Baku')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
    @if (session('success'))
        <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif
    <section class="section">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.ingredients.create') }}" class="btn btn-primary">Tambah</a>
            </div>
            <div class="card-body">
                <table class="display" id="table1">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Satuan</th>
                            <th>Stok</th>
                            <th>Stok Min</th>
                            <th>Aksi</th>

                        </tr>
                    </thead>
                    <tbody>
                         @forelse($ingredients as $i)
                            <tr>
                                <td>{{ $i->name }}</td>
                                <td>{{ $i->unit }}</td>
                                <td>{{ $i->stock }}</td>
                                <td>{{ $i->min_stock }}</td>
                                <td>

                                    <a href="{{ route('admin.ingredients.edit', $i) }}" class="btn icon btn-primary">Edit</a>
                                    <form method="POST" action="{{ route('admin.ingredients.destroy',$i) }}"
                                        class="d-inline" onsubmit="return confirm('Hapus bahan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn icon btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada bahan baku.</td></tr>
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