<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'last_name' => [
                'bail',
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'email' => [
                'bail',
                'required',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'bail',
                'nullable',
                'string',
                'regex:/^09\d{9}$/',
            ],

            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',
                'max:255',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],

            'terms' => [
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'وارد کردن نام الزامی است.',
            'first_name.min' => 'نام باید حداقل ۲ کاراکتر باشد.',
            'first_name.max' => 'نام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'last_name.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'last_name.min' => 'نام خانوادگی باید حداقل ۲ کاراکتر باشد.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'email.required' => 'وارد کردن ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',

            'phone.regex' => 'شماره موبایل را به‌صورت 09123456789 وارد کنید.',

            'password.required' => 'وارد کردن رمز عبور الزامی است.',
            'password.max' => 'رمز عبور نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.letters' => 'رمز عبور باید حداقل یک حرف داشته باشد.',
            'password.mixed' => 'رمز عبور باید حداقل یک حرف بزرگ و یک حرف کوچک داشته باشد.',
            'password.numbers' => 'رمز عبور باید حداقل یک عدد داشته باشد.',
            'password' => 'رمز عبور باید حداقل ۸ کاراکتر و شامل حرف بزرگ، حرف کوچک و عدد باشد.',

            'terms.accepted' => 'برای ایجاد حساب، پذیرش قوانین و شرایط الزامی است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'email' => 'ایمیل',
            'phone' => 'شماره موبایل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
            'terms' => 'قوانین و شرایط',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => trim(
                (string) $this->input('first_name')
            ),
            'last_name' => trim(
                (string) $this->input('last_name')
            ),
            'email' => Str::lower(
                trim((string) $this->input('email'))
            ),
            'phone' => $this->normalizePhone(
                $this->input('phone')
            ),
            'terms' => $this->boolean('terms'),
        ]);
    }

    private function normalizePhone(
        mixed $value
    ): ?string {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        $digits = preg_replace(
            '/\D+/',
            '',
            $value
        ) ?? '';

        if (Str::startsWith($digits, '0098')) {
            $digits = '0' . substr($digits, 4);
        } elseif (Str::startsWith($digits, '98')) {
            $digits = '0' . substr($digits, 2);
        }

        return $digits !== ''
            ? $digits
            : null;
    }
}
