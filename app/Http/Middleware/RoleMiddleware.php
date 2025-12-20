<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Contoh pemakaian:
     * ->middleware('role:owner')
     * ->middleware('role:admin')
     * ->middleware('role:kasir')
     * atau gabungan: ->middleware('role:owner,admin')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRoleName = $user->role->name ?? null; // pastikan relasi role() ada di model User

        if (! $userRoleName || ! in_array($userRoleName, $roles)) {
            // Tidak punya akses
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
