<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.passwords.email');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));

        try {
            // The UI response is deliberately identical whether or not the
            // account exists, avoiding account enumeration through this form.
            Password::sendResetLink(['email' => $email]);
        } catch (Throwable $exception) {
            // Keep the public response generic; retain the actual failure in logs.
            report($exception);
        }

        return back()->with(
            'status',
            'اگر حسابی با این ایمیل ثبت شده باشد، لینک بازنشانی برای آن ارسال می‌شود.'
        );
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('auth.passwords.reset', [
            'token' => $token,
            'email' => Str::lower(trim((string) $request->query('email', ''))),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
        ]);

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'max:255',
                PasswordRule::min(8)->letters()->mixedCase()->numbers(),
            ],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with('status', 'رمز عبور تغییر کرد. حالا وارد حساب شوید.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'لینک بازنشانی معتبر نیست یا منقضی شده است.',
            ]);
    }
}
