<?php
// app/Models/OrderReserved.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserved extends Model
{
    protected $table = 'order_reserved';

    protected $fillable = [
        'order_id',
        'cafe_tables_id',
        'total_dp',
        'end_date',
        'start_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class, 'cafe_tables_id');
    }
}
