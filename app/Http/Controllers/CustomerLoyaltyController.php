<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerLoyaltyController extends Controller
{
    /**
     * Halaman history loyalty per customer untuk ADMIN
     *
     * GET /admin/customers/{customer}/loyalty
     * route name: admin.customers.loyalty
     */
    public function show(Request $request, Customer $customer)
    {
        // Filter tanggal (opsional)
        $from = $request->input('from_date');
        $to   = $request->input('to_date');

        $pointsQuery = LoyaltyPoint::where('customer_id', $customer->id);

        if ($from && $to) {
            $pointsQuery->whereBetween('created_at', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ]);
        }

        $history = $pointsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Total point (semua periode)
        $totalEarned = LoyaltyPoint::where('customer_id', $customer->id)
            ->where('type', 'earn')       // earn = penambahan poin
            ->sum('points');

        $totalRedeemed = LoyaltyPoint::where('customer_id', $customer->id)
            ->where('type', 'redeem')     // redeem = penukaran poin
            ->sum('points');

        $currentBalance = $totalEarned - $totalRedeemed;

        // Riwayat order terakhir (untuk konteks)
        $recentOrders = Order::with('outlet')
            ->paid()
            ->where('customer_id', $customer->id)
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get();

        return view('admin.customers.loyalty', compact(
            'customer',
            'history',
            'totalEarned',
            'totalRedeemed',
            'currentBalance',
            'recentOrders',
            'from',
            'to'
        ));
    }

    /**
     * Endpoint sederhana untuk kasir cek saldo poin customer
     * sesuai route kamu: kasir.customers.loyalty-balance
     *
     * GET /kasir/customers/{customer}/loyalty-balance
     */
    public function balance(Customer $customer)
    {
        $totalEarned = LoyaltyPoint::where('customer_id', $customer->id)
            ->where('type', 'earn')
            ->sum('points');

        $totalRedeemed = LoyaltyPoint::where('customer_id', $customer->id)
            ->where('type', 'redeem')
            ->sum('points');

        $currentBalance = $totalEarned - $totalRedeemed;

        return response()->json([
            'customer_id' => $customer->id,
            'name'        => $customer->name,
            'balance'     => $currentBalance,
        ]);
    }
}
