@csrf
<div class="row">
    <div class="col-md-6 col-12">
        <div class="form-group mandatory">
            <label for="first-name-column" class="form-label">
                Name</label>
            <input type="text" name="name" class="form-control" name="fname-column" placeholder="Name"
                data-parsley-required="true" value="{{ old('name', $ingredient->name ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-6 col-12">
        <div class="form-group mandatory">
            <label class="form-label">Outlet</label>
            <select class="form-select" name="outlet_id">
                <option value="">Semua / Global</option>
                @foreach($outlets as $o)
                    <option value="{{ $o->id }}"
                        @selected(old('outlet_id', $ingredient->outlet_id ?? null) == $o->id)>
                        {{ $o->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
  
    <div class="col-md-4 col-12">
        <div class="form-group">
            <label for="last-name-column" class="form-label">
                Satuan</label>
            <input type="text" name="unit" class="form-control" placeholder="Satuan" name="unit"
                data-parsley-required="true" value="{{ old('unit', $ingredient->unit ?? '') }}" required>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            <label for="city-column" class="form-label">Stok</label>
            <input type="number" step="0.01" name="stock" class="form-control" placeholder="Satuan" name="stock"
                data-parsley-required="true" value="{{ old('stock', $ingredient->stock ?? 0) }}" required>
        </div>
    </div>
    <div class="col-md-4 col-12">
        <div class="form-group">
            <label for="country-floating" class="form-label">Stok Minimum</label>

            <input type="number" step="0.01" name="min_stock" class="form-control" placeholder="Stok Minimum"
                name="min_stock" data-parsley-required="true"
                value="{{ old('min_stock', $ingredient->min_stock ?? 0) }}" required>
        </div>
    </div>

</div>
<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.ingredients.index') }}" class="btn btn-light">Batal</a>
    <button class="btn btn-primary">{{ $submit ?? 'Simpan' }}</button>
</div>
