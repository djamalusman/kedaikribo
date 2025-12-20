@extends('layouts.app')

@section('title', 'Tambah Kasir')

@section('content')

@if($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.cashiers.store') }}">
            @include('admin.cashiers.form', ['cashiers' => null, 'submit' => 'Simpan'])
        </form>
    </div>
</div>
@endsection
