<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if ($user?->isAdmin()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403);
        }

        if ($user?->isCustomer()) {
            return redirect()
                ->route('account.index')
                ->with(
                    'warning',
                    'این بخش فقط برای حساب‌های مدیریتی در دسترس است.'
                );
        }

        return redirect()
            ->route('home')
            ->with(
                'warning',
                'حساب فعلی اجازه دسترسی به پنل مدیریت را ندارد.'
            );
    }
}
