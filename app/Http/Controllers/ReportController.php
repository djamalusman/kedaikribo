<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Outlet;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Laporan penjualan untuk OWNER
     */
    public function salesOwner(Request $request)
    {
        return $this->salesInternal($request, 'owner');
    }

    /**
     * Export Excel laporan OWNER
     */
    public function salesOwnerExport(Request $request)
    {
        return $this->salesExportInternal($request, 'owner');
    }

    /**
     * Laporan penjualan untuk ADMIN
     */
    public function salesAdmin(Request $request)
    {
        return $this->salesInternal($request, 'admin');
    }

    /**
     * Export Excel laporan ADMIN
     */
    public function salesAdminExport(Request $request)
    {
        return $this->salesExportInternal($request, 'admin');
    }

    /**
     * Logic utama laporan (dipakai owner & admin)
     */
    protected function salesInternal(Request $request, string $role)
    {
        // Filter tanggal
        $from = $request->input('from_date');
        $to   = $request->input('to_date');

        if (!$from || !$to) {
            // default: bulan ini
            $from = now()->startOfMonth()->toDateString();
            $to   = now()->endOfMonth()->toDateString();
        }

        $outletId = $request->input('outlet_id');

        $query = Order::with(['outlet', 'cashier', 'customer'])
            ->paid()
            ->whereBetween('order_date', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ]);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(20);

        $totalTransactions = (clone $query)->count();
        $totalRevenue      = (clone $query)->sum('grand_total');

        $outlets = Outlet::orderBy('name')->get();

        return view('reports.sales', [
            'orders'           => $orders,
            'totalTransactions'=> $totalTransactions,
            'totalRevenue'     => $totalRevenue,
            'outlets'          => $outlets,
            'from'             => $from,
            'to'               => $to,
            'outletId'         => $outletId,
            'role'             => $role,
        ]);
    }

    /**
     * Logic utama export Excel
     */
    protected function salesExportInternal(Request $request, string $role)
    {
        $from = $request->input('from_date');
        $to   = $request->input('to_date');

        if (!$from || !$to) {
            $from = now()->startOfMonth()->toDateString();
            $to   = now()->endOfMonth()->toDateString();
        }

        $outletId = $request->input('outlet_id');

        $query = Order::with(['outlet', 'cashier', 'customer'])
            ->paid()
            ->whereBetween('order_date', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ]);

        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $orders = $query->orderBy('order_date', 'desc')->get();

        $fileName = 'sales-report-' . $role . '-' . $from . '_to_' . $to . '.xlsx';

        return Excel::download(new SalesReportExport($orders, $from, $to), $fileName);
    }
}