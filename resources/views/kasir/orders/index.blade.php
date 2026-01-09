@extends('layouts.app')

@section('title', 'POS - Daftar Order')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">

        <a href="{{ route('kasir.orders.create') }}" class="btn btn-primary">
            + Transaksi Baru
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $tab = $activeTab ?? 'open';
    @endphp

    <ul class="nav nav-tabs" id="kasirOrderTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $tab === 'open' ? 'active' : '' }}" id="tab-open-bill" data-bs-toggle="tab"
                data-bs-target="#open-bill" type="button" role="tab">
                Open Bill
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $tab === 'history' ? 'active' : '' }}" id="tab-history" data-bs-toggle="tab"
                data-bs-target="#history" type="button" role="tab">
                Riwayat
            </button>
        </li>
    </ul>

    <div class="tab-content mt-3">

        {{-- ======================= TAB OPEN BILL ======================= --}}
        <div class="tab-pane fade {{ $tab === 'open' ? 'show active' : '' }}" id="open-bill" role="tabpanel">

            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <strong>Bill Belum Lunas (Status: OPEN)</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="openTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kode</th>
                                    <th>Customer</th>
                                    <th>Tipe / Meja</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th width="220">Aksi</th>
                                </tr>
                            </thead>
                        </table>


                    </div>
                </div>
            </div>

        </div>

        {{-- ======================= TAB RIWAYAT ======================= --}}
        <div class="tab-pane fade {{ $tab === 'history' ? 'show active' : '' }}" id="history" role="tabpanel">

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <strong>Filter Riwayat Transaksi</strong>
                </div>
                <div class="card-body">
                    <form id="historyFilter" class="row g-2 align-items-end">
                        <input type="hidden" name="tab" value="history">

                        <div class="col-md-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="from_date" class="form-control" value="{{ $from }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="to_date" class="form-control" value="{{ $to }}">
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Tampilkan</button>
                        </div>
                    </form>


                </div>
            </div>

            {{-- Ringkasan --}}


            {{-- Tabel Riwayat --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <strong>Riwayat Transaksi (Paid)</strong>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle" id="historyTable" width="100%">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kode</th>
                                    <th>Customer</th>
                                    <th>Tipe / Meja</th>
                                    <th>Metode</th>
                                    <th>Total</th>
                                    <th width="150">Aksi</th>
                                </tr>
                            </thead>
                        </table>


                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(function() {

            // ================= OPEN BILL =================
            $('#openTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('kasir.orders.openData') }}",
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'tanggal',
                        name: 'order_date'
                    },
                    {
                        data: 'kode',
                        name: 'order_code'
                    },
                    {
                        data: 'customer',
                        orderable: false
                    },
                    {
                        data: 'tipe_meja',
                        orderable: false
                    },
                    {
                        data: 'total',
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // ================= RIWAYAT =================
            const historyTable = $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('kasir.orders.historyData') }}",
                    data: function(d) {
                        d.from_date = $('input[name=from_date]').val();
                        d.to_date = $('input[name=to_date]').val();
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'tanggal',
                        name: 'order_date'
                    },
                    {
                        data: 'kode',
                        name: 'order_code'
                    },
                    {
                        data: 'customer',
                        orderable: false
                    },
                    {
                        data: 'tipe_meja',
                        orderable: false
                    },
                    {
                        data: 'metode',
                        orderable: false
                    },
                    {
                        data: 'total',
                        orderable: false
                    },
                    {
                        data: 'aksi',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // 🔥 FORM FILTER TANPA RELOAD
            $('#historyFilter').on('submit', function(e) {
                e.preventDefault();
                historyTable.ajax.reload();
            });

        });
    </script>

@endsection
