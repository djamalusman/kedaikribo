@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Stock</label>
        <select class="form-select" name="namestock" id="namestock">
            <option value="">Pilih Stock</option>

            @foreach ($stock as $so)
                <option value="{{ $so->id }}"
                    @selected(
                        old('namestock', $menu->stock_id ?? '') == $so->id
                    )>
                    {{ $so->namestock }}
                </option>
            @endforeach
        </select>


        <small class="text-muted">
            Pilih Stock untuk menjadi menu
        </small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Nama Menu</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $menu->name ?? '') }}" >
    </div>
    <div class="col-md-4">
        <label class="form-label">Code</label>
        <input type="text" name="code" id="code" placeholder="tidak perlu di input" readonly
            class="form-control" value="{{ old('code', $menu->code ?? '') }}">
    </div>
    {{-- HIDDEN nilai numeric --}}
    <input type="hidden" name="price" value="{{ old('price', $menu->price ?? '') }}">

    <div class="col-md-4">
        <label class="form-label">Harga</label>
        <input type="text" class="form-control rupiah-display" data-target="price" placeholder="Contoh: 15.000"
            value="{{ old('price', isset($menu) ? rupiah($menu->price) : '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Outlet</label>
        <select class="form-select" name="outlet_id">
            @foreach ($outlets as $o)
                <option value="{{ $o->id }}" @selected(old('outlet_id', $menu->outlet_id ?? null) == $o->id)>
                    {{ $o->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Kategori Menu</label>
        <select name="category_id" class="form-select">
            <option value="">Pilih Kategori Menu</option>
            @foreach ($categories as $c)
                <option value="{{ $c->id }}" @selected(old('category_id', $menu->category_id ?? null) == $c->id)>
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

        <input type="file" name="image" id="imageInput" class="form-control" accept="image/png,image/jpeg">

        <div class="mt-2">
            <img id="imagePreview" src="{{ isset($menu) && $menu->image ? asset('storage/menu/' . $menu->image) : '' }}"
                class="img-thumbnail {{ isset($menu) && $menu->image ? '' : 'd-none' }}" style="max-height:150px">
        </div>

        @if (isset($menu))
            <small class="text-muted">
                Kosongkan jika tidak ingin mengganti gambar
            </small>
        @endif
    </div>




    <div class="col-md-6">
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                @checked(old('is_active', $menu->is_active ?? true))>
            <label for="is_active" class="form-check-label">Aktif</label>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.menu.index') }}" class="btn btn-light">Batal</a>
    <button class="btn btn-primary">{{ $submit ?? 'Simpan' }}</button>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const stockSelect = document.getElementById('namestock');

        // ambil wrapper kolom supaya label + input ikut hide
        const namaMenuCol = document.querySelector('input[name="name"]').closest('.col-md-4');
        const codeCol = document.querySelector('input[name="code"]').closest('.col-md-4');
        const descCol = document.querySelector('textarea[name="description"]').closest('.col-md-12');

        function toggleMenuFields() {
            if (stockSelect.value) {
                // stock dipilih → hide
                namaMenuCol.classList.add('d-none');
                codeCol.classList.add('d-none');
                descCol.classList.add('d-none');
            } else {
                // stock kosong → show
                namaMenuCol.classList.remove('d-none');
                codeCol.classList.remove('d-none');
                descCol.classList.remove('d-none');
            }
        }

        // trigger saat select berubah
        stockSelect.addEventListener('change', toggleMenuFields);

        // trigger saat page reload (edit / old value)
        toggleMenuFields();
    });
</script>
