@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $cashier->name ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
               value="{{ old('email', $cashier->email ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
               value="{{ old('email', $cashier->password ?? '') }}" required>
    </div>
    <div class="col-md-4" hidden>
        <label class="form-label">Outlet</label>
        <input type="text" name="outlet_id" class="form-control"
               value="1" required>
    </div>
</div>

<div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.cashiers.index') }}" class="btn btn-light">Batal</a>
    <button class="btn btn-primary">{{ $submit ?? 'Simpan' }}</button>
</div>
