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
        $data = $request->validate([
            'outlet_id'      => 'required|exists:outlets,id',
            'movement_type'  => 'required|in:in',
            'qty'            => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0.001',
            'satuan'         => 'nullable|string|max:50',
            'namestock'      => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'created_at'     => 'required|date',
        ]);

        DB::transaction(function () use ($data) {
            StockMovement::create($data);
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
        $data = $request->validate([
            'outlet_id'      => 'required|exists:outlets,id',
            'movement_type'  => 'required|in:in',
            'qty'            => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0.001',
            'satuan'         => 'nullable|string|max:50',
            'namestock'      => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'created_at'     => 'required|date',
        ]);

        DB::transaction(function () use ($stock_movement, $data) {
            $stock_movement->update($data);
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
