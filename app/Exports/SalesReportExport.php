<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $orders;
    protected string $from;
    protected string $to;

    public function __construct(Collection $orders, string $from, string $to)
    {
        $this->orders = $orders;
        $this->from   = $from;
        $this->to     = $to;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kode Order',
            'Outlet',
            'Kasir',
            'Pelanggan',
            'Tipe Order',
            'Status',
            'Subtotal',
            'Diskon',
            'Grand Total',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_date,
            $order->order_code,
            $order->outlet->name ?? '-',
            $order->cashier->name ?? '-',
            $order->customer->name ?? '-',
            $order->order_type,
            $order->status,
            $order->subtotal,
            $order->discount_total,
            $order->grand_total,
        ];
    }
}