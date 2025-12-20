@extends('layouts.App')

@section('title', 'Kasir')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')

    <section class="section">
        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.cashiers.create') }}" class="btn btn-primary">Tambah</a>
            </div>
            <div class="card-body">
                <table class="display" id="table1">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Outlet</th>
                            <th>Password</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cashiers as $i)
                            <tr>
                                <td>{{ $i->name }}</td>
                                <td>{{ $i->email }}</td>
                                <td>{{ $i->outlet->name ?? '-' }}</td>
                                <td>{{ $i->password }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.cashiers.edit', $i) }}" class="btn icon btn-primary">
                                        Edit
                                    </a>
                                    <form method="POST"
                                        action="{{ route('admin.cashiers.destroy', $i) }}"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus bahan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn icon btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data Kasir.</td>
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
