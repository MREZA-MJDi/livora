<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            'price' => $this->normalizeNumericInput($this->input('price')),
            'compare_at_price' => $this->normalizeNumericInput($this->input('compare_at_price')),
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

            /*
             * Disable installment settings completely
             * when installment sales are disabled.
             */
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

    /**
     * Normalize Persian/Arabic digits and formatted money values
     * before Laravel numeric validation runs.
     */
    private function normalizeNumericInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];

        $value = str_replace(
            $persian,
            ['0','1','2','3','4','5','6','7','8','9'],
            $value
        );

        $value = str_replace(
            $arabic,
            ['0','1','2','3','4','5','6','7','8','9'],
            $value
        );

        $value = str_replace(
            [',', '٬', ' ', '٫'],
            ['', '', '', '.'],
            $value
        );

        $value = preg_replace('/[^0-9.\-]/u', '', $value) ?? '';

        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $value = array_shift($parts) . '.' . implode('', $parts);
        }

        return $value !== '' ? $value : null;
    }

    public function rules(): array
    {
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
                'unique:products,slug',
            ],

            'sku' => [
                'required',
                'string',
                'max:255',
                'unique:products,sku',
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
                'gte:price',
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

    public function messages(): array
    {
        return [
            'price.required' => 'وارد کردن قیمت محصول الزامی است.',
            'price.numeric' => 'قیمت محصول باید یک عدد معتبر باشد.',
            'price.min' => 'قیمت محصول نمی‌تواند منفی باشد.',
            'price.decimal' => 'قیمت محصول باید حداکثر دو رقم اعشار داشته باشد.',
            'compare_at_price.numeric' => 'قیمت قبل باید یک عدد معتبر باشد.',
            'compare_at_price.min' => 'قیمت قبل نمی‌تواند منفی باشد.',
            'compare_at_price.decimal' => 'قیمت قبل باید حداکثر دو رقم اعشار داشته باشد.',
            'compare_at_price.gte' => 'قیمت قبل باید بیشتر یا مساوی قیمت فعلی باشد.',

            'category_id.required' => 'انتخاب دسته‌بندی الزامی است.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده معتبر نیست.',
            'name.required' => 'نام محصول الزامی است.',
            'slug.unique' => 'این Slug قبلاً استفاده شده است.',
            'sku.unique' => 'این SKU قبلاً استفاده شده است.',
            'stock.integer' => 'موجودی باید عدد صحیح باشد.',
            'stock.min' => 'موجودی نمی‌تواند منفی باشد.',
            'status.in' => 'وضعیت محصول نامعتبر است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'دسته‌بندی',
            'name' => 'نام محصول',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'price' => 'قیمت',
            'compare_at_price' => 'قیمت قبل',
            'stock' => 'موجودی',
            'status' => 'وضعیت',
        ];
    }

