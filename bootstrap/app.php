<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\LogContextMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->alias([
            'auth'  => Authenticate::class,
            'role'  => RoleMiddleware::class,
            'guest' => RedirectIfAuthenticated::class,
        ]);

        // Middleware global untuk menambahkan context log
        $middleware->append(LogContextMiddleware::class);
        // Jika Anda hanya ingin untuk web routes:
        // $middleware->web(append: [LogContextMiddleware::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
