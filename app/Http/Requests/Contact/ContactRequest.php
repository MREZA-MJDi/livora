<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
                'min:2',
                'max:100',
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'subject' => [
                'required',
                'string',
                'in:product,installment,order,shipping,other',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'نام و نام خانوادگی',
            'phone' => 'شماره تماس',
            'email' => 'ایمیل',
            'subject' => 'موضوع',
            'message' => 'پیام',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'لطفاً نام و نام خانوادگی خود را وارد کنید.',
            'name.min' => 'نام باید حداقل ۲ کاراکتر باشد.',
            'name.max' => 'نام نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',

            'phone.required' => 'لطفاً شماره تماس خود را وارد کنید.',
            'phone.max' => 'شماره تماس واردشده معتبر نیست.',

            'email.email' => 'فرمت ایمیل واردشده صحیح نیست.',

            'subject.required' => 'لطفاً موضوع پیام را انتخاب کنید.',
            'subject.in' => 'موضوع انتخاب‌شده معتبر نیست.',

            'message.required' => 'لطفاً پیام خود را وارد کنید.',
            'message.min' => 'پیام باید حداقل ۱۰ کاراکتر باشد.',
            'message.max' => 'پیام نمی‌تواند بیشتر از ۵۰۰۰ کاراکتر باشد.',
        ];
    }
}
