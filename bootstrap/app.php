<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\ApplyRobotsHeaders;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'customer' => CustomerMiddleware::class,
        ]);

        $middleware->appendToGroup('web', [ApplyRobotsHeaders::class]);

        // External payment providers cannot send browser CSRF tokens. The callback
        // remains safe only because PaymentService verifies the transaction
        // directly with the provider before updating local payment state.
        $middleware->validateCsrfTokens(except: [
            'checkout/payment/callback/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            if (
                ! $request->expectsJson()
                || $e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500)
            ) {
                // Keep Laravel's native 401/403/404/419/422 responses and payloads.
                return null;
            }

            report($e);

            $status = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : 500;

            $message = config('app.debug') && ! app()->isProduction()
                ? $e->getMessage()
                : 'در پردازش درخواست مشکلی پیش آمد. لطفاً دوباره تلاش کنید.';

            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        });

        $exceptions->render(function (
            HttpExceptionInterface $e,
            Request $request
        ) {
            // Give expired dashboard sessions a distinct response for both
            // browser navigation and fetch/XHR requests.
            if (
                $e->getStatusCode() === 419
                && $request->is(config('admin.path'), config('admin.path').'/*')
            ) {
                if ($request->expectsJson()) {
                    $referer = $request->headers->get('referer');
                    $refererHost = is_string($referer)
                        ? parse_url($referer, PHP_URL_HOST)
                        : null;
                    $refererPath = is_string($referer)
                        ? parse_url($referer, PHP_URL_PATH)
                        : null;

                    if (
                        $refererHost
                        && hash_equals(strtolower($request->getHost()), strtolower($refererHost))
                        && is_string($refererPath)
                        && preg_match('#^/'.preg_quote(config('admin.path'), '#').'(?:/|$)#', $refererPath) === 1
                    ) {
                        $request->session()->put('url.intended', $referer);
                    } else {
                        $request->session()->put('url.intended', route('admin.dashboard'));
                    }

                    return response()->json([
                        'success' => false,
                        'code' => 'admin_session_expired',
                        'message' => 'نشست مدیریت منقضی شده است. برای ادامه دوباره وارد شوید.',
                        'login_url' => route('login'),
                    ], 419);
                }

                return redirect()
                    ->guest(route('login'))
                    ->with('warning', 'نشست مدیریت منقضی شده است؛ دوباره وارد شوید تا به صفحه قبلی بازگردید.');
            }

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

            return response()->view($view, [], $e->getStatusCode());
        });

        $exceptions->render(function (
            Throwable $e,
            Request $request
        ) {
            if (! app()->isProduction() || $request->expectsJson()) {
                return null;
            }

            report($e);

            return response()->view('errors.500', [], 500);
        });
    })
    ->create();
