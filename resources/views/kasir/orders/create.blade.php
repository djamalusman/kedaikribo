@extends('layouts.app')

@section('title', 'Transaksi Baru')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('kasir.orders.store') }}" id="pos-form">
                    @csrf

                    {{-- DATA PELANGGAN --}}
                    <h5 class="mb-2">Data Pelanggan</h5>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama Customer</label>
                            <input type="text" name="customer_name" class="form-control"
                                value="{{ old('customer_name') }}" placeholder="Nama pelanggan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">No. HP</label>
                            <input type="text" name="customer_phone" class="form-control"
                                value="{{ old('customer_phone') }}" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" class="form-control"
                                value="{{ old('customer_email') }}" placeholder="email@contoh.com">
                        </div>
                    </div>

                    <hr>

                    {{-- HEADER ORDER --}}
                    <h5 class="mb-2">Informasi Order</h5>
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Tipe Order</label>
                            <select name="order_type" id="order_type" class="form-select" required>
                                <option value="dine_in" {{ old('order_type') === 'dine_in' ? 'selected' : '' }}>Dine In
                                </option>
                                <option value="take_away" {{ old('order_type') === 'take_away' ? 'selected' : '' }}>Take
                                    Away</option>
                                <option value="delivery" {{ old('order_type') === 'delivery' ? 'selected' : '' }}>Delivery
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3" id="table-wrapper">
                            <label class="form-label">Meja (untuk Dine In)</label>
                            <select name="table_id" class="form-select">
                                <option value="">Pilih Meja</option>
                                @foreach ($tables as $t)
                                    <option value="{{ $t->id }}-{{ $t->status }}" {{ old('table_id') == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} ({{ $t->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Promo</label>
                            <select name="promotion_id" id="promotion_id" class="form-select">
                                <option value="">Tidak ada</option>
                                @foreach ($promotions as $promo)
                                    <option value="{{ $promo->id }}"
                                        {{ old('promotion_id') == $promo->id ? 'selected' : '' }}>
                                        {{ $promo->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block">Diskon dihitung otomatis dari promo.</small>
                        </div>
                    </div>

                    <hr>

                    {{-- MENU & KERANJANG --}}
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <h5 class="mb-2">Menu</h5>

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

                            <div class="tab-content">
                                @foreach ($categories as $index => $cat)
                                    <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                                        id="cat-{{ $cat->id }}" role="tabpanel">
                                        <div class="row g-2">
                                            @forelse($cat->menuItems as $menu)
                                            <div class="col-6 col-md-2 mb-6">
                                                <button type="button"
                                                    class="btn btn-outline-secondary w-100 text-start add-to-cart-btn p-0 overflow-hidden"
                                                    data-id="{{ $menu->id }}"
                                                    data-name="{{ $menu->name }}"
                                                    data-price="{{ $menu->price }}">

                                                    {{-- Gambar --}}
                                                    <div class="ratio ratio-1x1">
                                                        <img src="{{ $menu->image
                                                                ? asset('storage/menu/' . $menu->image)
                                                                : asset('images/no-image.png') }}"
                                                            class="img-fluid object-fit-cover"
                                                            alt="{{ $menu->name }}">
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

                        <div class="col-lg-12">
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
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal</span>
                                <span id="subtotal_display">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Diskon (Promo)</span>
                                <span id="discount_display">Rp 0</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Grand Total</strong>
                                <strong id="grand_total_display">Rp 0</strong>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Simpan Bill (OPEN)
                            </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
                <script>
                    console.log('POS Create JS loaded');

                    const cart = [];
                    const cartTableBody = document.querySelector('#cart-table tbody');
                    const promotionSelect = document.getElementById('promotion_id');

                    // data promo ke JS
                    const promos = @json($promos);

                    function findPromoById(id) {
                        return promos.find(p => String(p.id) === String(id)) || null;
                    }

                    function formatRupiah(num) {
                        num = Number(num) || 0;
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 0
                        }).format(num);
                    }

                    function calculatePromoDiscount(subtotal) {
                        const promoId = promotionSelect.value;
                        if (!promoId) return 0;

                        const promo = findPromoById(promoId);
                        if (!promo) return 0;

                        const minAmount = promo.min_amount ? Number(promo.min_amount) : 0;
                        if (minAmount > 0 && subtotal < minAmount) {
                            return 0;
                        }

                        let discount = 0;
                        if (promo.type === 'percent') {
                            discount = subtotal * (Number(promo.value) / 100);
                        } else {
                            discount = Number(promo.value);
                        }

                        if (discount > subtotal) discount = subtotal;
                        return discount;
                    }

                    function renderCart() {
                        cartTableBody.innerHTML = '';
                        let subtotal = 0;

                        cart.forEach((item, index) => {
                            const lineTotal = item.qty * item.price;
                            subtotal += lineTotal;

                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                <td>${item.name}</td>
                <td class="text-center">
                    <input type="number" min="1" value="${item.qty}"
                           class="form-control form-control-sm qty-input"
                           data-index="${index}">
                </td>
                <td class="text-end">${formatRupiah(item.price)}</td>
                <td class="text-end">${formatRupiah(lineTotal)}</td>
                <td class="text-end">
                    <button type="button"
                            class="btn btn-sm btn-danger remove-item-btn"
                            data-index="${index}">&times;</button>
                </td>
            `;
                            cartTableBody.appendChild(tr);
                        });

                        const discount = calculatePromoDiscount(subtotal);
                        const grandTotal = subtotal - discount;

                        document.getElementById('subtotal_display').innerText = formatRupiah(subtotal);
                        document.getElementById('discount_display').innerText = formatRupiah(discount);
                        document.getElementById('grand_total_display').innerText = formatRupiah(grandTotal);
                    }

                    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const name = this.dataset.name;
                            const price = parseFloat(this.dataset.price) || 0;

                            let item = cart.find(c => c.menu_item_id === id);
                            if (item) {
                                item.qty += 1;
                            } else {
                                cart.push({
                                    menu_item_id: id,
                                    name: name,
                                    price: price,
                                    qty: 1,
                                });
                            }
                            renderCart();
                        });
                    });

                    cartTableBody.addEventListener('input', function(e) {
                        if (e.target.classList.contains('qty-input')) {
                            const index = e.target.dataset.index;
                            const qty = parseInt(e.target.value) || 1;
                            cart[index].qty = qty;
                            renderCart();
                        }
                    });

                    cartTableBody.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-item-btn')) {
                            const index = e.target.dataset.index;
                            cart.splice(index, 1);
                            renderCart();
                        }
                    });

                    promotionSelect.addEventListener('change', renderCart);

                    const orderTypeSelect = document.getElementById('order_type');
                    const tableWrapper = document.getElementById('table-wrapper');

                    // validasi meja
                    const tableSelect = document.getElementById('table_id');
                    const selectedOption = tableSelect.options[tableSelect.selectedIndex];
                    

                    function toggleTable() {
                        if (orderTypeSelect.value === 'dine_in') {
                            tableWrapper.style.display = '';
                        } else {
                            tableWrapper.style.display = 'none';
                        }
                    }
                    orderTypeSelect.addEventListener('change', toggleTable);
                    toggleTable();

                    document.getElementById('pos-form').addEventListener('submit', function(e) {
                        if (selectedOption && selectedOption.dataset.status === 'reserved') {
                            e.preventDefault();
                            alert('Meja yang dipilih sedang reserved.');
                            return false;
                        }
                        else if (cart.length === 0) {
                            e.preventDefault();
                            alert('Keranjang masih kosong.');
                            return;
                        }
                        else {
                            tableWrapper.style.display = 'none';
                        }

                        document.querySelectorAll('.cart-hidden-input').forEach(el => el.remove());

                        cart.forEach((item, index) => {
                            ['menu_item_id', 'name', 'qty', 'price'].forEach(field => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = `cart[${index}][${field}]`;
                                input.value = item[field];
                                input.classList.add('cart-hidden-input');
                                document.getElementById('pos-form').appendChild(input);
                            });
                        });
                    });
                </script>
            @endsection
