@csrf

<div class="row g-3">
    <div class="col-md-4">
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

    <div class="col-md-4">
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
    </div>

    <div class="col-md-4">
        <label class="form-label">Jenis Pergerakan</label>
        <select name="movement_type" class="form-select" required>
            @php
                $mt = old('movement_type', $movement->movement_type ?? 'in');
            @endphp
            <option value="in"  @selected($mt === 'in')>IN (Stok Masuk)</option>
            <option value="out" @selected($mt === 'out')>OUT (Stok Keluar)</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Qty</label>
        <input type="number" step="0.001" min="0.001"
               name="qty" class="form-control"
               value="{{ old('qty', $movement->qty ?? 0) }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Jenis Referensi</label>
        <input type="text" name="reference_type" class="form-control"
               placeholder="PO, WASTE, ADJUST, dll"
               value="{{ old('reference_type', $movement->reference_type ?? '') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">No. Referensi</label>
        <input type="text" name="reference_no" class="form-control"
               placeholder="No PO / Dokumen"
               value="{{ old('reference_no', $movement->reference_no ?? '') }}">
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
