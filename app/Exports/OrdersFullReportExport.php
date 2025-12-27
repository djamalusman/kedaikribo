<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OrdersFullReportExport implements WithMultipleSheets
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function sheets(): array
    {
        return [
            new SheetDetailOrder($this->orders),
            new SheetSummaryTotal($this->orders),
            new SheetSummaryOutletKasir($this->orders),
            new SheetSummaryTanggal($this->orders),
        ];
    }
}
