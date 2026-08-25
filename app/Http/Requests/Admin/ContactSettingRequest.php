<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'hours' => [
                'nullable',
                'string',
            ],

            'phone_label' => [
                'required',
                'string',
                'max:100',
            ],

            'email_label' => [
                'required',
                'string',
                'max:100',
            ],

            'support_label' => [
                'required',
                'string',
                'max:100',
            ],

            'online_label' => [
                'required',
                'string',
                'max:100',
            ],

            'support_description' => [
                'nullable',
                'string',
            ],

            'online_description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'شماره تماس',
            'email' => 'ایمیل',
            'address' => 'آدرس',
            'hours' => 'ساعات کاری',
            'phone_label' => 'عنوان تماس تلفنی',
            'email_label' => 'عنوان ایمیل',
            'support_label' => 'عنوان پشتیبانی',
            'online_label' => 'عنوان ارتباط آنلاین',
            'support_description' => 'توضیحات پشتیبانی',
            'online_description' => 'توضیحات ارتباط آنلاین',
            'is_active' => 'وضعیت فعال بودن',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'فرمت ایمیل واردشده صحیح نیست.',

            'phone.max' => 'شماره تماس نمی‌تواند بیشتر از :max کاراکتر باشد.',

            'phone_label.required' => 'عنوان تماس تلفنی الزامی است.',
            'email_label.required' => 'عنوان ایمیل الزامی است.',
            'support_label.required' => 'عنوان پشتیبانی الزامی است.',
            'online_label.required' => 'عنوان ارتباط آنلاین الزامی است.',

            '*.string' => 'مقدار واردشده باید به‌صورت متن باشد.',
        ];
    }
}
