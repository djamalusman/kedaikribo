<?php

// app/Models/Customer.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $guarded = [];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }
    
    public function loyaltyBalance(): int
    {
        $earned = (int) $this->loyaltyPoints()
            ->where('type', 'earn')
            ->sum('points');

        $redeemed = (int) $this->loyaltyPoints()
            ->where('type', 'redeem')
            ->sum('points');

        return $earned - $redeemed;
    }
}
