@extends('layouts.app')

@section('title', 'Manajemen Meja')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
<link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')

    <section class="section">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.tables.create') }}" class="btn btn-primary">
                    Tambah
                </a>
            </div>
            <div class="card-body">
                <table class="display" id="table1">
                    <thead>
                        <tr>
                            <th>Outlet</th>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Kapasitas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tables as $table)
                            <tr>
                                <td>{{ $table->outlet->name ?? '-' }}</td>
                                <td>{{ $table->name }}</td>
                                <td>{{ $table->code ?? '-' }}</td>
                                <td>{{ $table->capacity }}</td>
                                <td>
                                    @switch($table->status)
                                        @case('available')
                                            <span class="badge bg-success">Available</span>
                                        @break

                                        @case('occupied')
                                            <span class="badge bg-danger">Occupied</span>
                                        @break

                                        @case('reserved')
                                            <span class="badge bg-warning text-dark">Reserved</span>
                                        @break

                                        @case('inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                        @break

                                        @default
                                            <span class="badge bg-light text-dark">{{ $table->status }}</span>
                                    @endswitch
                                </td>
                                <td>

                                    <a href="{{ route('admin.tables.edit', $table) }}" class="btn icon btn-primary">
                                        Edit
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.tables.destroy', $table) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus meja ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn icon btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">
                                        Belum ada data meja.
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
