<?php

// app/Models/CafeTable.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CafeTable extends Model
{
    protected $guarded = [];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    // Scope opsional: hanya meja yang tidak inactive
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'inactive');
    }
}
