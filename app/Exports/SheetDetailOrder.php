<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetDetailOrder implements FromCollection, WithHeadings,WithTitle
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

     public function title(): string
    {
        return 'Detail Order';
    }

    public function headings(): array
    {
        return [
            'Tanggal','Kode','Outlet','Kasir','Customer','Meja',
            'Menu','Qty','Harga','Total Item',
            'Subtotal','Diskon','DP','Total Bayar'
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->orders as $order) {

            $subtotal = $order->subtotal;
            $diskon   = $order->discount_total ?? 0;
            $dp       = $order->reserved?->total_dp ?? 0;
            $total    = $order->reserved
                ? max(0, $order->grand_total - $dp)
                : $order->grand_total;

            foreach ($order->items as $item) {
                $rows->push([
                    $order->order_date->format('Y-m-d H:i'),
                    $order->order_code,
                    $order->outlet->name ?? '-',
                    $order->cashier->name ?? '-',
                    $order->customer->name ?? '-',
                    $order->table->name ?? '-',
                    $item->menuItem->name ?? '-',
                    $item->qty,
                    $item->price,
                    $item->total,
                    $subtotal,
                    $diskon,
                    $dp,
                    $total,
                ]);
            }
        }

        return $rows;
    }
}
