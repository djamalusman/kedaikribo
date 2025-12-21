@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')
    <section class="row">

        {{-- =======================
LEFT CONTENT
======================= --}}
        <div class="col-12 col-lg-12">

            {{-- ===== STAT CARD ===== --}}
            <div class="row">
                <div class="col-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">Transaksi Hari Ini</small>
                            <h4>{{ $todayTransactions }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">Omzet Hari Ini</small>
                            <h4>Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">Total Omzet</small>
                            <h4>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <small class="text-muted">Order Terbaru</small>
                            <h4>{{ $recentOrders->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Menu Terlaris per Kategori – {{ $year }}</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="menuCategoryChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Omzet Bulanan Tahun {{ $year }}</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="monthlyRevenueChart" height="120"></canvas>
                        </div>
                    </div>
                </div>

            </div>






            {{-- ===== TABLE ORDER TERBARU ===== --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Detail Order</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="display" id="table1">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Kode</th>
                                <th>Pelanggan</th>
                                <th>Meja</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Metode</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>{{ $order->order_date->format('H:i') }}</td>
                                    <td>#{{ $order->order_code }}</td>
                                    <td>{{ $order->customer->name ?? '-' }}</td>
                                    <td>{{ $order->table->name ?? '-' }}</td>
                                    <td class="text-center">
                                        @if ($order->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                        @elseif($order->status === 'open')
                                            <span class="badge bg-warning text-dark">Open</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{ strtoupper($order->payment_method ?? '-') }}
                                    </td>
                                    <td class="text-end">
                                        {{ rupiah($order->grand_total) }}
                                    </td>
                                    <td class="text-end">
                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-view-order"
                                            data-bs-toggle="modal"
                                            data-bs-target="#default"
                                            data-order='@json($order)'>
                                            View List
                                        </button>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        Belum ada transaksi untuk kasir hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="modal fade text-left" id="default" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel1"></h5>
                        <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer">
                        
                    </div>
                </div>
            </div>
        </div>
        {{-- =======================
RIGHT SIDEBAR
======================= --}}


    </section>
@endsection


@section('scripts')
    <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.menuDetailMap = @json($menuDetailMap);
        window.menuCategoryMonthLabels = @json($menuCategoryMonthLabels);
        window.menuCategoryMonthlySeries = @json($menuCategoryMonthlySeries);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* =====================================================
               DATA DARI CONTROLLER
            ===================================================== */
            const monthLabels = @json($menuCategoryMonthLabels ?? []);
            const seriesData = @json($menuCategoryMonthlySeries ?? []);

            console.log('Month Labels:', monthLabels);
            console.log('Series Data:', seriesData);
            console.log('Detail Map:', menuDetailMap);

            if (!monthLabels.length || !seriesData.length) {
                console.warn('Menu Terlaris per Kategori: data kosong');
                return;
            }

            const ctx = document.getElementById('menuCategoryChart');
            if (!ctx) {
                console.error('Canvas menuCategoryChart tidak ditemukan');
                return;
            }

            /* =====================================================
               ANTI DOUBLE RENDER
            ===================================================== */
            if (window.menuCategoryChartInstance) {
                window.menuCategoryChartInstance.destroy();
            }
            const colors = [
                '#ffc107', // kuning
                '#000000' // hitam
            ];


            /* =====================================================
               
                /* =====================================================
                   BENTUK DATASET UNTUK CHART.JS
                ===================================================== */
            const datasets = seriesData.map((item, index) => ({
                label: item.label,
                data: item.data,
                backgroundColor: colors[index % colors.length],
                borderColor: colors[index % colors.length],
                borderRadius: 6,
                barPercentage: 0.75,
                categoryPercentage: 0.7

            }));

            /* =====================================================
               RENDER BAR CHART
            ===================================================== */
            window.menuCategoryChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthLabels, // Jan–Des
                    datasets: datasets // kategori
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {

                                // Judul tooltip (bulan)
                                title: function(items) {
                                    return items[0].label;
                                },

                                // Isi tooltip (DETAIL MENU)
                                label: function(ctx) {

                                    const category = ctx.dataset.label; // Makanan / Minuman
                                    const month = ctx.label; // Jan / Feb / Des
                                    const total = ctx.raw;

                                    let lines = [];
                                    lines.push(`${category}: ${total} terjual`);

                                    if (
                                        menuDetailMap[month] &&
                                        menuDetailMap[month][category] &&
                                        menuDetailMap[month][category].length
                                    ) {
                                        menuDetailMap[month][category].forEach(item => {
                                            lines.push(`• ${item.name}: ${item.qty}`);
                                        });
                                    } else {
                                        lines.push('• Tidak ada detail');
                                    }

                                    return lines;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Qty Terjual'
                            }
                        }
                    }
                }
            });

        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const labels = @json($monthlyLabels ?? []);
            const revenue = @json($monthlyRevenue ?? []);

            if (!labels.length || !revenue.length) {
                console.warn('Omzet bulanan: data kosong');
                return;
            }

            const ctx = document.getElementById('monthlyRevenueChart');
            if (!ctx) return;

            // 🔥 ANTI DOUBLE
            if (window.monthlyRevenueChartInstance) {
                window.monthlyRevenueChartInstance.destroy();
            }

            window.monthlyRevenueChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Omzet Bulanan',
                        data: revenue,

                        // =====================
                        // 🎯 POINT STYLING
                        // =====================
                        pointStyle: 'circle',
                        pointRadius: 6,
                        pointHoverRadius: 9,
                        pointBackgroundColor: '#ffc107',
                        pointBorderColor: 'black',
                        pointBorderWidth: 2,

                        // =====================
                        // LINE STYLE
                        // =====================
                        borderColor: 'black',
                        backgroundColor: 'rgba(40,167,69,0.25)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return 'Rp ' + ctx.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('default');
    const modalBody = modal.querySelector('.modal-body');
    const modalTitle = modal.querySelector('.modal-title');

    document.querySelectorAll('.btn-view-order').forEach(btn => {
        btn.addEventListener('click', function () {
            const order = JSON.parse(this.dataset.order);

            modalTitle.innerText = 'Order #' + order.order_code;

            let html = `
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            order.items.forEach(item => {
                html += `
                    <tr>
                        <td>${item.menu_item?.name ?? '-'}</td>
                        <td class="text-center">${item.qty}</td>
                       <td class="text-end">
                            Rp ${Number(item.price).toLocaleString('id-ID')}
                        </td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;

            modalBody.innerHTML = html;
        });
    });
});
</script>

@endsection
