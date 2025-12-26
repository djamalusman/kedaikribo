<?php

// app/Models/StockMovement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
     protected $guarded = [];

    protected $casts = [
        'qty' => 'float',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Terapkan pergerakan stok ke tabel ingredients.
     * IN  => tambah stok
     * OUT => kurangi stok
     */
    public function applyToIngredient(): void
    {
       

        if ($this->movement_type === 'in') {
            $ingredient->increment('stock', $this->qty);
        } elseif ($this->movement_type === 'out') {
            $ingredient->decrement('stock', $this->qty);
        }
    }

    /**
     * Membalik efek movement dari stok (dipakai saat edit/delete).
     */
    public function revertFromIngredient(): void
    {
        

        if ($this->movement_type === 'in') {
            $ingredient->decrement('stock', $this->qty);
        } elseif ($this->movement_type === 'out') {
            $ingredient->increment('stock', $this->qty);
        }
    }
}
