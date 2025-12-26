@csrf

<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Outlet</label>
        <select name="outlet_id" class="form-select" required>
            <option value="">-- Pilih Outlet --</option>
            @foreach($outlets as $o)
                <option value="{{ $o->id }}"
                    @selected(old('outlet_id', $movement->outlet_id ?? null) == $o->id)>
                    {{ $o->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- <div class="col-md-4">
        <label class="form-label">Bahan Baku</label>
        <select name="ingredient_id" class="form-select" required>
            <option value="">-- Pilih Bahan --</option>
            @foreach($ingredients as $ing)
                <option value="{{ $ing->id }}"
                    @selected(old('ingredient_id', $movement->ingredient_id ?? null) == $ing->id)>
                    {{ $ing->name }} (stok: {{ $ing->stock }})
                </option>
            @endforeach
        </select>
    </div> --}}

    <div class="col-md-3">
        <label class="form-label">Jenis Pergerakan</label>
        <select name="movement_type" class="form-select" required>
            @php
                $mt = old('movement_type', $movement->movement_type ?? 'in');
            @endphp
            <option value="in"  @selected($mt === 'in')>IN (Stok Masuk)</option>
        </select>
    </div>
    <div class="col-md-3">
        <input type="hidden"
        name="purchase_price"
        value="{{ old('price', $movement->purchase_price ?? '') }}">
        <label class="form-label">Harga Beli</label>
        <input type="text"
            class="form-control rupiah-display"
            data-target="purchase_price"
            placeholder="Contoh: 15.000"
            value="{{ old('purchase_price', isset($movement) ? rupiah($movement->purchase_price) : '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Nama</label>
        <input type="text"
               name="namestock" class="form-control" placeholder="Input Milo/Kopi Kapal Api"
               value="{{ old('namestock', $movement->namestock ?? "") }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Satuan</label>
        <input type="text"
               name="satuan" class="form-control"
               value="{{ old('qty', $movement->satuan ?? 0) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Qty</label>
        <input type="number"
               name="qty" class="form-control"
               value="{{ old('qty', $movement->qty ?? 0) }}" required>
    </div>


    
    <div class="col-md-4">
        <label class="form-label">Tanggal Beli</label>
        <input type="datetime-local"
       name="created_at"
       value="{{ old('created_at', optional($movement->created_at)->format('Y-m-d\TH:i:s')) }}"
       class="form-control"
       step="1"
       required>

    </div>

    <div class="col-12">
        <label class="form-label">Deskripsi / Catatan</label>
        <textarea name="description" rows="3"
                  class="form-control"
                  placeholder="Catatan tambahan...">{{ old('description', $movement->description ?? '') }}</textarea>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-light">
        Batal
    </a>
    <button type="submit" class="btn btn-primary">
        {{ $submit ?? 'Simpan' }}
    </button>
</div>
