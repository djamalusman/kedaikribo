@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')

    <form action="{{ route('kasir.orders.update', $order) }}" method="POST" id="pos-form">
        @csrf
        @method('PUT')

        {{-- ================== CUSTOMER ================== --}}
        <div class="card mb-3">
            <div class="card-header">
                <strong>Customer</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Nama</label>
                        <input type="text" name="customer_name" class="form-control"
                            value="{{ old('customer_name', $order->customer->name ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="customer_phone" class="form-control"
                            value="{{ old('customer_phone', $order->customer->phone ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="customer_email" class="form-control"
                            value="{{ old('customer_email', $order->customer->email ?? '') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== INFO ORDER (TIPE, MEJA, PROMO) ================== --}}
        <div class="card mb-3">
            <div class="card-header">
                <strong>Info Order</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label">Tipe Order</label>
                        <select name="order_type" id="order_type" class="form-select" required>
                            @php $ot = old('order_type', $order->order_type); @endphp
                            <option value="dine_in" @selected($ot === 'dine_in')>Dine In</option>
                            <option value="take_away" @selected($ot === 'take_away')>Take Away</option>
                            <option value="delivery" @selected($ot === 'delivery')>Delivery</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="table-wrapper">
                        <label class="form-label">Meja (untuk Dine In)</label>
                        <select name="table_id" class="form-select">
                            <option value="">- Tidak ada -</option>
                            @foreach ($tables as $table)
                                <option value="{{ $table->id }}" @selected(old('table_id', $order->table_id) == $table->id)>
                                    {{ $table->name }} ({{ $table->status }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="is_reserved" name="is_reserved"
                                value="reserved" @checked(old('is_reserved', $isReserved))>
                            <label class="form-check-label" for="is_reserved">
                                Reserved
                            </label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Promo</label>
                        <select name="promotion_id" id="promotion_id" class="form-select">
                            <option value="">- Tanpa Promo -</option>
                            @foreach ($promos as $promo)
                                <option value="{{ $promo->id }}" @selected(old('promotion_id', $order->promotion_id) == $promo->id)>
                                    {{ $promo->name }}
                                    @if ($promo->type === 'percent')
                                        ({{ $promo->value }}%)
                                    @else
                                        ({{ rupiah($promo->value) }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mt-2" id="reserve-wrapper" style="display:none">
                        
                    <div class="col-md-4">
                        <label class="form-label">Nominal DP</label>
                        <input type="text" name="nominal_dp" class="form-control rupiah-display" data-target="nominal_dp"
                            placeholder="Contoh: 15.000"
                            value="{{ rupiah(old('nominal_dp', $order->reservation->total_dp ?? '')) }}">

                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Date</label>
                        <input type="datetime-local" name="start_date" class="form-control"
                            value="{{ old('start_date', optional($order->reservation?->start_date)->format('Y-m-d\TH:i')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Date</label>
                        <input type="datetime-local" name="end_date" class="form-control"
                            value="{{ old('end_date', optional($order->reservation?->end_date)->format('Y-m-d\TH:i')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ================== POS LAYOUT: DAFTAR MENU & KERANJANG ================== --}}
        <div class="row">
            {{-- KIRI: DAFTAR MENU --}}
            <div class="row g-3">
                <div class="col-lg-12">
                    <h5 class="mb-2">Menu</h5>

                    {{-- TAB KATEGORI --}}
                    <ul class="nav nav-pills mb-3" role="tablist">
                        @foreach ($categories as $index => $cat)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $index === 0 ? 'active' : '' }}" data-bs-toggle="tab"
                                    data-bs-target="#cat-{{ $cat->id }}" type="button" role="tab">
                                    {{ $cat->name }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- ISI TAB: GRID MENU --}}
                    <div class="tab-content">
                        @foreach ($categories as $index => $cat)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                id="cat-{{ $cat->id }}" role="tabpanel">
                                <div class="row g-2">
                                    @forelse($cat->menuItems as $menu)
                                        <div class="col-6 col-md-2 mb-2">
                                            <button type="button"
                                                class="btn btn-outline-secondary w-100 text-start add-to-cart-btn p-0 overflow-hidden"
                                                data-id="{{ $menu->id }}" data-name="{{ $menu->name }}"
                                                data-price="{{ $menu->price }}">

                                                {{-- Gambar --}}
                                                <div class="ratio ratio-1x1">
                                                    <img src="{{ $menu->image ? asset('storage/menu/' . $menu->image) : asset('images/no-image.png') }}"
                                                        class="img-fluid object-fit-cover" alt="{{ $menu->name }}">
                                                </div>

                                                {{-- Info --}}
                                                <div class="p-2">
                                                    <div class="fw-semibold text-truncate">{{ $menu->name }}</div>
                                                    <div class="small text-muted">{{ rupiah($menu->price) }}</div>
                                                </div>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <span class="text-muted small">Tidak ada menu di kategori ini.</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-12 mt-3">
                    <h5 class="mb-2">Keranjang</h5>
                    <div class="table-responsive mb-2">
                        <table class="table table-sm align-middle" id="cart-table">
                            <thead>
                                <tr>
                                    <th>Menu</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- diisi JS (cart) --}}
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal</span>
                        <span id="subtotal_display">
                            {{ rupiah($order->subtotal ?? 0) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Diskon (Promo)</span>
                        <span id="discount_display">
                            {{ rupiah($order->discount_total ?? 0) }}
                        </span>
                    </div>
                    

                    @if (!empty($isReserved))
                        <div id="dp-summary-row" class="d-flex justify-content-between mb-1">
                            <span>Nominal DP</span>
                            <span id="nominal_dp_display" style="color:red">Rp 15.000</span>
                        </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Grand Total</strong>
                        <strong id="grand_total_display">
                            {{ rupiah($order->grand_total ?? 0) }}
                        </strong>
                    </div>

                    {{-- <button type="submit" class="btn btn-primary w-100">
                        Simpan Perubahan Order
                    </button> --}}
                </div>
            </div>

        </div>

        {{-- ================== TOMBOL SIMPAN ================== --}}
        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('kasir.orders.index') }}" class="btn btn-light">
                &larr; Batal & Kembali
            </a>
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>
        </div>
    </form>

    {{-- ================== SCRIPT POS EDIT ================== --}}
    <script>
/* =========================================================
   DATA DARI PHP
========================================================= */
var cart   = @json($initialCart);
var promos = @json($promosData);

/* =========================================================
   ELEMENT
========================================================= */
var cartTableBody   = document.querySelector('#cart-table tbody');
var promotionSelect = document.getElementById('promotion_id');
var orderTypeSelect = document.getElementById('order_type');
var tableWrapper    = document.getElementById('table-wrapper');
var posForm         = document.getElementById('pos-form');

var reservedCheckbox = document.getElementById('is_reserved');
var reserveWrapper   = document.getElementById('reserve-wrapper');
var dpInput          = document.querySelector('input[name="nominal_dp"]');

/* =========================================================
   STATE
========================================================= */
var subtotal = 0;

/* =========================================================
   UTIL
========================================================= */
function formatRupiah(num) {
    num = Number(num) || 0;
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(num);
}

function parseRupiah(val) {
    return Number((val || '').replace(/[^0-9]/g, '')) || 0;
}

function findPromoById(id) {
    return promos.find(p => String(p.id) === String(id)) || null;
}

function calculatePromoDiscount(sub) {
    if (!promotionSelect?.value) return 0;

    var promo = findPromoById(promotionSelect.value);
    if (!promo) return 0;

    var minAmount = Number(promo.min_amount || 0);
    if (minAmount && sub < minAmount) return 0;

    var discount = promo.type === 'percent'
        ? sub * (Number(promo.value) / 100)
        : Number(promo.value);

    return Math.min(discount, sub);
}

/* =========================================================
   RENDER CART & TOTAL (SATU-SATUNYA TEMPAT HITUNG)
========================================================= */
function renderCart() {
    if (!cartTableBody) return;

    cartTableBody.innerHTML = '';
    subtotal = 0;

    if (cart.length === 0) {
        cartTableBody.innerHTML =
            '<tr><td colspan="5" class="text-center text-muted py-3">Keranjang masih kosong.</td></tr>';
    }

    cart.forEach(function (item, index) {
        var lineTotal = item.qty * item.price;
        subtotal += lineTotal;

        cartTableBody.insertAdjacentHTML('beforeend', `
            <tr>
                <td>${item.name}</td>
                <td class="text-center">
                    <input type="number" min="1" value="${item.qty}"
                           class="form-control form-control-sm qty-input"
                           data-index="${index}">
                </td>
                <td class="text-end">${formatRupiah(item.price)}</td>
                <td class="text-end">${formatRupiah(lineTotal)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item-btn"
                            data-index="${index}">&times;</button>
                </td>
            </tr>
        `);
    });

    var discount   = calculatePromoDiscount(subtotal);
    var grandTotal = subtotal - discount;

    /* ================= DP LOGIC ================= */
    if (reservedCheckbox) {
        var dp = parseRupiah(dpInput?.value);
        grandTotal -= dp; // BOLEH NEGATIF
    }

    // var discountInput = document.getElementById('discount_total_input');
    // if (discountInput) {
    //     discountInput.value = discount;
    // }
    /* ============================================ */

    document.getElementById('subtotal_display').innerText    = formatRupiah(subtotal);
    document.getElementById('discount_display').innerText    = formatRupiah(discount);
    document.getElementById('grand_total_display').innerText = formatRupiah(grandTotal);
}

/* =========================================================
   EVENT LISTENERS
========================================================= */

// Tambah menu
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        var id    = this.dataset.id;
        var name  = this.dataset.name;
        var price = Number(this.dataset.price) || 0;

        var item = cart.find(i => String(i.menu_item_id) === String(id));
        item ? item.qty++ : cart.push({ menu_item_id: id, name, price, qty: 1 });

        renderCart();
    });
});

// Update qty & remove
cartTableBody?.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty-input')) {
        var idx = e.target.dataset.index;
        cart[idx].qty = Math.max(1, parseInt(e.target.value) || 1);
        renderCart();
    }
});

cartTableBody?.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-item-btn')) {
        cart.splice(e.target.dataset.index, 1);
        renderCart();
    }
});

// Promo
promotionSelect?.addEventListener('change', renderCart);

// DP realtime
dpInput?.addEventListener('input', renderCart);
var dpSummaryRow = document.getElementById('dp-summary-row');

// Reserved toggle

function toggleReserved() {
    if (!reservedCheckbox) return;

    if (reservedCheckbox.checked) {
        // TAMPILKAN
        if (reserveWrapper) reserveWrapper.style.display = 'block';
        if (dpSummaryRow) dpSummaryRow.style.display = 'flex';
    } else {
        // SEMBUNYIKAN
        if (reserveWrapper) reserveWrapper.style.display = 'none';
        if (dpSummaryRow) dpSummaryRow.style.display = 'none';

        // RESET DP
        if (dpInput) dpInput.value = '';
        if (document.getElementById('nominal_dp_display')) {
            document.getElementById('nominal_dp_display').innerText = formatRupiah(0);
        }
    }

    renderCart(); // ⬅️ penting
}

reservedCheckbox?.addEventListener('change', toggleReserved);

// Order type
function toggleTable() {
    if (!orderTypeSelect || !tableWrapper) return;
    tableWrapper.style.display = orderTypeSelect.value === 'dine_in' ? '' : 'none';
}

orderTypeSelect?.addEventListener('change', toggleTable);

/* =========================================================
   INIT
========================================================= */
document.addEventListener('DOMContentLoaded', function () {
    toggleTable();
    toggleReserved();
    renderCart();
});

/* =========================================================
   SUBMIT
========================================================= */
posForm?.addEventListener('submit', function (e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Keranjang masih kosong.');
        return;
    }

    document.querySelectorAll('.cart-hidden-input').forEach(el => el.remove());

    cart.forEach(function (item, idx) {
        ['menu_item_id', 'name', 'qty', 'price'].forEach(field => {
            var input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = `cart[${idx}][${field}]`;
            input.value = item[field];
            input.className = 'cart-hidden-input';
            posForm.appendChild(input);
        });
    });
});
</script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reservedCheckbox = document.getElementById('is_reserved');
            const reserveWrapper = document.getElementById('reserve-wrapper');

            function toggleReserved() {
                if (!reservedCheckbox || !reserveWrapper) return;

                reserveWrapper.style.display = reservedCheckbox.checked ?
                    'flex' :
                    'none';
            }

            if (reservedCheckbox) {
                reservedCheckbox.addEventListener('change', toggleReserved);
                toggleReserved(); // ⬅️ penting untuk edit mode
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const dpInput = document.querySelector('input[name="nominal_dp"]');
            const dpDisplay = document.getElementById('nominal_dp_display');

            function parseRupiah(val) {
                return Number((val || '').replace(/[^0-9]/g, '')) || 0;
            }

            function recalculateTotal() {
                let discount = calculatePromoDiscount(subtotal);
                let grandTotal = subtotal - discount;

                const dp = parseRupiah(dpInput?.value);

                // DP boleh bikin negatif
                grandTotal -= dp;

                // Update UI
                if (dpDisplay) dpDisplay.innerText = formatRupiah(dp);
                document.getElementById('subtotal_display').innerText = formatRupiah(subtotal);
                document.getElementById('discount_display').innerText = formatRupiah(discount);
                document.getElementById('grand_total_display').innerText = formatRupiah(grandTotal);
            }

            // 🔥 Realtime saat user mengetik
            dpInput?.addEventListener('input', recalculateTotal);

            // Hitung pertama kali (page load)
            recalculateTotal();
        });
    </script>
@endsection
