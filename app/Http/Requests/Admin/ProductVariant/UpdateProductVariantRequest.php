<?php

namespace App\Http\Requests\Admin\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productVariant = $this->route('productVariant');

        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'type' => [
                'required',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'value' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('product_variants', 'sku')
                    ->ignore($productVariant),
            ],

            'price_adjustment' => [
                'nullable',
                'numeric',
                'decimal:0,2',
            ],

            'stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'price_adjustment' => $this->price_adjustment ?? 0,
            'stock' => $this->stock ?? 0,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'انتخاب محصول الزامی است.',
            'product_id.exists' => 'محصول انتخاب‌شده وجود ندارد.',

            'type.required' => 'نوع ویژگی الزامی است.',

            'name.required' => 'نام ویژگی الزامی است.',

            'value.required' => 'مقدار ویژگی الزامی است.',

            'sku.unique' => 'این SKU قبلاً استفاده شده است.',

            'price_adjustment.numeric' => 'تعدیل قیمت باید عدد باشد.',
            'price_adjustment.decimal' => 'تعدیل قیمت باید حداکثر دو رقم اعشار داشته باشد.',

            'stock.integer' => 'موجودی باید عدد صحیح باشد.',
            'stock.min' => 'موجودی نمی‌تواند منفی باشد.',

            'is_active.boolean' => 'وضعیت فعال/غیرفعال نامعتبر است.',
        ];
    }
}
