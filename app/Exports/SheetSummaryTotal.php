<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // 🔥 WAJIB
use Maatwebsite\Excel\Concerns\WithTitle;
class SheetSummaryTotal implements FromCollection, WithHeadings,WithTitle
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

     public function title(): string
    {
        return 'Summary Total';
    }

    public function collection()
    {
        return collect([
            ['Total Transaksi', $this->orders->count()],
            ['Total Qty', $this->orders->flatMap->items->sum('qty')],
            ['Total Subtotal', $this->orders->sum('subtotal')],
            ['Total Diskon', $this->orders->sum('discount_total')],
            ['Total DP', $this->orders->sum(fn($o) => $o->reserved?->total_dp ?? 0)],
            ['Total Bayar', $this->orders->sum('grand_total')],
        ]);
    }
}
