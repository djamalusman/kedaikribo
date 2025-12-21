<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Outlet;
use DB;
use Carbon\Carbon;
class AdminDashboardController extends Controller
{
    public function index(Request $request)
{
    /* =====================================================
       WAKTU HARI INI
    ===================================================== */
    $todayStart = now()->startOfDay();
    $todayEnd   = now()->endOfDay();

    /* =====================================================
       RINGKASAN HARI INI
    ===================================================== */
    $todayTransactions = Order::where('status', 'paid')
        ->whereBetween('order_date', [$todayStart, $todayEnd])
        ->count();

    $todayRevenue = Order::where('status', 'paid')
        ->whereBetween('order_date', [$todayStart, $todayEnd])
        ->sum('grand_total');

    /* =====================================================
       STOK MENIPIS
    ===================================================== */
    $lowStocks = Ingredient::whereColumn('stock', '<=', 'min_stock')
        ->orderBy('stock')
        ->limit(10)
        ->get();

    /* =====================================================
       ORDER TERBARU
    ===================================================== */
    $outletId =1;

      $recentOrders = Order::with([
            'outlet',
            'cashier',
            'customer',
            'items.menuItem' // 🔥 kunci utamanya
        ])
        ->paid()
        ->when($outletId, fn ($q) => $q->where('outlet_id', 1))
        ->orderBy('order_date', 'desc')
        ->paginate(20);


    /* =====================================================
       FILTER PERIODE
    ===================================================== */
    $from     = $request->input('from_date') ?? now()->startOfMonth()->toDateString();
    $to       = $request->input('to_date')   ?? now()->endOfMonth()->toDateString();
    $outletId = $request->input('outlet_id');

    /* =====================================================
       MENU TERLARIS (TOP 10 PER PERIODE)
    ===================================================== */
    $topMenus = MenuItem::select(
            'menu_items.name',
            'menu_items.category_id',
            DB::raw('SUM(order_items.qty) as total_qty')
        )
        ->join('order_items', 'order_items.menu_item_id', '=', 'menu_items.id')
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->where('orders.status', 'paid')
        ->whereBetween('orders.order_date', [$from.' 00:00:00', $to.' 23:59:59'])
        ->when($outletId, function ($q) use ($outletId) {
            $q->where('orders.outlet_id', $outletId);
        })
        ->groupBy('menu_items.name', 'menu_items.category_id')
        ->orderByDesc('total_qty')
        ->limit(10)
        ->get();

    $chartLabels = $topMenus->pluck('name');
    $chartData   = $topMenus->pluck('total_qty');
    $chartTypes  = $topMenus->pluck('category_id');

    /* =====================================================
       TAHUN AKTIF
    ===================================================== */
    $year = $request->input('year') ?? now()->year;

    /* =====================================================
       OMZET BULANAN (LINE / BAR)
    ===================================================== */
    $monthlyRaw = Order::selectRaw('MONTH(order_date) as month, SUM(grand_total) as total')
        ->where('status', 'paid')
        ->whereYear('order_date', $year)
        ->groupByRaw('MONTH(order_date)')
        ->pluck('total', 'month');

    $monthlyLabels = [
        'Jan','Feb','Mar','Apr','Mei','Jun',
        'Jul','Agu','Sep','Okt','Nov','Des'
    ];

    $monthlyRevenue = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlyRevenue[] = (int) ($monthlyRaw[$i] ?? 0);
    }

    /* =====================================================
       MENU TERLARIS PER KATEGORI PER BULAN (BAR CHART)
       → INI YANG KAMU TANYAKAN
    ===================================================== */
    $months = [
        1  => 'Jan',  2  => 'Feb',  3  => 'Mar',  4  => 'Apr',
        5  => 'Mei',  6  => 'Jun',  7  => 'Jul',  8  => 'Agu',
        9  => 'Sep',  10 => 'Okt',  11 => 'Nov',  12 => 'Des',
    ];

    $menuCategoryMonthLabels = array_values($months);

    // ambil semua kategori
    $categories = DB::table('categories')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    $menuCategoryMonthlySeries = [];

    foreach ($categories as $category) {

        $monthlyQty = [];

        foreach ($months as $monthNum => $monthName) {

            $qty = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
                ->where('orders.status', 'paid')
                ->whereYear('orders.order_date', $year)
                ->whereMonth('orders.order_date', $monthNum)
                ->where('menu_items.category_id', $category->id)
                ->sum('order_items.qty');

            $monthlyQty[] = (int) $qty;
        }

        $menuCategoryMonthlySeries[] = [
            'label' => $category->name, // nama kategori
            'data'  => $monthlyQty      // qty Jan–Des
        ];
    }

   $menuDetailMap = [];

foreach ($months as $monthNum => $monthName) {

    foreach ($categories as $category) {

        $details = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->where('orders.status', 'paid')
            ->whereYear('orders.order_date', $year)
            ->whereMonth('orders.order_date', $monthNum)
            ->where('menu_items.category_id', $category->id)
            ->select(
                'menu_items.name',
                DB::raw('SUM(order_items.qty) as qty')
            )
            ->groupBy('menu_items.name')
            ->orderByDesc('qty')
            ->limit(5) // 🔥 BATASI BIAR TOOLTIP RAPI
            ->get();

        $menuDetailMap[$monthName][$category->name] = $details;
    }
}

    /* =====================================================
       TOTAL REVENUE (PERIODE FILTER)
    ===================================================== */
    $orderQuery = Order::where('status', 'paid')
        ->whereBetween('order_date', [$from.' 00:00:00', $to.' 23:59:59']);

    if ($outletId) {
        $orderQuery->where('outlet_id', $outletId);
    }

    $totalRevenue = $orderQuery->sum('grand_total');

    /* =====================================================
       DATA OUTLET
    ===================================================== */
    $outlets = Outlet::orderBy('name')->get();

    /* =====================================================
       KIRIM KE VIEW
    ===================================================== */
    return view('dashboard.admin', compact(
        'todayTransactions',
        'todayRevenue',
        'totalRevenue',
        'lowStocks',
        'recentOrders',
        'from',
        'to',
        'outletId',
        'outlets',
        'chartLabels',
        'chartData',
        'chartTypes',
        'year',
        'monthlyLabels',
        'monthlyRevenue',
        'menuCategoryMonthLabels',
        'menuCategoryMonthlySeries',
        'menuDetailMap'
    ));
}

}
