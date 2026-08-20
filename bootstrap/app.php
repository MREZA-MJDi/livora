<?php

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
        //
    })

    ->withExceptions(function (
        Exceptions $exceptions
    ) {

        /*
        |--------------------------------------------------------------------------
        | JSON / AJAX Errors
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

            return match ($e->getStatusCode()) {

                403 => response()->view(
                    'errors.403',
                    [],
                    403
                ),

                404 => response()->view(
                    'errors.404',
                    [],
                    404
                ),

                419 => response()->view(
                    'errors.419',
                    [],
                    419
                ),

                422 => response()->view(
                    'errors.422',
                    [],
                    422
                ),

                default => null,
            };
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
                [
                    'exception' => null,
                ],
                500
            );
        });

    })
    ->create();
