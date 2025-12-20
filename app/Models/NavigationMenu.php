<?php

// app/Models/NavigationMenu.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Role;

class NavigationMenu extends Model
{
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationMenu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavigationMenu::class, 'parent_id')->orderBy('sort_order');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_navigation_menu',
            'navigation_menu_id',
            'role_id'
        )->withPivot('can_view')->withTimestamps();
    }

    // Ambil root menu untuk role tertentu
    public static function getSidebarMenuForRole(Role $role)
    {
        return static::with('children')
            ->whereNull('parent_id')
            ->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.id', $role->id)
                  ->where('is_active',1)
                  ->where('role_navigation_menu.can_view', true);
            })
            ->orderBy('sort_order')
            ->get();
    }
}
