<?php

namespace App\Http\Controllers\Kasir;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $q = $request->input('q');

        $customers = Customer::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(20);

        return view('kasir.customers.index', compact('customers', 'q'));
    }

    public function show(Customer $customer)
    {
        $points = LoyaltyPoint::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        $totalPoints = $points->sum('points'); // karena redeem pakai minus

        $orders = $customer->orders()
            ->orderByDesc('order_date')
            ->limit(10)
            ->get();

        return view('kasir.customers.show', compact(
            'customer',
            'points',
            'totalPoints',
            'orders'
        ));
    }

   public function points(Customer $customer)
    {
        $total = \App\Models\LoyaltyPoint::where('customer_id', $customer->id)->sum('points');

        return response()->json([
            'customer_id'  => $customer->id,
            'total_points' => $total,
        ]);
    }
}
