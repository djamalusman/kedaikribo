@extends('layouts.app')

@section('title','Manajemen Menu')

@push('styles')
<link rel="stylesheet"
 href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.menu.create') }}"
               class="btn btn-primary">
                Tambah
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered" id="menuTable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Outlet</th>
                        <th>Kategori</th>
                        <th>Kode</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {
    $('#menuTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.menu.data') }}",
        columns: [
            { data: 'name', name: 'name' },
            { data: 'outlet', name: 'outlet.name', orderable:false },
            { data: 'category', name: 'category.name', orderable:false },
            { data: 'code', name: 'code' },
            { data: 'price', name: 'price' },
            { data: 'status', orderable:false, searchable:false },
            { data: 'aksi', orderable:false, searchable:false }
        ]
    });
});
</script>
@endsection
