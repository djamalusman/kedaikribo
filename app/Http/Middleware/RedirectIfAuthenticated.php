<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = $guards ?: [null];

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // kalau user sudah login, arahkan ke dashboard sesuai role
                $user = Auth::user();
                if ($user && $user->role) {
                    switch ($user->role->name) {
                        case 'owner':
                            return redirect()->route('owner.dashboard');
                        case 'admin':
                            return redirect()->route('admin.dashboard');
                        case 'kasir':
                            return redirect()->route('kasir.dashboard');
                    }
                }

                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
