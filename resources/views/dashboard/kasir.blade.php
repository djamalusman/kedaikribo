@extends('layouts.app')

@section('title', 'Dashboard')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/extensions/simple-datatables/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/table-datatable.css') }}">
@endpush
@section('content')
    <h4 class="mb-3">
        <small class="text-muted fs-6">
            ({{ $start->format('d/m/Y') }})
        </small>
    </h4>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Transaksi</p>
                    <h4 class="mb-0">{{ $summary['total_tx'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Transaksi Paid</p>
                    <h4 class="mb-0">{{ $summary['total_paid'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Open Bill</p>
                    <h4 class="mb-0">{{ $summary['total_open'] }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Omzet Paid</p>
                    <h4 class="mb-0">{{ rupiah($summary['total_amount']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan per metode pembayaran --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h5 class="mb-3">Ringkasan per Metode Pembayaran</h5>

            @if ($paymentByMethod->isEmpty())
                <p class="text-muted mb-0">Belum ada transaksi paid hari ini.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Metode</th>
                                <th class="text-center">Jumlah Tx</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($paymentByMethod as $row)
                                <tr>
                                    <td>{{ strtoupper($row->payment_method ?? '-') }}</td>
                                    <td class="text-center">{{ $row->total_tx }}</td>
                                    <td class="text-end">{{ rupiah($row->total_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Detail transaksi hari ini --}}
    <div class="card border-0 shadow-sm" hidden>
        <div class="card-body">
            <h5 class="mb-3">Detail Transaksi</h5>
            <div class="table-responsive">
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
                        @forelse($orders as $order)
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
                                    {{ strtoupper(optional($order->payments->first())->payment_method ?? '-') }}

                                </td>
                                <td class="text-end">
                                    {{ rupiah($order->grand_total) }}
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-primary btn-view-order"
                                        data-bs-toggle="modal" data-bs-target="#default"
                                        data-url="{{ route('kasir.dashboard.items', $order->id) }}">
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
    <div class="modal fade text-left" id="default" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close rounded-pill" data-bs-dismiss="modal">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>

@endsection

@section('scripts')
     <script src="{{ asset('assets/extensions/simple-datatables/umd/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/static/js/pages/simple-datatables.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const modal = document.getElementById('default');
            const modalBody = modal.querySelector('.modal-body');
            const modalTitle = modal.querySelector('.modal-title');
            const modalFooter = modal.querySelector('.modal-footer');

            document.querySelectorAll('.btn-view-order').forEach(btn => {
                btn.addEventListener('click', async function() {

                    const url = this.dataset.url;

                    modalTitle.innerText = 'Loading...';
                    modalBody.innerHTML = `
                <p class="text-center text-muted">
                    Memuat detail transaksi...
                </p>
            `;
                    modalFooter.innerHTML = '';

                    try {
                        const res = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!res.ok) {
                            throw new Error('HTTP ' + res.status);
                        }

                        const data = await res.json();

                        modalTitle.innerText = `Order #${data.order_code}`;

                        // =============================
                        // TABLE HEADER
                        // =============================
                        let html = `
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Menu</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                        // =============================
                        // ITEMS
                        // =============================
                        data.items.forEach(item => {
                            html += `
                        <tr>
                            <td>${item.name}</td>
                            <td class="text-center">${item.qty}</td>
                            <td class="text-end">
                                Rp ${Number(item.subtotal).toLocaleString('id-ID')}
                            </td>
                        </tr>
                    `;
                        });
                        console.log('DATA API:', data);

                        // =============================
                        // PROMO DISPLAY
                        // =============================
                        let promoHtml = '';

                        if (data.promotype === 'percent') {
                            promoHtml = `
                        <div class="fw-bold">
                            Rp ${Number(data.totalreal).toLocaleString('id-ID')}
                        </div>
                        <div class="text-danger">
                            - ${Number(data.promoPercent).toLocaleString('id-ID')}%
                        </div>
                        <div>─────────</div>
                        <div class="fw-bold">
                            Rp ${Number(data.grand_total).toLocaleString('id-ID')}
                        </div>
                    `;
                        } else if (data.promotype === 'nominal') {
                            promoHtml = `
                         <div class="fw-bold">
                            Rp ${Number(data.totalreal).toLocaleString('id-ID')}
                        </div>
                        <div class="text-danger">
                            - Rp ${Number(data.promoPercent).toLocaleString('id-ID')}
                        </div>
                        <div>─────────</div>
                        <div class="fw-bold">
                            Rp ${Number(data.grand_total).toLocaleString('id-ID')}
                        </div>
                    `;
                        } else {
                            // tanpa promo
                            promoHtml = `
                        <div class="fw-bold">
                            Rp ${Number(data.totalreal).toLocaleString('id-ID')}
                        </div>
                    `;
                        }

                        // =============================
                        // FOOTER TOTAL
                        // =============================
                        html += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-start align-top">
                                    Grand Total
                                </th>

                                <th class="text-end">
                                    ${promoHtml}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                `;

                        modalBody.innerHTML = html;

                        modalFooter.innerHTML = `
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Tutup
                    </button>
                `;

                    } catch (err) {
                        console.error(err);
                        modalTitle.innerText = 'Error';
                        modalBody.innerHTML = `
                    <p class="text-danger text-center">
                        Gagal memuat detail transaksi
                    </p>
                `;
                    }
                });
            });

        });
    </script>

@endsection
