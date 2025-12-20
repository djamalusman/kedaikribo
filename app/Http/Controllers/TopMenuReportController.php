<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuItem;

class TopMenuReportController extends Controller
{
    public function exportOwner(Request $request)
    {
        return $this->exportInternal($request, 'owner');
    }

    public function exportAdmin(Request $request)
    {
        return $this->exportInternal($request, 'admin');
    }

    protected function exportInternal(Request $request, string $role)
    {
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
            ->whereBetween('orders.order_date', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ]);

        if ($outletId) {
            $query->where('orders.outlet_id', $outletId);
        }

        $topMenus = $query
            ->groupBy('menu_items.name')
            ->orderByDesc('total_qty')
            ->get();

        $fileName = "top-menu-{$role}-{$from}_to_{$to}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($topMenus, $from, $to) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ["Menu terlaris dari {$from} sampai {$to}"]);
            fputcsv($handle, []);
            fputcsv($handle, ['Menu', 'Total Terjual']);

            foreach ($topMenus as $row) {
                fputcsv($handle, [
                    $row->name,
                    $row->total_qty,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
