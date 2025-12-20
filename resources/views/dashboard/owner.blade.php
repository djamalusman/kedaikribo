@extends('layouts.app')

@section('title', 'Dashboard Owner')

@section('content')
<div class="row g-3">
    @foreach($summary as $key => $item)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-1">{{ $item['label'] }}</h6>
                    <div class="mb-1">
                        <small class="text-muted">Jumlah Transaksi</small>
                        <h4 class="mb-0">{{ $item['transactions'] }}</h4>
                    </div>
                    <div>
                        <small class="text-muted">Total Omzet</small>
                        <h5 class="mb-0">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mt-3">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="card-title mb-0">Menu Terlaris per Periode</h6>
                </div>

                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Dari</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $chartFrom }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $chartTo }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Outlet</label>
                        <select name="outlet_id" class="form-select">
                            <option value="">Semua Outlet</option>
                            @foreach($outlets as $o)
                                <option value="{{ $o->id }}" @selected($outletId == $o->id)>{{ $o->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button class="btn btn-primary flex-fill" type="submit">
                            <i class="bi bi-funnel me-1"></i> Tampilkan
                        </button>
                        <a href="{{ route('owner.reports.top-menus.export', request()->query()) }}"
                        class="btn btn-success flex-fill">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                        </a>
                    </div>
                </form>

                <canvas id="menuTerlarisChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('menuTerlarisChart').getContext('2d');
        const labels = @json($chartLabels);
        const data   = @json($chartData);

        if (labels.length > 0) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Terjual',
                        data: data,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
