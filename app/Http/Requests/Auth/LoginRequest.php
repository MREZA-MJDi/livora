<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'bail',
                'required',
                'email:rfc',
                'max:255',
            ],

            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
                'max:255',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'وارد کردن ایمیل الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'password.required' => 'وارد کردن رمز عبور الزامی است.',
            'password.string' => 'رمز عبور واردشده معتبر نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'password.max' => 'رمز عبور نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'remember' => 'مرا به خاطر بسپار',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(
                trim((string) $this->input('email'))
            ),
            'remember' => $this->boolean('remember'),
        ]);
    }
}
