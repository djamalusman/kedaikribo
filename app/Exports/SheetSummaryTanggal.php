<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; // 🔥 WAJIB
use Maatwebsite\Excel\Concerns\WithTitle;
class SheetSummaryTanggal implements FromCollection, WithHeadings,WithTitle
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Menu',
            'Total Qty',
            'Total Omzet',
        ];
    }

    public function title(): string
    {
        return 'Tanggal Transaksi';
    }


    public function collection()
    {
        $rows = collect();

        /**
         * Struktur data:
         * orders
         * └── items (order_items)
         *     └── menuItem (menu_items)
         */

        // Group berdasarkan TANGGAL ORDER
        $this->orders
            ->groupBy(fn ($order) => $order->order_date->format('Y-m-d'))
            ->each(function ($ordersByDate, $date) use ($rows) {

                // Kumpulkan semua item di tanggal tersebut
                $items = $ordersByDate->flatMap->items;

                // Group by MENU
                $items
                    ->groupBy(fn ($item) => $item->menuItem->name ?? 'Unknown')
                    ->each(function ($menuItems, $menuName) use ($rows, $date) {

                        $totalQty = $menuItems->sum('qty');

                        // omzet = sum(total item)
                        $totalOmzet = $menuItems->sum('total');

                        $rows->push([
                            $date,
                            $menuName,
                            $totalQty,
                            $totalOmzet,
                        ]);
                    });
            });

        return $rows;
    }

}
