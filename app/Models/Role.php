<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $guarded = [];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function navigationMenus(): BelongsToMany
    {
        return $this->belongsToMany(
            NavigationMenu::class,
            'role_navigation_menu',
            'role_id',
            'navigation_menu_id'
        )->withPivot('can_view')->withTimestamps();
    }
}
