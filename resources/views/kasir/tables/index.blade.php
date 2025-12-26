@extends('layouts.app')

@section('title', 'Status Meja')

@section('content')

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Filter Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua</option>
                    <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="occupied" {{ $status === 'occupied' ? 'selected' : '' }}>Occupied</option>
                    <option value="reserved" {{ $status === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100">Terapkan</button>
            </div>
        </div>
    </form>

    @if($tables->isEmpty())
        <div class="alert alert-info">
            Belum ada meja terdaftar untuk outlet ini.
        </div>
    @else
    {{-- <div class="row g-3">
        @foreach($tables as $table)
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-1">{{ $table->name }}</h5>
                        @if($table->code)
                            <p class="mb-1"><small class="text-muted">Kode: {{ $table->code }}</small></p>
                        @endif
                        <p class="mb-2"><small>Kapasitas: {{ $table->capacity }} orang</small></p>
                        @php
                            $badgeClass = match($table->status) {
                                'available' => 'bg-success',
                                'occupied'  => 'bg-danger',
                                'reserved'  => 'bg-warning text-dark',
                                default     => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($table->status) }}
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div> --}}

    <section id="content-types">
        <div class="row">
            @foreach($tables as $table)
                <div class="col-xl-3 col-md-6 col-sm-12">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                <h4 class="card-title">{{ $table->name }}</h4>
                                <p class="card-text">
                                    @if($table->code)
                                        <p class="mb-1"><small class="text-muted">Kode: {{ $table->code }}</small></p>
                                    @endif
                                    <p class="mb-2"><small>Kapasitas: {{ $table->capacity }} orang</small></p>
                                    @php
                                        $badgeClass = match($table->status) {
                                            'available' => 'bg-success',
                                            'occupied'  => 'bg-danger',
                                            'reserved'  => 'bg-warning text-dark',
                                            default     => 'bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst($table->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

@endif
@endsection
