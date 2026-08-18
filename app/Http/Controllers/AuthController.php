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
        dd([
            'id' => $request->user()->id,
            'email' => $request->user()->email,
            'role' => $request->user()->role,
            'is_admin' => $request->user()->isAdmin(),
        ]);

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
