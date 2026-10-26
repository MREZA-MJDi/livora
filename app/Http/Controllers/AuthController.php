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

        /*
        |--------------------------------------------------------------------------
        | Role Based Redirect
        |--------------------------------------------------------------------------
        */

        if ($request->user()->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    'به پنل مدیریت خوش آمدید.'
                );
        }

        return redirect()
            ->intended(route('account.index'))
            ->with(
                'success',
                'با موفقیت وارد شدید.'
            );
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


        if ($request->user()->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    'به پنل مدیریت خوش آمدید.'
                );
        }

        return redirect()
            ->intended(route('account.index'))
            ->with(
                'success',
                'حساب کاربری با موفقیت ایجاد شد.'
            );
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
