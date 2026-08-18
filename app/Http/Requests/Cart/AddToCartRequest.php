<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
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

            'product_variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('is_active', true),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');

            if (! $product instanceof Product) {
                return;
            }

            if ($product->status !== 'active') {
                $validator->errors()->add(
                    'product',
                    'این محصول در حال حاضر قابل خرید نیست.'
                );

                return;
            }

            if ($product->stock < 1) {
                $validator->errors()->add(
                    'product',
                    'این محصول موجود نیست.'
                );
            }

            $variantId = $this->input('product_variant_id');

            if ($variantId) {
                $variant = $product->variants()
                    ->whereKey($variantId)
                    ->first();

                if (! $variant) {
                    $validator->errors()->add(
                        'product_variant_id',
                        'گزینه انتخاب‌شده متعلق به این محصول نیست.'
                    );
                }
            }
        });
    }
}
