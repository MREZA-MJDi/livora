<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('categories', 'slug')
                    ->where('is_active', true),
            ],

            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:min_price',
            ],

            'in_stock' => [
                'nullable',
                'boolean',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'newest',
                    'popular',
                    'price_asc',
                    'price_desc',
                    'name_asc',
                ]),
            ],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'in_stock' => $this->boolean('in_stock'),
        ]);
    }
}
