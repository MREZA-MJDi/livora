<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
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
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'regex:/^09\d{9}$/',
            ],

            'password' => [
                'required',
                'confirmed',
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
            'password.letters' => 'رمز عبور باید حداقل یک حرف داشته باشد.',
            'password.mixed' => 'رمز عبور باید حداقل یک حرف بزرگ و یک حرف کوچک داشته باشد.',
            'password.numbers' => 'رمز عبور باید حداقل یک عدد داشته باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'phone.regex' => 'شماره موبایل باید با فرمت 09123456789 وارد شود.',
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
            'terms' => 'قوانین و شرایط',
        ];
    }
}
