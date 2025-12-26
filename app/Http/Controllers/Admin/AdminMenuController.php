<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Outlet;
use App\Models\Category; // kalau ada kategori
use App\Models\OrderItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class AdminMenuController extends Controller
{
    public function index()
    {
        //outlet
        $menus = MenuItem::with(['category', 'outlet'])
        ->orderByDesc('is_active')
        ->orderBy('name')
        ->paginate(15);
        


        return view('admin.menu.index', compact('menus'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $outlets = Outlet::orderBy('name')->get();
        $stock = StockMovement::get();
         return view('admin.menu.create', compact('categories', 'outlets','stock'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'nullable|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'code'        => 'nullable|string',
            'namestock'   => 'nullable|exists:stock_movements,id',
            'is_active'   => 'sometimes|boolean',
            'outlet_id'   => 'nullable|exists:outlets,id',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data['is_active'] = $request->boolean('is_active');

            if (empty($data['code'])) {
                $data['code'] = $this->generateMenuCode($data['category_id']);
            }

            // 📤 Upload image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = uniqid().'_'.$file->getClientOriginalName();
                $file->storeAs('menu', $filename, 'public');
                $data['image'] = $filename;
            }

            // ===== JIKA AMBIL DARI STOCK =====
            if (!empty($data['namestock'])) {

                $stock = StockMovement::find($data['namestock']);

                $menu = MenuItem::create([
                    'name'        => $stock->namestock,
                    'stock_id'    => $stock->id,
                    'category_id' => $data['category_id'],
                    'price'       => $data['price'],
                    'description' => $data['description'],
                    'code'        => $data['code'],
                    'outlet_id'        => $data['outlet_id'],
                    'is_active'   => $data['is_active'],
                    'image'       => $data['image'] ?? null,
                ]);

            } 
            // ===== MANUAL INPUT =====
            else {

                $menu = MenuItem::create([
                    'name'        => $data['name'],
                    'category_id' => $data['category_id'],
                    'price'       => $data['price'],
                    'description' => $data['description'],
                    'code'        => $data['code'],
                    'outlet_id'        => $data['outlet_id'],
                    'is_active'   => $data['is_active'],
                    'image'       => $data['image'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.menu.index')
                ->with('success', 'Menu berhasil dibuat.');

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Gagal membuat Menu', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal membuat data.');
        }
    }



    public function edit(MenuItem $menu)
    {
        $categories = Category::orderBy('name')->get();
        $outlets = Outlet::orderBy('name')->get();
        $stock = StockMovement::get();
        return view('admin.menu.edit', compact('menu','categories', 'outlets','stock'));
    }

    // public function update(Request $request, MenuItem $menu)
    // {
    //     $data = $request->validate([
    //         'name'        => 'required|string|max:150',
    //         'category_id' => 'nullable|exists:categories,id',
    //         'price'       => 'required|numeric|min:0',
    //         'description' => 'nullable|string',
    //         'is_active'   => 'sometimes|boolean',
    //         'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $data['is_active'] = $request->boolean('is_active');

            
    //         if ($request->hasFile('image')) {

    //             // hapus image lama (aman Linux / Windows)
    //             if (!empty($menu->image)) {
    //                 Storage::disk('public')->delete('menu/'.$menu->image);
    //             }

    //             $file = $request->file('image');
    //             $filename = uniqid().'_'.$file->getClientOriginalName();

    //             // simpan image
    //             $file->storeAs('menu', $filename, 'public');

    //             $data['image'] = $filename;
    //         }



    //         $menu->update($data);

    //         DB::commit();

    //         return redirect()
    //             ->route('admin.menu.index')
    //             ->with('success', 'Menu berhasil diupdate.');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         Log::error('Gagal update menu', [
    //             'menu_id' => $menu->id,
    //             'message' => $e->getMessage(),
    //         ]);

    //         return back()
    //             ->with('error', 'Gagal menyimpan perubahan.')
    //             ->withInput();
    //     }
    // }

    public function update(Request $request, MenuItem $menu)
{
    $data = $request->validate([
        'name'        => 'nullable|string|max:150',
        'category_id' => 'nullable|exists:categories,id',
        'price'       => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'namestock'   => 'nullable|exists:stock_movements,id',
        'is_active'   => 'sometimes|boolean',
        'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    try {
        DB::beginTransaction();

        $data['is_active'] = $request->boolean('is_active');

        /* ================= IMAGE ================= */
        if ($request->hasFile('image')) {

            if ($menu->image) {
                Storage::disk('public')->delete('menu/'.$menu->image);
            }

            $file = $request->file('image');
            $filename = uniqid().'_'.$file->getClientOriginalName();
            $file->storeAs('menu', $filename, 'public');

            $data['image'] = $filename;
        }

        /* ================= LOGIC STOCK ================= */

        // 🔹 JIKA PILIH / GANTI STOCK
        if (!empty($data['namestock'])) {

            // hanya update kalau stock berubah
            if ($menu->stock_id != $data['namestock']) {

                $stock = StockMovement::findOrFail($data['namestock']);

                $data['name']     = $stock->namestock;
                $data['stock_id'] = $stock->id;
            }

        }
        // 🔹 JIKA STOCK DIKOSONGKAN (BALIK MANUAL)
        else {

            $data['stock_id'] = null;

            if (empty($data['name'])) {
                throw new \Exception(
                    'Nama menu wajib diisi jika tidak menggunakan stock'
                );
            }
        }

        // hapus field virtual
        unset($data['namestock']);

        /* ================= UPDATE ================= */
        $menu->update($data);

        DB::commit();

        return redirect()
            ->route('admin.menu.index')
            ->with('success', 'Menu berhasil diupdate.');

    } catch (\Throwable $e) {

        DB::rollBack();

        Log::error('Gagal update menu', [
            'menu_id' => $menu->id,
            'message' => $e->getMessage(),
        ]);

        return back()
            ->with('error', 'Gagal menyimpan perubahan.')
            ->withInput();
    }
}


    public function destroy(MenuItem $menu)
    {
        dd($menu);
        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu dihapus.');
    }

    

    private function generateMenuCode(?int $categoryId): string
    {
        if (! $categoryId) return '';

        $category = Category::findOrFail($categoryId);

        
        $prefix = $category->prefix ?? (
            $categoryId == 1 ? 'MNM' : 'MKN' // contoh sementara
        );

        $lastCode = MenuItem::where('code', 'like', $prefix . '%')
            ->orderByDesc('code')
            ->value('code');

        $next = 1;
        if ($lastCode) {
            // Ambil 2 digit terakhir dari code, mis: MNM07 -> 7
            $suffix = (int) substr($lastCode, -2);
            $next = $suffix + 1;
        }

        if ($next > 99) {
            throw new \RuntimeException("Kode {$prefix} sudah melebihi 99.");
        }

        return $prefix . str_pad($next, 2, '0', STR_PAD_LEFT); // MNM01, MNM02, ...
    }


    

}
