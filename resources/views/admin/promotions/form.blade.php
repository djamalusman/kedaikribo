@csrf

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Promo</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $promotion->name ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Outlet</label>
        <select name="outlet_id" class="form-select">
            <option value="">Semua Outlet</option>
            @foreach($outlets as $o)
                <option value="{{ $o->id }}"
                    @selected(old('outlet_id', $promotion->outlet_id ?? null) == $o->id)>
                    {{ $o->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Tipe Promo</label>
        <select name="type" id="promo_type" class="form-select" required>
            <option value="percent" @selected(old('type', $promotion->type ?? '') === 'percent')>
                % (Persentase)
            </option>
            <option value="nominal" @selected(old('type', $promotion->type ?? '') === 'nominal')>
                Rp (Nominal)
            </option>
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label" id="label_promo_value">Nilai</label>

        {{-- Hidden: nilai numeric yang dikirim ke server --}}
        <input type="hidden"
            name="value"
            id="promo_value"
            value="{{ old('value', $promotion->value ?? '') }}">

        {{-- Tampilan: berubah fungsi sesuai tipe promo --}}
        <input type="text"
            id="promo_value_display"
            class="form-control"
            placeholder="Isi nilai promo">

        <small class="text-muted" id="help_promo_value"></small>
    </div>

    {{-- <div class="col-md-3">
        <label class="form-label">Min. Belanja (Rp)</label>
        <input type="number" step="0.01" name="min_amount" class="form-control"
               value="{{ old('min_amount', $promotion->min_amount ?? 0) }}">
    </div> --}}

    <input type="hidden"
        name="min_amount"
        value="{{ old('min_amount', $promotion->min_amount ?? 0) }}">

    <div class="col-md-3">
        <label class="form-label">Min. Belanja (Rp)</label>
        <input type="text"
            class="form-control rupiah-display"
            data-target="min_amount"
            placeholder="Contoh: 15.000"
            value="{{ old('min_amount', isset($promotion) ? rupiah($promotion->min_amount) : '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">Aktif?</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   @checked(old('is_active', $promotion->is_active ?? 1))>
            <label class="form-check-label">Ya</label>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">Promo Loyalty?</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_loyalty" value="1"
                   @checked(old('is_loyalty', $promotion->is_loyalty ?? 0))>
            <label class="form-check-label">Ya (butuh min. order)</label>
        </div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Min. Jumlah Order</label>
        <input type="number" name="min_orders" class="form-control"
               value="{{ old('min_orders', $promotion->min_orders ?? 0) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Mulai</label>
        <input type="date" name="start_date" class="form-control"
               value="{{ old('start_date', $promotion->start_date ?? '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Sampai</label>
        <input type="date" name="end_date" class="form-control"
               value="{{ old('end_date', $promotion->end_date ?? '') }}" required>
    </div>

    {{-- INI BAGIAN PENTING: PILIH MENU YANG KENA PROMO --}}
    <div class="col-md-12">
        <label class="form-label">Berlaku untuk Menu</label>
        <select name="menu_item_id[]" class="choices form-select multiple-remove" multiple="multiple">
            @php
                $selected = old('menu_item_id', $selectedMenuItems ?? []);
            @endphp

            @foreach($menuItems as $item)
                <option value="{{ $item->id }}"
                    @selected(in_array($item->id, $selected))>
                    {{ $item->name }} ({{ rupiah($item->price ?? 0) }})
                </option>
            @endforeach
        </select>
        <small class="text-muted">
            Kosongkan kalau promo ini berlaku untuk semua menu.
        </small>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.promotions.index') }}" class="btn btn-light">Batal</a>
    <button class="btn btn-primary">{{ $submit ?? 'Simpan' }}</button>
</div>
