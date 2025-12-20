<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Outlet;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        // Ringkasan hari ini
        $todayTransactions = Order::where('status', 'paid')
            ->whereBetween('order_date', [$todayStart, $todayEnd])
            ->count();

        $todayRevenue = Order::where('status', 'paid')
            ->whereBetween('order_date', [$todayStart, $todayEnd])
            ->sum('grand_total');

        // Stok menipis
        $lowStocks = Ingredient::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        // Aktivitas kasir (order terbaru)
        $recentOrders = Order::with(['cashier', 'outlet'])
            ->where('status', 'paid')
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get();

        // Filter menu terlaris per periode + outlet
        $from = $request->input('from_date') ?: now()->startOfMonth()->toDateString();
        $to   = $request->input('to_date') ?: now()->endOfMonth()->toDateString();
        $outletId = $request->input('outlet_id');

        $query = MenuItem::select(
                'menu_items.name',
                \DB::raw('SUM(order_items.qty) as total_qty')
            )
            ->join('order_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.order_date', [$from.' 00:00:00', $to.' 23:59:59']);

        if ($outletId) {
            $query->where('orders.outlet_id', $outletId);
        }

        $topMenus = $query
            ->groupBy('menu_items.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $chartLabels = $topMenus->pluck('name');
        $chartData   = $topMenus->pluck('total_qty');

        $outlets = Outlet::orderBy('name')->get();

        return view('dashboard.admin', compact(
            'todayTransactions',
            'todayRevenue',
            'lowStocks',
            'recentOrders',
            'from',
            'to',
            'outletId',
            'outlets',
            'chartLabels',
            'chartData'
        ));
    }
}
