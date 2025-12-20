<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\MenuItem;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Summary harian/mingguan/bulanan (seperti sebelumnya)
        $startDay   = now()->startOfDay();
        $endDay     = now()->endOfDay();

        $startWeek  = now()->startOfWeek();
        $endWeek    = now()->endOfWeek();

        $startMonth = now()->startOfMonth();
        $endMonth   = now()->endOfMonth();

        // Periode chart
        $chartFrom = $request->input('from_date') ?: $startMonth->toDateString();
        $chartTo   = $request->input('to_date') ?: $endMonth->toDateString();
        $outletId  = $request->input('outlet_id');

        $query = MenuItem::select(
                'menu_items.name',
                \DB::raw('SUM(order_items.qty) as total_qty')
            )
            ->join('order_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.order_date', [
                $chartFrom . ' 00:00:00',
                $chartTo   . ' 23:59:59',
            ]);

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

        return view('dashboard.owner', compact(
            'summary',
            'chartFrom',
            'chartTo',
            'outletId',
            'outlets',
            'chartLabels',
            'chartData'
        ));
    }
}
