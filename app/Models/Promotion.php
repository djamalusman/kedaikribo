<?php

// app/Models/Promotion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promotion extends Model
{
    protected $guarded = [];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(
            MenuItem::class,
            'promotion_menu_items',
            'promotion_id',
            'menu_item_id'
        )->withTimestamps();
    }
    public function scopeActive($query)
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today);
    }

    /**
     * Hitung diskon untuk suatu subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($subtotal < (float)$this->min_amount) {
            return 0;
        }

        if ($this->type === 'percent') {
            return round($subtotal * ((float)$this->value / 100), 2);
        }

        // nominal
        return min((float)$this->value, $subtotal);
    }
}
