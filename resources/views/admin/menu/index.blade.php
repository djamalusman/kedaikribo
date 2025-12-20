@extends('layouts.app')

@section('title', 'Manajemen Menu')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')
    
    <section class="section">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.menu.create') }}" class="btn btn-primary">Tambah</a>
            </div>
            <div class="card-body">
                <table class="display" id="table1">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Outlet</th>
                            <th>Kategori</th>
                            <th>Kode</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $m)
                            <tr>
                                <td>{{ $m->name }}</td>
                                <td>{{ $m->outlet->name ?? '-' }}</td>
                                <td>{{ $m->category->name ?? '-' }}</td>
                                <td>{{ $m->code }}</td>
                                <td>{{ rupiah($m->price) }}</td>
                                <td>
                                    @if($m->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.menu.edit', $m) }}" class="btn icon btn-primary">
                                        Edit
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.menu.destroy', $m) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus menu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn icon btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada menu.</td></tr>
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