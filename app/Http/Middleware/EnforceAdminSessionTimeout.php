<?php

namespace App\\Http\\Middleware;

use Closure;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Auth;
use Symfony\\Component\\HttpFoundation\\Response;

final class EnforceAdminSessionTimeout
{
    private const LAST_ACTIVITY_KEY = 'admin_last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isAdmin()) {
            return $next($request);
        }

        $timeoutMinutes = max(5, (int) config('admin.session_timeout_minutes', 30));
        $timeoutSeconds = $timeoutMinutes * 60;
        $now = now()->timestamp;
        $lastActivity = (int) $request->session()->get(self::LAST_ACTIVITY_KEY, 0);

        if ($lastActivity > 0 && ($now - $lastActivity) >= $timeoutSeconds) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'code' => 'admin_session_expired',
                    'message' => 'نشست مدیریت به دلیل عدم فعالیت منقضی شده است. برای ادامه دوباره وارد شوید.',
                    'login_url' => route('login'),
                ], 419);
            }

            return redirect()
                ->guest(route('login'))
                ->with('warning', 'برای حفظ امنیت، نشست مدیریت پس از ۳۰ دقیقه عدم فعالیت پایان یافت. دوباره وارد شوید.');
        }

        $request->session()->put(self::LAST_ACTIVITY_KEY, $now);

        return $next($request);
    }
}
