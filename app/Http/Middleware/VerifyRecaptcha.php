<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        $siteKey = config('services.recaptcha.site_key');
        $secretKey = config('services.recaptcha.secret_key');

        // Keep local development and automated tests usable without real Google keys.
        // Production always fails closed when the integration is not configured.
        if (! filled($siteKey) || ! filled($secretKey)) {
            if (app()->environment(['local', 'testing'])) {
                return $next($request);
            }

            Log::critical('reCAPTCHA is not configured; a protected request was blocked.', [
                'route' => $request->route()?->getName(),
            ]);

            return $this->reject(
                $request,
                'سامانه ضدربات هنوز پیکربندی نشده است. لطفاً با پشتیبانی تماس بگیرید.',
                503
            );
        }

        $token = $request->input('g-recaptcha-response');

        if (! is_string($token) || trim($token) === '' || strlen($token) > 4096) {
            return $this->reject(
                $request,
                'لطفاً تأیید کنید که ربات نیستید و دوباره تلاش کنید.'
            );
        }

        try {
            $verification = Http::asForm()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                ]);
        } catch (Throwable $exception) {
            Log::warning('Google reCAPTCHA verification request failed.', [
                'exception' => $exception::class,
            ]);

            return $this->reject(
                $request,
                'ارتباط با سامانه ضدربات برقرار نشد. لطفاً دوباره تلاش کنید.'
            );
        }

        if (! $verification->successful() || $verification->json('success') !== true) {
            return $this->reject(
                $request,
                'تأیید ضدربات ناموفق بود. لطفاً کپچا را دوباره تکمیل کنید.'
            );
        }

        $hostname = strtolower((string) $verification->json('hostname', ''));
        $allowedHosts = config('services.recaptcha.allowed_hosts', []);

        if (! is_array($allowedHosts) || $allowedHosts === []) {
            $allowedHosts = [$request->getHost()];
        }

        $allowedHosts = array_map(
            static fn ($host): string => strtolower(trim((string) $host)),
            $allowedHosts
        );

        if ($hostname === '' || ! in_array($hostname, $allowedHosts, true)) {
            Log::notice('Google reCAPTCHA returned an unexpected hostname.', [
                'route' => $request->route()?->getName(),
                'hostname' => $hostname,
            ]);

            return $this->reject(
                $request,
                'تأیید ضدربات معتبر نبود. صفحه را تازه‌سازی و دوباره تلاش کنید.'
            );
        }

        return $next($request);
    }

    protected function reject(
        Request $request,
        string $message,
        int $status = 422
    ): Response {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'errors' => ['captcha' => [$message]],
            ], $status);
        }

        return redirect()
            ->back()
            ->withInput($request->except('g-recaptcha-response'))
            ->withErrors(['captcha' => $message]);
    }
}
