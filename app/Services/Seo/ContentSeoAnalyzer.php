<?php

namespace App\Services\Seo;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ContentSeoAnalyzer
{
    /**
     * Deterministic editorial checks; not a ranking or traffic prediction.
     */
    public function analyze(Model $record): array
    {
        $isProduct = $record instanceof Product;
        $keyword = trim((string) ($record->focus_keyword ?? ''));
        $metaTitle = trim((string) ($record->meta_title ?? ''));
        $metaDescription = trim((string) ($record->meta_description ?? ''));
        $title = $metaTitle !== '' ? $metaTitle : (string) ($record->name ?? '');
        $description = $metaDescription !== ''
            ? $metaDescription
            : (string) ($isProduct ? ($record->short_description ?? '') : ($record->description ?? ''));

        $sections = is_array($record->content_sections ?? null) ? $record->content_sections : [];
        $faq = is_array($record->faq_items ?? null) ? $record->faq_items : [];
        $keyFeatures = $isProduct && is_array($record->key_features ?? null) ? $record->key_features : [];
        $specifications = $isProduct && is_array($record->specifications ?? null) ? $record->specifications : [];
        $comparison = $record instanceof Category && is_array($record->comparison_rows ?? null) ? $record->comparison_rows : [];

        $parts = [
            $record->description ?? '',
            $record->short_description ?? '',
            $record->care_guide ?? '',
            ...array_map(fn ($row) => is_array($row) ? (($row['heading'] ?? '') . ' ' . ($row['body'] ?? '')) : '', $sections),
            ...array_map(fn ($row) => is_array($row) ? (($row['label'] ?? '') . ' ' . ($row['value'] ?? '')) : '', $keyFeatures),
            ...array_map(fn ($row) => is_array($row) ? (($row['label'] ?? '') . ' ' . ($row['value'] ?? '')) : '', $specifications),
            ...array_map(fn ($row) => is_array($row) ? (($row['label'] ?? '') . ' ' . ($row['value'] ?? '')) : '', $comparison),
            ...array_map(fn ($row) => is_array($row) ? (($row['question'] ?? '') . ' ' . ($row['answer'] ?? '')) : '', $faq),
        ];
        $body = trim(preg_replace('/\s+/u', ' ', strip_tags(implode(' ', $parts))) ?? '');
        $wordCount = count(array_filter(preg_split('/\s+/u', $body) ?: []));
        $hasHeading = collect($sections)->contains(fn ($row) => is_array($row) && filled($row['heading'] ?? null));

        if ($record instanceof Category) {
            $hasAlt = filled($record->image_alt ?? null);
        } elseif ($isProduct) {
            $images = $record->relationLoaded('images') ? $record->images : $record->images()->get();
            $hasAlt = $images->contains(fn ($image) => filled($image->alt));
        } else {
            $hasAlt = true;
        }

        $checks = [
            $this->check('focus_keyword', 'کلمه کلیدی کانونی ثبت شده', $keyword !== '', 'یک عبارت اصلی و دقیق برای این صفحه ثبت کنید.'),
            $this->check('meta_title', 'عنوان متا اختصاصی', $metaTitle !== '', 'عنوان اختصاصی برای نتیجه جست‌وجو ثبت کنید.'),
            $this->check('title_length', 'طول عنوان متا مناسب', mb_strlen($metaTitle) >= 30 && mb_strlen($metaTitle) <= 65, 'راهنمای داخلی: حدود ۳۰ تا ۶۵ نویسه.'),
            $this->check('meta_description', 'توضیحات متا اختصاصی', $metaDescription !== '', 'توضیح یکتا و واقعی برای همین صفحه بنویسید.'),
            $this->check('description_length', 'طول توضیحات متا مناسب', mb_strlen($metaDescription) >= 70 && mb_strlen($metaDescription) <= 170, 'راهنمای داخلی: حدود ۷۰ تا ۱۷۰ نویسه.'),
            $this->check('keyword_title', 'کلمه کلیدی در عنوان', $keyword !== '' && mb_stripos($title, $keyword) !== false, 'در صورت طبیعی‌بودن، عبارت اصلی را در عنوان جای دهید.'),
            $this->check('keyword_description', 'کلمه کلیدی در توضیحات', $keyword !== '' && mb_stripos($description, $keyword) !== false, 'عبارت اصلی را طبیعی در توضیحات استفاده کنید.'),
            $this->check('content_depth', 'محتوای اختصاصی کافی', $wordCount >= 180, 'راهنمای داخلی: حدود ۱۸۰ واژهٔ مفید؛ کیفیت مهم‌تر از تعداد است.'),
            $this->check('content_heading', 'بخش‌بندی محتوا', $hasHeading, 'حداقل یک بخش محتوایی با عنوان مشخص اضافه کنید.'),
            $this->check('image_alt', 'تصویر دارای متن جایگزین', (bool) $hasAlt, 'برای تصویر شاخص Alt توصیفی ثبت کنید.'),
            $this->check('faq', 'سؤالات متداول واقعی', count($faq) > 0, 'در صورت وجود پرسش واقعی مشتری، آن را ثبت کنید.'),
        ];

        $passed = count(array_filter($checks, fn ($item) => $item['passed']));

        return [
            'score' => (int) round(($passed / max(1, count($checks))) * 100),
            'passed' => $passed,
            'total' => count($checks),
            'checks' => $checks,
        ];
    }

    private function check(string $key, string $label, bool $passed, string $detail): array
    {
        return compact('key', 'label', 'passed', 'detail');
    }
}
