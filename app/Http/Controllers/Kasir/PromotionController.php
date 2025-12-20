<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Support\Facades\Auth;

class PromotionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $promotions = Promotion::with('menuItems')
            ->where('is_active', 1)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->when($user->outlet_id, function ($q) use ($user) {
                $q->where(function ($qq) use ($user) {
                    $qq->whereNull('outlet_id')
                       ->orWhere('outlet_id', $user->outlet_id);
                });
            })
            ->orderBy('name')
            ->get();

        return view('kasir.promotions.index', compact('promotions'));
    }
}
