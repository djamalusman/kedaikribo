{{-- resources/views/admin/menu/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Menu')

@section('content')

@if($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.menu.update', $menu) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.menu.form', ['submit' => 'Update'])
        </form>
    </div>
</div>
@endsection
