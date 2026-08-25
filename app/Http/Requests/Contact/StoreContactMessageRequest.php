<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
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
                'max:120',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
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
    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام و نام خانوادگی الزامی است.',
            'name.max' => 'نام نمی‌تواند بیشتر از ۱۲۰ کاراکتر باشد.',

            'phone.required' => 'وارد کردن شماره تماس الزامی است.',
            'phone.max' => 'شماره تماس واردشده معتبر نیست.',

            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'subject.required' => 'لطفاً موضوع پیام را انتخاب کنید.',
            'subject.in' => 'موضوع انتخاب‌شده معتبر نیست.',

            'message.required' => 'نوشتن پیام الزامی است.',
            'message.min' => 'پیام باید حداقل ۱۰ کاراکتر باشد.',
            'message.max' => 'پیام نمی‌تواند بیشتر از ۵۰۰۰ کاراکتر باشد.',
        ];
    }
}
