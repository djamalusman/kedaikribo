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
                        <div class="col-md-4">
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
                        <div class="col-md-4" id="table-wrapper">
                            <label class="form-label">Meja (untuk Dine In) &nbsp;&nbsp;
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#inlineForm">
                                    Update Status Meja
                                </button></label>
                            <select name="table_id" class="form-select" id="table_id">
                                <option value="">Pilih Meja</option>
                                @foreach ($tables as $t)
                                    <option value="{{ $t->id }}-{{ $t->status }}">
                                        {{ $t->name }} ({{ $t->status }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_reserved" name="is_reserved"
                                    value="reserved">
                                <label class="form-check-label" for="is_reserved">
                                    Reserved
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    <div class="row g-2 mb-3" id="reserve-date-wrapper" style="display:none">
                        <div class="col-md-4" id="dp-wrapper" style="display:none">
                            <label class="form-label">Nominal DP</label>
                            <input type="text" name="nominal_dp" class="form-control rupiah-display"
                                data-target="nominal_dp" placeholder="Contoh: 15.000" </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="datetime-local" name="start_date" class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="datetime-local" name="end_date" class="form-control">
                        </div>
                    </div>


                    <!--Basic Modal -->
                    <div class="modal fade" id="inlineForm" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h4 class="modal-title">Update Status Meja</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <label>Meja</label>
                                    <select id="table_id_update" class="form-select">
                                        <option value="">Pilih Meja</option>

                                        @foreach ($tables as $t)
                                            @if ($t->status === 'occupied')
                                                <option value="{{ $t->id }}" data-status="{{ $t->status }}">
                                                    {{ $t->name }} ({{ $t->status }})
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>


                                    <label class="mt-2">Status</label>
                                    <select id="table_status_update" class="form-select">
                                        <option value="">Pilih Status</option>
                                        <option value="available">Available</option>
                                    </select>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button class="btn btn-primary" id="btnUpdateTableStatus">
                                        Update
                                    </button>
                                </div>

                            </div>
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
                                                        data-id="{{ $menu->id }}" data-name="{{ $menu->name }}"
                                                        data-price="{{ $menu->price }}">

                                                        {{-- Gambar --}}
                                                        <div class="ratio ratio-1x1"style="height:250px;">
                                                            <img src="{{ $menu->image ? asset('storage/menu/' . $menu->image) : asset('images/no-image.png') }}"
                                                                class="img-fluid object-fit-cover"
                                                                alt="{{ $menu->name }}">
                                                        </div>

                                                        {{-- Info --}}
                                                        <div class="p-2">
                                                            <div class="fw-semibold text-truncate">{{ $menu->name }}
                                                            </div>
                                                            <div class="small text-muted">{{ rupiah($menu->price) }}
                                                            </div>
                                                        </div>
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <span class="text-muted small">Tidak ada menu di kategori
                                                        ini.</span>
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
        document.addEventListener('DOMContentLoaded', function() {

            console.log('POS Create JS loaded');

            /* =========================================================
             * HELPER (ANTI NULL)
             * ========================================================= */
            function on(el, event, handler) {
                if (el) el.addEventListener(event, handler);
            }

            function setText(id, value) {
                const el = document.getElementById(id);
                if (el) el.innerText = value;
            }

            /* =========================================================
             * ELEMENTS
             * ========================================================= */
            const form = document.getElementById('pos-form');
            const cartTableBody = document.querySelector('#cart-table tbody');
            const promotionSelect = document.getElementById('promotion_id');

            const orderTypeSelect = document.getElementById('order_type');
            const tableWrapper = document.getElementById('table-wrapper');
            const tableSelect = document.getElementById('table_id');

            // 🔴 TAB MENU (MENU LIST)
            const tabContent = document.querySelector('.tab-content');

            // 🔴 RESERVED CHECKBOX
            const reservedCheckbox = document.getElementById('is_reserved');

            // 🔴 DP
            const dpWrapper = document.getElementById('dp-wrapper');
            const dpInput = dpWrapper ?
                dpWrapper.querySelector('input[name="nominal_dp"]') :
                null;

            // 🔴 RESERVE DATE
            const reserveDateWrapper = document.getElementById('reserve-date-wrapper');
            const startDateInput = reserveDateWrapper ?
                reserveDateWrapper.querySelector('input[name="start_date"]') :
                null;
            const endDateInput = reserveDateWrapper ?
                reserveDateWrapper.querySelector('input[name="end_date"]') :
                null;

            /* =========================================================
             * DATA
             * ========================================================= */
            const cart = [];
            const promos = @json($promos);

            /* =========================================================
             * UTIL
             * ========================================================= */
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
                if (!promotionSelect || !promotionSelect.value) return 0;

                const promo = findPromoById(promotionSelect.value);
                if (!promo) return 0;

                if (promo.min_amount && subtotal < Number(promo.min_amount)) return 0;

                let discount = promo.type === 'percent' ?
                    subtotal * (Number(promo.value) / 100) :
                    Number(promo.value);

                return Math.min(discount, subtotal);
            }

            /* =========================================================
             * CART
             * ========================================================= */
            function renderCart() {
                if (!cartTableBody) return;

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

                setText('subtotal_display', formatRupiah(subtotal));
                setText('discount_display', formatRupiah(discount));
                setText('grand_total_display', formatRupiah(grandTotal));
            }

            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name;
                    const price = parseFloat(this.dataset.price) || 0;

                    let item = cart.find(c => c.menu_item_id === id);
                    if (item) {
                        item.qty++;
                    } else {
                        cart.push({
                            menu_item_id: id,
                            name,
                            price,
                            qty: 1
                        });
                    }
                    renderCart();
                });
            });

            on(cartTableBody, 'input', function(e) {
                if (e.target.classList.contains('qty-input')) {
                    cart[e.target.dataset.index].qty = parseInt(e.target.value) || 1;
                    renderCart();
                }
            });

            on(cartTableBody, 'click', function(e) {
                if (e.target.classList.contains('remove-item-btn')) {
                    cart.splice(e.target.dataset.index, 1);
                    renderCart();
                }
            });

            on(promotionSelect, 'change', renderCart);

            /* =========================================================
             * TABLE / RESERVED / DP / DATE
             * ========================================================= */
            function toggleTable() {
                if (!orderTypeSelect || !tableWrapper) return;
                tableWrapper.style.display =
                    orderTypeSelect.value === 'dine_in' ? '' : 'none';
            }

            function toggleReservedUI() {
                if (!reservedCheckbox) return;

                const isReserved = reservedCheckbox.checked;

                if (isReserved) {
                    if (dpWrapper) dpWrapper.style.display = 'block';
                    if (reserveDateWrapper) reserveDateWrapper.style.display = 'flex';

                    if (tabContent) tabContent.style.display = 'none';
                } else {
                    if (dpWrapper) dpWrapper.style.display = 'none';
                    if (reserveDateWrapper) reserveDateWrapper.style.display = 'none';

                    if (dpInput) dpInput.value = '';
                    if (startDateInput) startDateInput.value = '';
                    if (endDateInput) endDateInput.value = '';
                    if (tabContent) tabContent.style.display = '';
                }
            }

            on(orderTypeSelect, 'change', toggleTable);
            on(reservedCheckbox, 'change', toggleReservedUI);

            toggleTable();
            toggleReservedUI();

            /* =========================================================
             * SUBMIT VALIDATION
             * ========================================================= */
            on(form, 'submit', function(e) {

                const isReserved = reservedCheckbox && reservedCheckbox.checked;

                // 🔴 Jika reserved → meja wajib
                if (isReserved) {
                    if (!tableSelect || !tableSelect.value) {
                        e.preventDefault();
                        alert('Jika Reserved, silakan pilih meja.');
                        return;
                    }
                }

                // 🔴 Jika reserved → DP wajib
                if (isReserved) {
                    if (!dpInput || !dpInput.value) {
                        e.preventDefault();
                        alert('Nominal DP wajib diisi.');
                        return;
                    }
                }

                // 🔴 Jika reserved → tanggal wajib
                if (isReserved) {
                    if (!startDateInput || !startDateInput.value) {
                        e.preventDefault();
                        alert('Start Date wajib diisi.');
                        return;
                    }

                    if (!endDateInput || !endDateInput.value) {
                        e.preventDefault();
                        alert('End Date wajib diisi.');
                        return;
                    }

                    if (new Date(endDateInput.value) <= new Date(startDateInput.value)) {
                        e.preventDefault();
                        alert('End Date harus lebih besar dari Start Date.');
                        return;
                    }
                }

                // 🔴 Jika bukan reserved → cart wajib
                if (!isReserved && cart.length === 0) {
                    e.preventDefault();
                    alert('Keranjang masih kosong.');
                    return;
                }

                // 🔴 Inject cart
                document.querySelectorAll('.cart-hidden-input').forEach(el => el.remove());

                cart.forEach((item, index) => {
                    ['menu_item_id', 'name', 'qty', 'price'].forEach(field => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `cart[${index}][${field}]`;
                        input.value = item[field];
                        input.classList.add('cart-hidden-input');
                        form.appendChild(input);
                    });
                });
            });

        });
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function () {

    document.getElementById('btnUpdateTableStatus')
        .addEventListener('click', async function (e) {

        e.preventDefault();

        const tableId = document.getElementById('table_id_update').value;
        const status  = document.getElementById('table_status_update').value;

        if (!tableId || !status) {
            Swal.fire({
                icon: 'warning',
                title: 'Data belum lengkap',
                text: 'Meja dan status wajib dipilih'
            });
            return;
        }

        try {
            const res = await fetch("{{ route('kasir.tables.updateStatus') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({
                    table_id: tableId,
                    status: status
                })
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Gagal memperbarui status meja');
            }

            // ✅ SUCCESS
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message || 'Status meja berhasil diperbarui',
                timer: 1500,
                showConfirmButton: false
            });

            // tutup modal
            const modalEl = document.getElementById('inlineForm');
            bootstrap.Modal.getInstance(modalEl).hide();

            // reload halaman (opsional)
            setTimeout(() => {
                location.reload();
            }, 1500);

        } catch (err) {

            // ❌ ERROR
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message
            });
        }
    });

});
</script>



@endsection
