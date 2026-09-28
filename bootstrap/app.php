<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (
        Middleware $middleware
    ) {

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

    ->withExceptions(function (
        Exceptions $exceptions
    ) {

        /*
        |--------------------------------------------------------------------------
        | JSON / AJAX
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => app()->isProduction()
                    ? 'در پردازش درخواست مشکلی پیش آمد. لطفاً دوباره تلاش کنید.'
                    : $e->getMessage(),
            ], 500);
        });


        /*
        |--------------------------------------------------------------------------
        | HTTP Error Pages
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (
            HttpExceptionInterface $e,
            Request $request
        ) {
            if ($request->expectsJson()) {
                return null;
            }

            $view = match ($e->getStatusCode()) {
                403 => 'errors.403',
                404 => 'errors.404',
                419 => 'errors.419',
                422 => 'errors.422',
                default => null,
            };

            if ($view === null) {
                return null;
            }

            return response()->view(
                $view,
                [],
                $e->getStatusCode()
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Production Fallback
        |--------------------------------------------------------------------------
        */

        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            if (
                ! app()->isProduction()
                || $request->expectsJson()
            ) {
                return null;
            }

            report($e);

            return response()->view(
                'errors.500',
                [],
                500
            );
        });
    })
    ->create();
