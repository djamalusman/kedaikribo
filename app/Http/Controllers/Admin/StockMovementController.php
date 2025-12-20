<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\Ingredient;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $outletId      = $request->input('outlet_id');
        $ingredientId  = $request->input('ingredient_id');
        $movementType  = $request->input('movement_type');
        $fromDate      = $request->input('from_date');
        $toDate        = $request->input('to_date');

        $query = StockMovement::with(['ingredient', 'outlet'])
            ->orderByDesc('created_at');

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        if ($ingredientId) {
            $query->where('ingredient_id', $ingredientId);
        }

        if ($movementType) {
            $query->where('movement_type', $movementType);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $movements   = $query->paginate(20);
        $outlets     = Outlet::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('admin.stock_movements.index', compact(
            'movements',
            'outlets',
            'ingredients',
            'outletId',
            'ingredientId',
            'movementType',
            'fromDate',
            'toDate'
        ));
    }

    public function create()
    {
        $outlets     = Outlet::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('admin.stock_movements.create', [
            'movement'    => new StockMovement(),
            'outlets'     => $outlets,
            'ingredients' => $ingredients,
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi input dasar
        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'outlet_id'     => 'required|exists:outlets,id',
            'movement_type' => 'required|in:in,out',
            'qty'           => 'required|numeric|min:0.001',
            'reference_type'=> 'nullable|string|max:50',
            'reference_no'  => 'nullable|string|max:100',
            'description'   => 'nullable|string',
        ]);

        // 2. Ambil ingredient dan cek outlet-nya
        $ingredient = Ingredient::findOrFail($data['ingredient_id']);

        // Jika outlet di form tidak sama dengan outlet di ingredient → error
        if ((int) $ingredient->outlet_id !== (int) $data['outlet_id']) {
            return back()
                ->withErrors([
                    'outlet_id' => 'Outlet yang dipilih tidak sesuai dengan outlet pemilik bahan baku: '
                        . $ingredient->name . '.',
                ])
                ->withInput();
        }

        // 3. Simpan movement + update stok dalam transaksi DB
        DB::transaction(function () use ($data) {
            /** @var \App\Models\StockMovement $movement */
            $movement = StockMovement::create($data);

            // Terapkan ke stok ingredient (IN = tambah, OUT = kurang)
            $movement->applyToIngredient();
        });

        return redirect()
            ->route('admin.stock-movements.index')
            ->with('success', 'Pergerakan stok berhasil dicatat.');
    }

    public function edit(StockMovement $stock_movement)
    {
        $outlets     = Outlet::orderBy('name')->get();
        $ingredients = Ingredient::orderBy('name')->get();

        return view('admin.stock_movements.edit', [
            'movement'    => $stock_movement,
            'outlets'     => $outlets,
            'ingredients' => $ingredients,
        ]);
    }

    public function update(Request $request, StockMovement $stock_movement)
    {
        // 1. Validasi input dasar
        $data = $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'outlet_id'     => 'required|exists:outlets,id',
            'movement_type' => 'required|in:in,out',
            'qty'           => 'required|numeric|min:0.001',
            'reference_type'=> 'nullable|string|max:50',
            'reference_no'  => 'nullable|string|max:100',
            'description'   => 'nullable|string',
        ]);

        // 2. Ambil ingredient dan cek outlet-nya
        $ingredient = Ingredient::findOrFail($data['ingredient_id']);

        if ((int) $ingredient->outlet_id !== (int) $data['outlet_id']) {
            return back()
                ->withErrors([
                    'outlet_id' => 'Outlet yang dipilih tidak sesuai dengan outlet pemilik bahan baku: '
                        . $ingredient->name . '.',
                ])
                ->withInput();
        }

        // 3. Koreksi stok lama, lalu terapkan stok baru dalam transaksi
        DB::transaction(function () use ($stock_movement, $data) {
            // Balik efek movement lama dari stok
            $stock_movement->revertFromIngredient();

            // Update movement
            $stock_movement->update($data);

            // Terapkan efek movement baru
            $stock_movement->refresh();
            $stock_movement->applyToIngredient();
        });

        return redirect()
            ->route('admin.stock-movements.index')
            ->with('success', 'Pergerakan stok berhasil diupdate.');
    }


    public function destroy(StockMovement $stock_movement)
    {
        DB::transaction(function () use ($stock_movement) {
            // Balikkan efek stok
            $stock_movement->revertFromIngredient();

            $stock_movement->delete();
        });

        return redirect()->route('admin.stock-movements.index')
            ->with('success', 'Pergerakan stok berhasil dihapus.');
    }
}
