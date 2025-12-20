@extends('layouts.app')

@section('title', 'Tambah Bahan Baku')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/extensions/choices.js/public/assets/styles/choices.css')}}">
@endpush
@section('content')


@if($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.ingredients.store') }}">
            @include('admin.ingredients.form', ['ingredient' => null, 'submit' => 'Simpan'])
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script src="{{ asset('assets/extensions/jquery/jquery.min.js')}}"></script>
<script src="{{ asset('assets/extensions/parsleyjs/parsley.min.js')}}"></script>
<script src="{{ asset('assets/static/js/pages/parsley.js')}}"></script>
<script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js')}}"></script>
<script src="{{ asset('assets/static/js/pages/form-element-select.js')}}"></script>
@endsection