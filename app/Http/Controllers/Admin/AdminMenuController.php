<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Outlet;
use App\Models\Category; // kalau ada kategori
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
         return view('admin.menu.create', compact('categories', 'outlets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'code'        => 'nullable|string',
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

            // 📤 Upload image → public_html/storage/menu
            if ($request->hasFile('image')) {

                $file = $request->file('image');
                $filename = uniqid().'_'.$file->getClientOriginalName();

                $publicDir = public_path('storage/menu');
                if (!is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }

                // SIMPAN LANGSUNG KE PUBLIC
                $file->move($publicDir, $filename);

                $data['image'] = $filename;
            }

            MenuItem::create($data);

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
        return view('admin.menu.edit', compact('menu','categories', 'outlets'));
    }

    public function update(Request $request, MenuItem $menu)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active'   => 'sometimes|boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $data['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('image')) {

                // 🧹 Hapus image lama di public_html
                if (!empty($menu->image)) {
                    $old = public_path('storage/menu/'.$menu->image);
                    if (file_exists($old)) {
                        @unlink($old);
                    }
                }

                // 📤 Upload image baru
                $file = $request->file('image');
                $filename = uniqid().'_'.$file->getClientOriginalName();

                $publicDir = public_path('storage/menu');
                if (!is_dir($publicDir)) {
                    mkdir($publicDir, 0755, true);
                }

                $file->move($publicDir, $filename);

                $data['image'] = $filename;
            }

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
