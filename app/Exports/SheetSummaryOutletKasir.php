<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // 🔥 WAJIB
use Maatwebsite\Excel\Concerns\WithTitle;
class SheetSummaryOutletKasir implements FromCollection, WithHeadings,WithTitle
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function headings(): array
    {
        return ['Outlet', 'Kasir', 'Total Transaksi', 'Total Omzet'];
    }

    public function title(): string
    {
        return 'Per Outlet & Kasir';
    }

    public function collection()
    {
        return $this->orders
            ->groupBy(fn ($o) =>
                ($o->outlet->name ?? '-') . '|' . ($o->cashier->name ?? '-')
            )
            ->map(function ($rows) {
                return [
                    $rows->first()->outlet->name ?? '-',
                    $rows->first()->cashier->name ?? '-',
                    $rows->count(),
                    $rows->sum('grand_total'),
                ];
            })
            ->values();
    }
}
