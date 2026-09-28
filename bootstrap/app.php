<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'customer' => CustomerMiddleware::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Authentication Redirects
        |--------------------------------------------------------------------------
        |
        | Keep authentication redirects explicit and role-aware.
        | Guests always go to login. Authenticated users who hit a guest
        | route are sent to the correct area for their role.
        |
        */

        $middleware->redirectGuestsTo(
            fn (Request $request) => route('login')
        );

        $middleware->redirectUsersTo(
            function (Request $request) {
                $user = $request->user();

                if ($user?->isAdmin()) {
                    return route('admin.dashboard');
                }

                if ($user?->isCustomer()) {
                    return route('account.index');
                }

                return route('home');
            }
        );
    })
    ->create();
