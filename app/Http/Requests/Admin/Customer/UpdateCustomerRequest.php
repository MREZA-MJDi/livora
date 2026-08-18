<?php

namespace App\Http\Requests\Admin\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->route('customer')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام مشتری الزامی است.',
            'name.string' => 'نام مشتری باید به صورت متن باشد.',
            'name.max' => 'نام مشتری نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'email.required' => 'ایمیل مشتری الزامی است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.max' => 'ایمیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'email.unique' => 'این ایمیل قبلاً استفاده شده است.',
        ];
    }
}
