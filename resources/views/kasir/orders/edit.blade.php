@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Edit Order #{{ $order->order_code }}</h4>

    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div><strong>Terjadi kesalahan:</strong></div>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

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
        // ====== Data awal dari PHP ======
        // cart: item existing dari order_items
        var cart = @json($initialCart);
        // promos: daftar promo aktif
        var promos = @json($promosData);

        var cartTableBody = document.querySelector('#cart-table tbody');
        var promotionSelect = document.getElementById('promotion_id');
        var orderTypeSelect = document.getElementById('order_type');
        var tableWrapper = document.getElementById('table-wrapper');
        var posForm = document.getElementById('pos-form');

        function formatRupiah(num) {
            num = Number(num) || 0;
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);
        }

        function findPromoById(id) {
            for (var i = 0; i < promos.length; i++) {
                if (String(promos[i].id) === String(id)) {
                    return promos[i];
                }
            }
            return null;
        }

        function calculatePromoDiscount(subtotal) {
            if (!promotionSelect) return 0;

            var promoId = promotionSelect.value;
            if (!promoId) return 0;

            var promo = findPromoById(promoId);
            if (!promo) return 0;

            var minAmount = promo.min_amount ? Number(promo.min_amount) : 0;
            if (minAmount > 0 && subtotal < minAmount) {
                return 0;
            }

            var discount = 0;
            if (promo.type === 'percent') {
                discount = subtotal * (Number(promo.value) / 100);
            } else {
                discount = Number(promo.value);
            }

            if (discount > subtotal) {
                discount = subtotal;
            }
            return discount;
        }

        function renderCart() {
            if (!cartTableBody) return;

            cartTableBody.innerHTML = '';

            if (cart.length === 0) {
                var emptyTr = document.createElement('tr');
                emptyTr.innerHTML =
                    '<td colspan="5" class="text-center text-muted py-3">Keranjang masih kosong.</td>';
                cartTableBody.appendChild(emptyTr);
            }

            var subtotal = 0;

            for (var i = 0; i < cart.length; i++) {
                var item = cart[i];
                var lineTotal = item.qty * item.price;
                subtotal += lineTotal;

                var tr = document.createElement('tr');

                var tdName = document.createElement('td');
                tdName.textContent = item.name;

                var tdQty = document.createElement('td');
                tdQty.className = 'text-center';
                tdQty.innerHTML =
                    '<input type="number" min="1" value="' + item.qty +
                    '" class="form-control form-control-sm qty-input" data-index="' + i + '">';

                var tdPrice = document.createElement('td');
                tdPrice.className = 'text-end';
                tdPrice.textContent = formatRupiah(item.price);

                var tdTotal = document.createElement('td');
                tdTotal.className = 'text-end';
                tdTotal.textContent = formatRupiah(lineTotal);

                var tdAction = document.createElement('td');
                tdAction.className = 'text-center';
                tdAction.innerHTML =
                    '<button type="button" class="btn btn-sm btn-danger remove-item-btn" data-index="' + i +
                    '">&times;</button>';

                tr.appendChild(tdName);
                tr.appendChild(tdQty);
                tr.appendChild(tdPrice);
                tr.appendChild(tdTotal);
                tr.appendChild(tdAction);

                cartTableBody.appendChild(tr);
            }

            var discount = calculatePromoDiscount(subtotal);
            var grandTotal = subtotal - discount;

            var elSub = document.getElementById('subtotal_display');
            var elDisc = document.getElementById('discount_display');
            var elGrand = document.getElementById('grand_total_display');

            if (elSub) elSub.innerText = formatRupiah(subtotal);
            if (elDisc) elDisc.innerText = formatRupiah(discount);
            if (elGrand) elGrand.innerText = formatRupiah(grandTotal);
        }

        // Tambah menu ke cart
        var addButtons = document.querySelectorAll('.add-to-cart-btn');
        for (var i = 0; i < addButtons.length; i++) {
            addButtons[i].addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                var name = this.getAttribute('data-name');
                var price = parseFloat(this.getAttribute('data-price')) || 0;

                var foundIndex = -1;
                for (var j = 0; j < cart.length; j++) {
                    if (String(cart[j].menu_item_id) === String(id)) {
                        foundIndex = j;
                        break;
                    }
                }

                if (foundIndex >= 0) {
                    cart[foundIndex].qty = cart[foundIndex].qty + 1;
                } else {
                    cart.push({
                        menu_item_id: id,
                        name: name,
                        price: price,
                        qty: 1
                    });
                }
                renderCart();
            });
        }

        // Ubah qty / hapus item di cart
        if (cartTableBody) {
            cartTableBody.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty-input')) {
                    var index = e.target.getAttribute('data-index');
                    var qty = parseInt(e.target.value, 10);
                    if (!qty || qty < 1) {
                        qty = 1;
                    }
                    cart[index].qty = qty;
                    renderCart();
                }
            });

            cartTableBody.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item-btn')) {
                    var index = e.target.getAttribute('data-index');
                    cart.splice(index, 1);
                    renderCart();
                }
            });
        }

        // Promo berubah
        if (promotionSelect) {
            promotionSelect.addEventListener('change', function() {
                renderCart();
            });
        }

        // Show/hide pilihan meja
        function toggleTable() {
            if (!orderTypeSelect || !tableWrapper) return;

            if (orderTypeSelect.value === 'dine_in') {
                tableWrapper.style.display = '';
            } else {
                tableWrapper.style.display = 'none';
            }
        }

        if (orderTypeSelect) {
            orderTypeSelect.addEventListener('change', toggleTable);
        }

        // Inisialisasi awal
        document.addEventListener('DOMContentLoaded', function() {
            toggleTable();
            renderCart();
        });

        // Submit: kirim cart sebagai hidden input
        if (posForm) {
            posForm.addEventListener('submit', function(e) {
                if (cart.length === 0) {
                    e.preventDefault();
                    alert('Keranjang masih kosong.');
                    return;
                }

                var oldHidden = document.querySelectorAll('.cart-hidden-input');
                for (var i = 0; i < oldHidden.length; i++) {
                    oldHidden[i].parentNode.removeChild(oldHidden[i]);
                }

                for (var idx = 0; idx < cart.length; idx++) {
                    var item = cart[idx];

                    var fields = ['menu_item_id', 'name', 'qty', 'price'];
                    for (var f = 0; f < fields.length; f++) {
                        var field = fields[f];
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'cart[' + idx + '][' + field + ']';
                        input.value = item[field];
                        input.className = 'cart-hidden-input';
                        posForm.appendChild(input);
                    }
                }
            });
        }
    </script>
@endsection
