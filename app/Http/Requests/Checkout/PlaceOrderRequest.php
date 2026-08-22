<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
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

            'phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
            ],

            'province' => [
                'required',
                'string',
                'max:100',
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],

            'address' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],

            'postal_code' => [
                'required',
                'digits:10',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'phone' => 'شماره موبایل',
            'email' => 'ایمیل',
            'province' => 'استان',
            'city' => 'شهر',
            'address' => 'آدرس',
            'postal_code' => 'کد پستی',
            'unit' => 'واحد',
            'notes' => 'توضیحات',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' =>
                'وارد کردن نام الزامی است.',

            'last_name.required' =>
                'وارد کردن نام خانوادگی الزامی است.',

            'phone.required' =>
                'شماره موبایل را وارد کنید.',

            'phone.regex' =>
                'شماره موبایل باید به شکل 09123456789 باشد.',

            'email.required' =>
                'ایمیل را وارد کنید.',

            'email.email' =>
                'فرمت ایمیل صحیح نیست.',

            'province.required' =>
                'استان را انتخاب یا وارد کنید.',

            'city.required' =>
                'شهر را وارد کنید.',

            'address.required' =>
                'آدرس کامل را وارد کنید.',

            'address.min' =>
                'آدرس واردشده خیلی کوتاه است.',

            'postal_code.required' =>
                'کد پستی را وارد کنید.',

            'postal_code.digits' =>
                'کد پستی باید دقیقاً ۱۰ رقم باشد.',
        ];
    }
}
