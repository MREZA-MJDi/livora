<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:100',
            ],

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
                'regex:/^09\d{9}$/',
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

            'is_default' => [
                'nullable',
                'boolean',
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

        $phone = preg_replace('/\\s+/u', '', (string) $this->input('phone', ''));
        $postalCode = preg_replace('/\\s+/u', '', (string) $this->input('postal_code', ''));

        $this->merge([
            'phone' => strtr($phone, $digitMap),
            'postal_code' => strtr($postalCode, $digitMap),
            'is_default' => $this->boolean('is_default'),
        ]);
    }
}
