@csrf

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Outlet</label>
        <select name="outlet_id" class="form-select" required>
            <option value="">-- Pilih Outlet --</option>
            @foreach($outlets as $o)
                <option value="{{ $o->id }}"
                    @selected(old('outlet_id', $table->outlet_id ?? null) == $o->id)>
                    {{ $o->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Nama Meja</label>
        <input type="text" name="name" class="form-control"
               placeholder="Contoh: Meja 1"
               value="{{ old('name', $table->name ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Kode Meja</label>
        <input type="text" name="code" class="form-control"
               placeholder="Contoh: T01"
               value="{{ old('code', $table->code ?? '') }}">
        <small class="text-muted">Optional, untuk kode internal.</small>
    </div>

    <div class="col-md-3">
        <label class="form-label">Kapasitas</label>
        <input type="number" min="1" name="capacity" class="form-control"
               placeholder="Jumlah kursi"
               value="{{ old('capacity', $table->capacity ?? 1) }}" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Status Meja</label>
        @php
            $currentStatus = old('status', $table->status ?? 'available');
        @endphp
        <select name="status" class="form-select" required>
            <option  value="available" @selected($currentStatus === 'available')>Available</option>
            <option hidden value="occupied"  @selected($currentStatus === 'occupied')>Occupied</option>
            <option hidden value="reserved"  @selected($currentStatus === 'reserved')>Reserved</option>
            <option hidden value="inactive"  @selected($currentStatus === 'inactive')>Inactive</option>
        </select>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.tables.index') }}" class="btn btn-light">
        Batal
    </a>
    <button type="submit" class="btn btn-primary">
        {{ $submit ?? 'Simpan' }}
    </button>
</div>
