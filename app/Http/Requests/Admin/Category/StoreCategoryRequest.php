<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'seo_index' => $this->boolean('seo_index'),
            'seo_follow' => $this->boolean('seo_follow'),
            'slug' => $this->slug
                ? Str::slug($this->slug)
                : Str::slug($this->name),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->sort_order ?? 0,
        ]);

        $this->merge([
            'content_sections' => $this->cleanRows('content_sections', ['heading', 'body']),
            'comparison_rows' => $this->cleanRows('comparison_rows', ['label', 'value']),
            'faq_items' => $this->cleanRows('faq_items', ['question', 'answer']),
            'related_category_ids' => is_array($this->input('related_category_ids')) ? array_values(array_unique(array_map('intval', $this->input('related_category_ids')))) : $this->input('related_category_ids'),
            'related_product_ids' => is_array($this->input('related_product_ids')) ? array_values(array_unique(array_map('intval', $this->input('related_product_ids')))) : $this->input('related_product_ids'),
        ]);
    }

    private function cleanRows(string $key, array $fields): mixed
    {
        $rows = $this->input($key, []);
        if (! is_array($rows)) {
            return $rows;
        }

        return collect($rows)
            ->filter(function ($row) use ($fields): bool {
                return ! is_array($row)
                    || collect($fields)->contains(fn ($field) => filled($row[$field] ?? null));
            })
            ->map(function ($row) use ($fields) {
                if (! is_array($row)) {
                    return $row;
                }

                return collect($fields)->mapWithKeys(
                    fn ($field) => [$field => trim((string) ($row[$field] ?? ''))]
                )->all();
            })
            ->values()->all();
    }



    public function rules(): array
    {
        return [
            'seo_index' => ['sometimes', 'boolean'],
            'seo_follow' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'focus_keyword' => ['nullable', 'string', 'max:120'],
            'content_sections' => ['nullable', 'array', 'max:12'],
            'content_sections.*.heading' => ['required', 'string', 'max:160'],
            'content_sections.*.body' => ['required', 'string', 'max:6000'],
            'comparison_rows' => ['nullable', 'array', 'max:30'],
            'comparison_rows.*.label' => ['required', 'string', 'max:120'],
            'comparison_rows.*.value' => ['required', 'string', 'max:500'],
            'faq_items' => ['nullable', 'array', 'max:20'],
            'faq_items.*.question' => ['required', 'string', 'max:255'],
            'faq_items.*.answer' => ['required', 'string', 'max:2000'],
            'related_category_ids' => ['nullable', 'array', 'max:30'],
            'related_category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'related_product_ids' => ['nullable', 'array', 'max:60'],
            'related_product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'og_image' => ['nullable', 'url', 'max:2048'],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:categories,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام دسته‌بندی الزامی است.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'slug.required' => 'Slug دسته‌بندی الزامی است.',
            'slug.unique' => 'این Slug قبلاً استفاده شده است.',

            'description.string' => 'توضیحات باید به صورت متن باشد.',

            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۱۰ مگابایت باشد.',

            'sort_order.integer' => 'ترتیب نمایش باید عدد باشد.',
            'sort_order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',
        ];
    }
}
