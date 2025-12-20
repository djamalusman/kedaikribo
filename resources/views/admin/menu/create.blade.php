{{-- resources/views/admin/menu/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah Menu')
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
        <form method="POST" action="{{ route('admin.menu.store') }}" enctype="multipart/form-data">
            @include('admin.menu.form', ['menu' => null, 'submit' => 'Simpan'])
        </form>
    </div>
</div>
@endsection

@push('scripts')

<script src="{{ asset('assets/extensions/choices.js/public/assets/scripts/choices.js')}}"></script>
<script src="{{ asset('assets/static/js/pages/form-element-select.js')}}"></script>
<script src="{{ asset('assets/static/js/pages/image.js')}}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const category = document.getElementById('category_id');
  const code     = document.getElementById('code');

  if (!category || !code) return;

  function syncCode() {
    const opt = category.options[category.selectedIndex];
    code.value = opt && opt.dataset && opt.dataset.code ? opt.dataset.code : '';
  }

  category.addEventListener('change', syncCode);
  syncCode(); // initial saat load (untuk edit/old value)
});
</script>
@endpush
