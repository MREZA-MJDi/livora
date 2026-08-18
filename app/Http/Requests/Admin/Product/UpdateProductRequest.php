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
        $this->merge([
            'slug' => $this->slug ?: Str::slug($this->name),
            'stock' => $this->stock ?? 0,
            'is_featured' => $this->boolean('is_featured'),
            'is_new' => $this->boolean('is_new'),
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
                Rule::unique('products', 'slug')->ignore($product),
            ],

            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'sku')->ignore($product),
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
