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
            'brand_name' => ['nullable', 'string', 'max:120'],
            'logo_path' => ['nullable', 'string', 'max:2048'],
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
            'whatsapp_url' => ['nullable', 'url', 'max:2048'],
            'telegram_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'eitaa_url' => ['nullable', 'url', 'max:2048'],
            'bale_url' => ['nullable', 'url', 'max:2048'],
            'map_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'map_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'warranty_text' => ['nullable', 'string', 'max:5000'],
            'shipping_text' => ['nullable', 'string', 'max:5000'],
            'installation_text' => ['nullable', 'string', 'max:5000'],
            'returns_text' => ['nullable', 'string', 'max:5000'],
            'floating_actions_enabled' => ['nullable', 'boolean'],

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
