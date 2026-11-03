<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $regions = config('iran_shipping.provinces', []);
        $provinceNames = array_column($regions, 'name');
        $selectedProvince = trim((string) $this->input('province', ''));

        $selectedRegion = collect($regions)->first(
            fn (array $region): bool => ($region['name'] ?? null) === $selectedProvince
        );

        $cityRules = [
            'required',
            'string',
            'min:2',
            'max:100',
        ];

        if (! $this->boolean('city_is_other')) {
            $cityRules[] = Rule::in($selectedRegion['cities'] ?? []);
        }

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

            // Email is useful for a receipt, but it is not required to deliver an order.
            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
            ],

            'province' => [
                'required',
                'string',
                'max:100',
                Rule::in($provinceNames),
            ],

            'city' => $cityRules,

            'city_is_other' => [
                'nullable',
                'boolean',
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

    protected function prepareForValidation(): void
    {
        $digitMap = [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ];

        $phone = strtr((string) $this->input('phone', ''), $digitMap);
        $phone = preg_replace('/[^0-9+]/', '', $phone) ?? '';

        if (str_starts_with($phone, '+98')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '0098')) {
            $phone = '0' . substr($phone, 4);
        } elseif (preg_match('/^98\d{10}$/', $phone)) {
            $phone = '0' . substr($phone, 2);
        }

        $postalCode = strtr((string) $this->input('postal_code', ''), $digitMap);
        $postalCode = preg_replace('/\D/u', '', $postalCode) ?? '';

        $email = trim((string) $this->input('email', ''));

        $this->merge([
            'first_name' => trim((string) $this->input('first_name', '')),
            'last_name' => trim((string) $this->input('last_name', '')),
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'province' => trim((string) $this->input('province', '')),
            'city' => trim((string) $this->input('city', '')),
            'city_is_other' => $this->boolean('city_is_other'),
            'postal_code' => $postalCode,
        ]);
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
            'first_name.required' => 'وارد کردن نام الزامی است.',
            'last_name.required' => 'وارد کردن نام خانوادگی الزامی است.',
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'phone.regex' => 'شماره موبایل باید به شکل 09123456789 باشد.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'province.required' => 'استان را انتخاب کنید.',
            'province.in' => 'استان انتخاب‌شده معتبر نیست.',
            'city.required' => 'شهر را انتخاب یا وارد کنید.',
            'city.min' => 'نام شهر را کامل‌تر وارد کنید.',
            'city.in' => 'شهر انتخاب‌شده با استان انتخابی مطابقت ندارد.',
            'address.required' => 'آدرس کامل را وارد کنید.',
            'address.min' => 'آدرس واردشده خیلی کوتاه است.',
            'postal_code.required' => 'کد پستی را وارد کنید.',
            'postal_code.digits' => 'کد پستی باید دقیقاً ۱۰ رقم باشد.',
        ];
    }
}
