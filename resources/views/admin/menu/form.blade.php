@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nama Menu</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $menu->name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Code</label>
        <input type="text" name="code" id="code" placeholder="tidak perlu di input" readonly class="form-control"
            value="{{ old('code', $menu->code ?? '') }}">
    </div>
    {{-- HIDDEN nilai numeric --}}
    <input type="hidden"
        name="price"
        value="{{ old('price', $menu->price ?? '') }}">

    <div class="col-md-4">
        <label class="form-label">Harga</label>
        <input type="text"
            class="form-control rupiah-display"
            data-target="price"
            placeholder="Contoh: 15.000"
            value="{{ old('price', isset($menu) ? rupiah($menu->price) : '') }}">
    </div>
    <div class="col-md-6">
            <label class="form-label">Outlet</label>
            <select class="form-select" name="outlet_id">
                <option value="">Semua / Global</option>
                @foreach($outlets as $o)
                    <option value="{{ $o->id }}"
                        @selected(old('outlet_id', $menu->outlet_id ?? null) == $o->id)>
                        {{ $o->name }}
                    </option>
                @endforeach
            </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-select">
            <option value="">-</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}"
                    @selected(old('category_id', $menu->category_id ?? null) == $c->id)>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="2">{{ old('description', $menu->description ?? '') }}</textarea>
    </div>

    <div class="col-md-12">
        <label class="form-label">Gambar Menu (PNG / JPG)</label>

        <input type="file"
            name="image"
            id="imageInput"
            class="form-control"
            accept="image/png,image/jpeg">

        <div class="mt-2">
            <img id="imagePreview"
                src="{{ isset($menu) && $menu->image ? asset('storage/menu/'.$menu->image) : '' }}"
                class="img-thumbnail {{ isset($menu) && $menu->image ? '' : 'd-none' }}"
                style="max-height:150px">
        </div>

        @if(isset($menu))
            <small class="text-muted">
                Kosongkan jika tidak ingin mengganti gambar
            </small>
        @endif
    </div>




    <div class="col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                   value="1" @checked(old('is_active', $menu->is_active ?? true))>
            <label for="is_active" class="form-check-label">Aktif</label>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.menu.index') }}" class="btn btn-light">Batal</a>
    <button class="btn btn-primary">{{ $submit ?? 'Simpan' }}</button>
</div>
