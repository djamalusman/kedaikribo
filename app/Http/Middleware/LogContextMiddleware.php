<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\MenuItem;

class LogContextMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Ambil menu dari berbagai kemungkinan:
        // 1) Route model binding: /admin/menu/{menu}
        $menu = $request->route('menu');

        // 2) Jika route param namanya berbeda (mis. menuItem)
        if (! $menu) {
            $menu = $request->route('menuItem');
        }

        // 3) Jika menu_id dikirim dari form
        if (! $menu && $request->filled('menu_item_id')) {
            // menu_item_id bisa array (promo), ambil yang pertama untuk konteks
            $id = is_array($request->menu_item_id) ? ($request->menu_item_id[0] ?? null) : $request->menu_item_id;
            if ($id) {
                $menu = MenuItem::find($id);
            }
        }

        // Susun context menu
        $menuContext = null;
        if ($menu instanceof MenuItem) {
            $menuContext = [
                'menu_id'   => $menu->id,
                'menu_name' => $menu->name,
                'menu_code' => $menu->code ?? null,
            ];
        } elseif (is_object($menu) && isset($menu->id)) {
            // kalau route binding mengembalikan model lain yang punya id/name
            $menuContext = [
                'menu_id'   => $menu->id,
                'menu_name' => $menu->name ?? null,
            ];
        }

        // Inject context global
        Log::withContext(array_filter([
            'app'      => config('app.name'),
            'env'      => config('app.env'),
            'user_id'  => optional(auth()->user())->id,
            'url'      => $request->fullUrl(),
            'method'   => $request->method(),
            'ip'       => $request->ip(),
            'route'    => optional($request->route())->getName(),
            'menu'     => $menuContext,
        ]));

        return $next($request);
    }
}
