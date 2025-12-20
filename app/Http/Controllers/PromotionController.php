<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\MenuItem;
use App\Models\Outlet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::with('outlet')->orderByDesc('start_date')->paginate(20);

        return view('admin.promotions.index', compact('promotions'));
    }

   public function create()
    {
        $outlets   = Outlet::orderBy('name')->get();
        $menuItems = MenuItem::where('is_active', 1)
            ->orderBy('name')
            ->get();

        return view('admin.promotions.create', [
            'promotion'          => new Promotion(),
            'outlets'            => $outlets,
            'menuItems'          => $menuItems,
            'selectedMenuItems'  => [],
        ]);
    }


    public function store(Request $request)
    {
        

                $validated = $request->validate([
                    'name'        => 'required|string|max:150',
                    'outlet_id'   => 'nullable|exists:outlets,id',
                    'type'        => 'required|in:percent,nominal',
                    'value'       => 'required|numeric|min:0',
                    'min_amount'  => 'nullable|numeric|min:0',
                    'is_active'   => 'sometimes|boolean',
                    'is_loyalty'  => 'sometimes|boolean',
                    'min_orders'  => 'nullable|integer|min:0',
                    'start_date'  => 'required|date',
                    'end_date'    => 'required|date|after_or_equal:start_date',

                    // MENU YANG KENA PROMO (tidak masuk ke tabel promotions)
                    'menu_item_id'   => 'nullable|array',
                    'menu_item_id.*' => 'exists:menu_items,id',
                ]);
                try {
                    // DATA UNTUK TABEL promotions SAJA
                    $promoData = [
                        'name'        => $validated['name'],
                        'outlet_id'   => $validated['outlet_id'] ?? null,
                        'type'        => $validated['type'],
                        'value'       => $validated['value'],
                        'min_amount'  => $validated['min_amount'] ?? null,
                        'is_active'   => $request->boolean('is_active'),
                        'is_loyalty'  => $request->boolean('is_loyalty'),
                        'min_orders'  => $validated['min_orders'] ?? null,
                        'start_date'  => $validated['start_date'],
                        'end_date'    => $validated['end_date'],
                    ];

                    DB::beginTransaction();
                    // 1) SIMPAN KE TABEL promotions
                    $promotion = Promotion::create($promoData);

                    // 2) SIMPAN KE TABEL PIVOT promotion_menu_items
                    $menuIds = $validated['menu_item_id'] ?? [];
                    $promotion->menuItems()->sync($menuIds);

                    DB::commit();

                    return redirect()->route('admin.promotions.index')
                        ->with('success', 'Promo berhasil dibuat.');

                }catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Gagal membuat Promo', [
                        'message' => $e->getMessage(),
                        'input'   => $request->except(['_token']),
                    ]);

                return back()->with('error', 'Gagal membuat data.');
        }
    }




    public function edit(Promotion $promotion)
    {
        $outlets   = Outlet::orderBy('name')->get();
        $menuItems = MenuItem::where('is_active', 1)
            ->orderBy('name')
            ->get();

        $selectedMenuItems = $promotion->menuItems()
            ->pluck('menu_items.id')
            ->toArray();

        return view('admin.promotions.edit', [
            'promotion'         => $promotion,
            'outlets'           => $outlets,
            'menuItems'         => $menuItems,
            'selectedMenuItems' => $selectedMenuItems,
        ]);
    }

    public function update(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'outlet_id'   => 'nullable|exists:outlets,id',
            'type'        => 'required|in:percent,nominal',
            'value'       => 'required|numeric|min:0',
            'min_amount'  => 'nullable|numeric|min:0',
            'is_active'   => 'sometimes|boolean',
            'is_loyalty'  => 'sometimes|boolean',
            'min_orders'  => 'nullable|integer|min:0',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',

            'menu_item_id'   => 'nullable|array',
            'menu_item_id.*' => 'exists:menu_items,id',
        ]);

        try {
                DB::beginTransaction();
                $promoData = [
                    'name'        => $validated['name'],
                    'outlet_id'   => $validated['outlet_id'] ?? null,
                    'type'        => $validated['type'],
                    'value'       => $validated['value'],
                    'min_amount'  => $validated['min_amount'] ?? null,
                    'is_active'   => $request->boolean('is_active'),
                    'is_loyalty'  => $request->boolean('is_loyalty'),
                    'min_orders'  => $validated['min_orders'] ?? null,
                    'start_date'  => $validated['start_date'],
                    'end_date'    => $validated['end_date'],
                ];

                $promotion->update($promoData);

                $menuIds = $validated['menu_item_id'] ?? [];
                $promotion->menuItems()->sync($menuIds);

                DB::commit();

                return redirect()->route('admin.promotions.index')
                    ->with('success', 'Promo berhasil diupdate.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal update promo', [
                'promotion_id' => $promotion->id,
                'message'      => $e->getMessage(),
                'input'        => $request->except(['_token']),
            ]);

            return back()->with('error', 'Gagal menyimpan perubahan.')->withInput();
        }
    }


    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promo dihapus.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:150',
            'outlet_id'   => 'nullable|exists:outlets,id',
            'type'        => 'required|in:percent,fixed',   // sesuaikan enum di DB
            'value'       => 'required|numeric|min:0',
            'min_amount'  => 'nullable|numeric|min:0',
            'is_active'   => 'sometimes|boolean',
            'is_loyalty'  => 'sometimes|boolean',
            'min_orders'  => 'nullable|integer|min:0',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',

            // menu yang kena promo (boleh kosong)
            'menu_item_id'   => 'nullable|array',
            'menu_item_id.*' => 'exists:menu_items,id',
        ], [], [
            'value'      => 'nilai promo',
            'min_amount' => 'minimal transaksi',
        ]) + [
            'is_active'  => $request->boolean('is_active'),
            'is_loyalty' => $request->boolean('is_loyalty'),
        ];
    }
}
