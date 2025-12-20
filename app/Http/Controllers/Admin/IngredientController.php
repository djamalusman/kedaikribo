<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Outlet;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        // tampilkan bahan + outlet-nya (kalau ada)
        $ingredients = Ingredient::with('outlet')
            ->orderBy('name')
            ->paginate(15);
            
        return view('admin.ingredients.index', compact('ingredients'));
    }

    public function create()
    {
        // untuk pilih outlet (jika multi-outlet)
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.ingredients.create', compact('outlets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'unit'      => 'required|string|max:50',
            'stock'     => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'outlet_id' => 'nullable|exists:outlets,id', // kalau pakai outlet_id
        ]);

        try {
                DB::beginTransaction();
                Ingredient::create($data); // guarded = [] → aman
                DB::commit();
                return redirect()->route('admin.ingredients.index')
                    ->with('success', 'Bahan baku berhasil ditambahkan.');
        }catch (\Throwable $e) {
                 DB::rollBack();
                    Log::error('Gagal membuat Bahan Baku', [
                        'message' => $e->getMessage(),
                        'input'   => $request->except(['_token']),
                    ]);

                return back()->with('error', 'Gagal membuat data.');
            }
    }

    public function edit(Ingredient $ingredient)
    {
        $outlets = Outlet::orderBy('name')->get();

        return view('admin.ingredients.edit', compact('ingredient', 'outlets'));
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:150',
            'unit'      => 'required|string|max:50',
            'stock'     => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'outlet_id' => 'nullable|exists:outlets,id',
        ]);

        try {
                DB::beginTransaction();
                $ingredient->update($data);
                 DB::commit();
                return redirect()->route('admin.ingredients.index')
                    ->with('success', 'Bahan baku berhasil diupdate.');
        }catch (\Throwable $e) {
             DB::rollBack();
                Log::error('Gagal update bahan baku', [
                    'ingredient_id' => $ingredient->id,
                    'message'      => $e->getMessage(),
                    'input'        => $request->except(['_token']),
                ]);

                return back()->with('error', 'Gagal menyimpan perubahan.')->withInput();
        }
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('admin.ingredients.index')
            ->with('success', 'Bahan baku dihapus.');
    }
}
