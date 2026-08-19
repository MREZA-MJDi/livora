<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $installmentEnabled =
            $this->boolean('installment_enabled');

        $this->merge([
            'slug' =>
                $this->slug
                    ?: Str::slug($this->name),

            'stock' =>
                $this->stock ?? 0,

            'is_featured' =>
                $this->boolean('is_featured'),

            'is_new' =>
                $this->boolean('is_new'),

            'installment_enabled' =>
                $installmentEnabled,

            'installment_cash_percent' =>
                $installmentEnabled
                    ? ($this->installment_cash_percent ?? 50)
                    : null,

            'installment_remainder_method' =>
                $installmentEnabled
                    ? ($this->installment_remainder_method ?? 'cheque')
                    : null,

            'installment_cheque_count' =>
                $installmentEnabled
                    ? ($this->installment_cheque_count ?? 2)
                    : null,

            'installment_interval_months' =>
                $installmentEnabled
                    ? ($this->installment_interval_months ?? 2)
                    : null,
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->ignore($product),
            ],

            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')
                    ->ignore($product),
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'compare_at_price' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],

            'stock' => [
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'active',
                    'archived',
                ]),
            ],

            'is_featured' => [
                'boolean',
            ],

            'is_new' => [
                'boolean',
            ],

            /*
             |--------------------------------------------------------------------------
             | Installments
             |--------------------------------------------------------------------------
             */

            'installment_enabled' => [
                'boolean',
            ],

            'installment_cash_percent' => [
                'nullable',
                'integer',
                'between:1,99',
                'required_if:installment_enabled,1',
            ],

            'installment_remainder_method' => [
                'nullable',
                'string',
                Rule::in([
                    'cheque',
                ]),
                'required_if:installment_enabled,1',
            ],

            'installment_cheque_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:30',
                'required_if:installment_enabled,1',
            ],

            'installment_interval_months' => [
                'nullable',
                'integer',
                'min:1',
                'max:24',
                'required_if:installment_enabled,1',
            ],

            /*
             |--------------------------------------------------------------------------
             | SEO
             |--------------------------------------------------------------------------
             */

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
