<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CafeTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class KasirDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $start = now()->startOfDay();
        $end   = now()->endOfDay();

        $orders = Order::with(['customer', 'table','payments'])
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


        return view('dashboard.kasir', [
            'orders'          => $orders,
            'summary'         => $summary,
            'paymentByMethod' => $paymentByMethod,
            'start'           => $start,
            'end'             => $end,
        ]);

        
    }

    
   public function items(Order $order)
    {
        // Pastikan hanya order paid
        // if ($order->status !== 'zonk') {
        //     return response()->json([
        //         'message' => 'Order belum paid'
        //     ], 400);
        // }

        $items = DB::table('menu_items')
            ->join('order_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('promotions','promotions.id','=','orders.promotion_id')
            ->select(
                'menu_items.name',
                DB::raw('SUM(order_items.qty) as qty'),
                DB::raw('SUM(order_items.qty * order_items.price) as subtotal')
            )
            ->where('orders.id', $order->id)
            ->where('orders.outlet_id', $order->outlet_id)
            ->groupBy('menu_items.name')
            ->get();

        // ambil promo sekali saja
        
        $promotype    = $order->promotion->type ?? null;
        $totalBeforePromo = $items->sum('subtotal');
        $promoPercent = (float) str_replace(',', '.', $order->promotion->value);

        $grandTotal;
        if ($promotype =="percent") {
            $discount = ($promoPercent / 100) * $totalBeforePromo;
            $grandTotal = max(0, $totalBeforePromo - $discount);
        }
        else if($promotype =="nominal")
        {
            $grandTotal = max(0, $totalBeforePromo - $promoPercent);
        }
        
        $totalreal = $items->sum('subtotal');
        

        return response()->json([
            'order_code'        => $order->order_code,
            'items'             => $items,
            'total_before_promo'=> $totalBeforePromo,
            'promotype'          => $promotype,
            'grand_total'       => $grandTotal,
            'promoPercent'       => $promoPercent,
            'totalreal'       => $totalreal
        ]);
    }
}
