<?php

namespace AppHttpRequestsCart;

use IlluminateFoundationHttpFormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'تعداد محصول را وارد کنید.',
            'quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'quantity.min' => 'تعداد باید حداقل ۱ باشد.',
            'quantity.max' => 'تعداد بیشتر از حد مجاز است.',
        ];
    }
}
