<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Outlet;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $outletId = $request->input('outlet_id');
        $status   = $request->input('status');
        $search   = $request->input('q');

        $query = CafeTable::with('outlet')->orderBy('name');

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $tables  = $query->paginate(15);
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.tables.index', compact(
            'tables',
            'outlets',
            'outletId',
            'status',
            'search'
        ));
    }

    public function create()
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.tables.create', [
            'table'   => new CafeTable(),
            'outlets' => $outlets,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'name'      => 'required|string|max:50',
            'code'      => 'nullable|string|max:20|unique:cafe_tables,code',
            'capacity'  => 'required|integer|min:1',
            // sesuaikan enum di DB (mis: available, occupied, reserved, inactive)
            'status'    => 'required|in:available,occupied,reserved,inactive',
        ]);

        CafeTable::create($data);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Meja berhasil ditambahkan.');
    }

    public function edit(CafeTable $table)
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.tables.edit', [
            'table'   => $table,
            'outlets' => $outlets,
        ]);
    }

    public function update(Request $request, CafeTable $table)
    {
        $data = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'name'      => 'required|string|max:50',
            'code'      => 'nullable|string|max:20|unique:cafe_tables,code,' . $table->id,
            'capacity'  => 'required|integer|min:1',
            'status'    => 'required|in:available,occupied,reserved,inactive',
        ]);

        $table->update($data);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Meja berhasil diupdate.');
    }

    public function destroy(CafeTable $table)
    {
        // opsional: cek apakah meja sedang punya order aktif
        // if ($table->orders()->where('status', 'open')->exists()) { ... }

        $table->delete();

        return redirect()->route('admin.tables.index')
            ->with('success', 'Meja berhasil dihapus.');
    }
}
