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

        $guestCartSessionId = $request->session()->getId();

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

        $this->mergeGuestCartIntoUserCart($request, $guestCartSessionId);

        return $this->redirectAfterAuthentication($request);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(
        RegisterRequest $request
    ): RedirectResponse {
        $guestCartSessionId = $request->session()->getId();
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

                'phone' => $validated['phone'] ?? null,

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

        $this->mergeGuestCartIntoUserCart($request, $guestCartSessionId);


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
            is_string($intended)
            && $this->isSafeIntendedUrl($request, $intended)
        ) {
            $path = parse_url($intended, PHP_URL_PATH) ?: '/';

            if (
                $user->isAdmin()
                && $this->matchesPathPrefix($path, ['/admin'])
            ) {
                return redirect()->to($intended)->with('success', $successMessage);
            }

            if (
                $user->isCustomer()
                && $this->matchesPathPrefix($path, ['/account', '/checkout'])
            ) {
                return redirect()->to($intended)->with('success', $successMessage);
            }
        }

        return redirect()
            ->route($fallbackRoute)
            ->with('success', $successMessage);
    }

    /**
     * Do not allow session-stored intended URLs to redirect to external hosts,
     * protocol-relative URLs, non-web schemes, or a different scheme.
     */
    protected function isSafeIntendedUrl(
        Request $request,
        string $intended
    ): bool {
        if (
            str_contains($intended, '\\')
            || preg_match('/[\\r\\n]/', $intended)
        ) {
            return false;
        }

        $parsed = parse_url($intended);

        if (
            $parsed === false
            || isset($parsed['user'])
            || isset($parsed['pass'])
        ) {
            return false;
        }

        $scheme = strtolower((string) ($parsed['scheme'] ?? $request->getScheme()));

        if (
            ! in_array($scheme, ['http', 'https'], true)
            || $scheme !== strtolower($request->getScheme())
        ) {
            return false;
        }

        $host = $parsed['host'] ?? null;
        $port = $parsed['port'] ?? null;

        if ($host === null) {
            return str_starts_with($intended, '/')
                && ! str_starts_with($intended, '//');
        }

        return hash_equals(
            strtolower($request->getHost()),
            strtolower($host)
        ) && (
            $port === null
            || (int) $port === (int) $request->getPort()
        );
    }

    protected function matchesPathPrefix(
        string $path,
        array $prefixes
    ): bool {
        $normalizedPath = rtrim($path, '/') ?: '/';

        foreach ($prefixes as $prefix) {
            $normalizedPrefix = rtrim($prefix, '/') ?: '/';

            if (
                $normalizedPath === $normalizedPrefix
                || str_starts_with($normalizedPath, $normalizedPrefix . '/')
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
        Request $request,
        ?string $guestSessionId = null
    ): void {
        $sessionId = $guestSessionId ?? $request->session()->getId();

        DB::transaction(function () use ($sessionId) {
            // Serialize concurrent logins/cart merges for the same account.
            User::query()
                ->whereKey(Auth::id())
                ->lockForUpdate()
                ->firstOrFail();

            $guestCart = Cart::query()
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $guestCart) {
                return;
            }

            $guestCart->load('items');

            $userCart = Cart::query()
                ->where('user_id', Auth::id())
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (! $userCart) {
                $userCart = Cart::create([
                    'user_id' => Auth::id(),
                    'session_id' => null,
                    'status' => 'active',
                ]);
            }

            foreach ($guestCart->items as $guestItem) {
                $selectedIds = $guestItem->selectedVariantIds();
                $variantKey = $guestItem->variant_key;

                $existingQuery = $userCart->items()
                    ->where('product_id', $guestItem->product_id);

                if (filled($variantKey)) {
                    $existingQuery->where(function ($query) use (
                        $variantKey,
                        $guestItem,
                        $selectedIds
                    ) {
                        $query->where('variant_key', $variantKey);

                        // Merge legacy one-variant rows only when the selection
                        // is unambiguous; never merge two different combinations
                        // just because they share the primary variant ID.
                        if (count($selectedIds) === 1) {
                            $query->orWhere(function ($legacy) use ($selectedIds) {
                                $legacy
                                    ->whereNull('variant_key')
                                    ->whereNull('variant_options')
                                    ->where('product_variant_id', $selectedIds[0]);
                            });
                        } elseif ($variantKey === 'base' || $selectedIds === []) {
                            $query->orWhere(function ($legacy) {
                                $legacy
                                    ->whereNull('variant_key')
                                    ->whereNull('variant_options')
                                    ->whereNull('product_variant_id');
                            });
                        }
                    });
                } elseif ($guestItem->product_variant_id !== null) {
                    $existingQuery
                        ->whereNull('variant_key')
                        ->where('product_variant_id', $guestItem->product_variant_id);
                } else {
                    $existingQuery
                        ->whereNull('variant_key')
                        ->whereNull('variant_options')
                        ->whereNull('product_variant_id');
                }

                $existingItem = $existingQuery->lockForUpdate()->first();

                $payload = [
                    'product_variant_id' => $guestItem->product_variant_id,
                    'variant_key' => $variantKey ?? 'base',
                    'variant_options' => $guestItem->variant_options,
                    'unit_price' => $guestItem->unit_price,
                ];

                if ($existingItem) {
                    $existingItem->update(array_merge($payload, [
                        'quantity' => (int) $existingItem->quantity + (int) $guestItem->quantity,
                    ]));

                    continue;
                }

                $userCart->items()->create(array_merge($payload, [
                    'product_id' => $guestItem->product_id,
                    'quantity' => $guestItem->quantity,
                ]));
            }

            $guestCart->items()->delete();
            $guestCart->update(['status' => 'converted']);
        });
    }
}
