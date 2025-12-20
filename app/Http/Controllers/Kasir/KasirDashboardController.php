<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CafeTable;
use Illuminate\Support\Facades\Auth;

class KasirDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        // Order yang dibayar oleh kasir ini hari ini
        $ordersQuery = Order::where('cashier_id', $user->id)
            ->whereBetween('order_date', [$todayStart, $todayEnd])
            ->where('status', 'paid');

        $totalTransactions = (clone $ordersQuery)->count();
        $totalRevenue      = (clone $ordersQuery)->sum('grand_total');

        // Pembagian per metode pembayaran (cash, qris, transfer)
        $paymentByMethod = (clone $ordersQuery)
        ->join('payments as p', 'p.order_id', '=', 'orders.id')
        ->selectRaw('p.payment_method, COUNT(DISTINCT orders.id) as total_tx, SUM(p.amount) as total_amount')
        ->groupBy('p.payment_method')
        ->get();

        // 5 menu terlaris hari ini (berdasarkan order_items)
        $topMenus = \DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->where('orders.cashier_id', $user->id)
            ->whereBetween('orders.order_date', [$todayStart, $todayEnd])
            ->where('orders.status', 'paid')
            ->selectRaw('menu_items.name, SUM(order_items.qty) as qty_sold')
            ->groupBy('menu_items.name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get();

        // Status meja (di outlet kasir ini, kalau user punya outlet_id)
        $tablesSummary = null;
        if (!empty($user->outlet_id)) {
            $tablesSummary = CafeTable::where('outlet_id', $user->outlet_id)
                ->selectRaw("
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_count,
                    SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied_count,
                    SUM(CASE WHEN status = 'reserved' THEN 1 ELSE 0 END) as reserved_count
                ")
                ->first();
        }

        return view('dashboard.kasir', compact(
            'totalTransactions',
            'totalRevenue',
            'paymentByMethod',
            'topMenus',
            'tablesSummary'
        ));
    }
}
