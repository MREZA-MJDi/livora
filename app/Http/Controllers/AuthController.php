<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(
        LoginRequest $request
    ): RedirectResponse {
        $credentials = $request->validated();

        $email = Str::lower($credentials['email']);

        $throttleKey = Str::transliterate(
            Str::lower($email) .
            '|' .
            $request->ip()
        );

        if (RateLimiter::tooManyAttempts(
            $throttleKey,
            5
        )) {
            $seconds = RateLimiter::availableIn(
                $throttleKey
            );

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' =>
                        "تعداد تلاش‌ها زیاد است. {$seconds} ثانیه دیگر دوباره تلاش کنید.",
                ]);
        }

        if (! Auth::attempt([
            'email' => $email,
            'password' => $credentials['password'],
        ], $credentials['remember'] ?? false)) {

            RateLimiter::hit(
                $throttleKey,
                60
            );

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' =>
                        'ایمیل یا رمز عبور صحیح نیست.',
                ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $this->mergeGuestCartIntoUserCart($request);

        return $this->redirectAfterAuthentication($request);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(
        RegisterRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $user = DB::transaction(function () use (
            $validated
        ) {
            return User::create([
                'name' => trim(
                    $validated['first_name'] .
                    ' ' .
                    $validated['last_name']
                ),

                'email' => Str::lower(
                    $validated['email']
                ),

                'password' => Hash::make(
                    $validated['password']
                ),

                /*
                |--------------------------------------------------------------------------
                | New users are always customers
                |--------------------------------------------------------------------------
                */
                'role' => 'customer',
            ]);
        });

        Auth::login($user);

        $request->session()->regenerate();

        $this->mergeGuestCartIntoUserCart($request);

        return $this->redirectAfterAuthentication(
            $request,
            'حساب کاربری با موفقیت ایجاد شد.'
        );
    }

    protected function redirectAfterAuthentication(
        Request $request,
        string $successMessage = 'با موفقیت وارد شدید.'
    ): RedirectResponse {
        $user = $request->user();

        if (! $user) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'احراز هویت انجام نشد. دوباره تلاش کنید.',
                ]);
        }

        $fallbackRoute = match (true) {
            $user->isAdmin() => 'admin.dashboard',
            $user->isCustomer() => 'account.index',
            default => 'home',
        };

        $intended = $request->session()->pull('url.intended');

        if (
            is_string($intended) &&
            $this->isSafeIntendedUrl($request, $intended)
        ) {
            $path = parse_url(
                $intended,
                PHP_URL_PATH
            ) ?: '/';

            if (
                $user->isAdmin() &&
                $this->matchesPathPrefix(
                    $path,
                    ['/admin']
                )
            ) {
                return redirect()
                    ->to($intended)
                    ->with(
                        'success',
                        $successMessage
                    );
            }

            if (
                $user->isCustomer() &&
                $this->matchesPathPrefix(
                    $path,
                    ['/account', '/checkout']
                )
            ) {
                return redirect()
                    ->to($intended)
                    ->with(
                        'success',
                        $successMessage
                    );
            }
        }

        return redirect()
            ->route($fallbackRoute)
            ->with(
                'success',
                $successMessage
            );
    }

    protected function isSafeIntendedUrl(
        Request $request,
        string $intended
    ): bool {
        $parsed = parse_url($intended);

        if ($parsed === false) {
            return false;
        }

        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? null;

        if ($host === null) {
            return true;
        }

        return hash_equals(
            $request->getHost(),
            $host
        ) &&
            (
                $port === null ||
                (int) $port === (int) $request->getPort()
            );
    }

    protected function matchesPathPrefix(
        string $path,
        array $prefixes
    ): bool {
        $normalizedPath = rtrim(
            $path,
            '/'
        );

        if ($normalizedPath === '') {
            $normalizedPath = '/';
        }

        foreach ($prefixes as $prefix) {
            $normalizedPrefix = rtrim(
                $prefix,
                '/'
            );

            if (
                $normalizedPath === $normalizedPrefix ||
                Str::startsWith(
                    $normalizedPath,
                    $normalizedPrefix . '/'
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function logout(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'با موفقیت خارج شدید.'
            );
    }

    protected function mergeGuestCartIntoUserCart(
        Request $request
    ): void {
        $sessionId = $request->session()->getId();

        $guestCart = Cart::query()
            ->where(
                'session_id',
                $sessionId
            )
            ->whereNull('user_id')
            ->where(
                'status',
                'active'
            )
            ->with('items')
            ->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::query()
            ->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'active',
                ],
                [
                    'session_id' => null,
                ]
            );

        foreach ($guestCart->items as $guestItem) {
            $existingItem = $userCart
                ->items()
                ->where(
                    'product_id',
                    $guestItem->product_id
                )
                ->where(
                    'product_variant_id',
                    $guestItem->product_variant_id
                )
                ->first();

            if ($existingItem) {
                $existingItem->increment(
                    'quantity',
                    $guestItem->quantity
                );

                continue;
            }

            $userCart->items()->create([
                'product_id' =>
                    $guestItem->product_id,

                'product_variant_id' =>
                    $guestItem->product_variant_id,

                'quantity' =>
                    $guestItem->quantity,

                'unit_price' =>
                    $guestItem->unit_price,
            ]);
        }

        $guestCart->items()->delete();

        $guestCart->update([
            'status' => 'converted',
        ]);
    }
}
