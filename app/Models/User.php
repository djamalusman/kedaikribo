<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang bisa diisi mass-assignment
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',        // tambahkan
        'outlet_id', 
    ];

    /**
     * Kolom yang disembunyikan
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Kolom dengan tipe data otomatis
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * RELASI
     * user → role
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * HELPER ROLE
     */
    public function isOwner(): bool
    {
        return $this->role?->name === 'owner';
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    public function isKasir(): bool
    {
        return $this->role?->name === 'kasir';
    }

    /**
     * OPTIONAL: untuk relasi order (kasir)
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}
