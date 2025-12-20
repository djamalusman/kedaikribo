<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasirReportController extends Controller
{
    public function today(Request $request)
    {
        $user  = Auth::user();
        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        $orders = Order::with(['customer', 'table'])
            ->where('cashier_id', $user->id)
            ->whereBetween('order_date', [$start, $end])
            ->orderBy('order_date', 'desc')
            ->get();

        $summary = [
            'total_tx'      => $orders->count(),
            'total_open'    => $orders->where('status', 'open')->count(),
            'total_paid'    => $orders->where('status', 'paid')->count(),
            'total_cancel'  => $orders->where('status', 'cancelled')->count(),
            'total_amount'  => $orders->where('status', 'paid')->sum('grand_total'),
        ];

        $paymentByMethod = Order::query()
    ->join('payments as p', 'p.order_id', '=', 'orders.id')
    ->where('orders.cashier_id', $user->id)
    ->whereBetween('orders.order_date', [$start, $end])
    ->where('orders.status', 'paid')
    ->selectRaw('p.payment_method, COUNT(DISTINCT orders.id) as total_tx, SUM(p.amount) as total_amount')
    ->groupBy('p.payment_method')
    ->get();


        return view('kasir.reports.today', [
            'orders'          => $orders,
            'summary'         => $summary,
            'paymentByMethod' => $paymentByMethod,
            'start'           => $start,
            'end'             => $end,
        ]);
    }
}
