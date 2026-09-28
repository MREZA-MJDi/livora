<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * Browser requests use the custom 403/404/500-style views under
         * resources/views/errors. JSON/API clients must never receive an
         * HTML error page; keep the contract small and predictable.
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $status = match (true) {
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                $e instanceof ModelNotFoundException => 404,
                default => 500,
            };

            $message = match ($status) {
                401 => 'برای انجام این درخواست باید وارد حساب کاربری شوید.',
                403 => 'شما اجازه انجام این عملیات را ندارید.',
                404 => 'منبع موردنظر پیدا نشد.',
                419 => 'درخواست منقضی شده است. دوباره تلاش کنید.',
                429 => 'تعداد درخواست‌ها بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.',
                503 => 'سرویس موقتاً در دسترس نیست. کمی بعد دوباره تلاش کنید.',
                default => 'خطایی در پردازش درخواست رخ داد. دوباره تلاش کنید.',
            };

            return response()->json([
                'success' => false,
                'status' => $status,
                'message' => $message,
            ], $status);
        });
    })
    ->create();
