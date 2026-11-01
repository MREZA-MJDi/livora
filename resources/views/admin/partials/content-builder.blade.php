@props([
    'sections' => [],
    'specifications' => [],
    'careGuide' => '',
    'comparisonRows' => [],
    'faqItems' => [],
    'availableRelatedCategories' => collect(),
    'availableRelatedProducts' => collect(),
    'selectedRelatedCategoryIds' => [],
    'selectedRelatedProductIds' => [],
    'includeSpecifications' => false,
    'includeCareGuide' => false,
    'includeComparison' => false,
    'includeRelatedCategories' => false,
    'includeRelatedProducts' => false,
])

@php
    $sections = old('content_sections', $sections);
    $specifications = old('specifications', $specifications);
    $careGuide = old('care_guide', $careGuide);
    $comparisonRows = old('comparison_rows', $comparisonRows);
    $faqItems = old('faq_items', $faqItems);
    $selectedRelatedCategoryIds = array_map('intval', old('related_category_ids', $selectedRelatedCategoryIds));
    $selectedRelatedProductIds = array_map('intval', old('related_product_ids', $selectedRelatedProductIds));
@endphp

<div class="admin-card space-y-7 p-6">
    <div>
        <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">CONTENT ARCHITECTURE</p>
        <h2 class="mt-2 text-base font-bold text-[var(--admin-text)]">ساختار محتوای قابل مدیریت</h2>
        <p class="mt-2 text-xs leading-7 text-[var(--admin-muted)]">محتوا را از همین داشبورد بسازید و مرتب کنید. متن‌ها به‌صورت امن و متنی ذخیره می‌شوند؛ HTML دلخواه اجرا نمی‌شود.</p>
    </div>

    <section x-data="{
        rows: @js(array_values(is_array($sections) ? $sections : [])),
        add() { this.rows.push({heading: '', body: ''}) },
        remove(index) { this.rows.splice(index, 1) }
    }" class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div><h3 class="text-sm font-bold">معرفی و بخش‌های محتوایی</h3><p class="admin-help">برای دسته: کاربردها، انواع و راهنمای انتخاب. برای محصول: معرفی تکمیلی و مزیت‌ها.</p></div>
            <button type="button" @click="add()" class="admin-btn admin-btn-secondary">افزودن بخش</button>
        </div>
        <template x-for="(row, index) in rows" :key="index">
            <div class="rounded-2xl border border-[var(--admin-border)] p-4">
                <div class="mb-3 flex items-center justify-between gap-3"><strong class="text-xs" x-text="'بخش ' + (index + 1)"></strong><button type="button" @click="remove(index)" class="text-xs text-[var(--admin-danger)]">حذف بخش</button></div>
                <label class="admin-label">عنوان بخش</label>
                <input class="admin-input" maxlength="160" :name="'content_sections[' + index + '][heading]'" x-model="row.heading" placeholder="مثلاً راهنمای انتخاب">
                <label class="admin-label mt-3">متن بخش</label>
                <textarea class="admin-textarea" rows="4" maxlength="6000" :name="'content_sections[' + index + '][body]'" x-model="row.body" placeholder="متن اختصاصی و مفید را بنویسید."></textarea>
            </div>
        </template>
        <p x-show="rows.length === 0" class="admin-help">هنوز بخشی اضافه نشده است.</p>
        @error('content_sections')<p class="admin-error">{{ $message }}</p>@enderror
        @error('content_sections.*.heading')<p class="admin-error">{{ $message }}</p>@enderror
        @error('content_sections.*.body')<p class="admin-error">{{ $message }}</p>@enderror
    </section>

    @if($includeSpecifications)
        <section x-data="{ rows: @js(array_values(is_array($specifications) ? $specifications : [])), add() { this.rows.push({label: '', value: ''}) }, remove(index) { this.rows.splice(index, 1) } }" class="space-y-4 border-t border-[var(--admin-border)] pt-6">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="text-sm font-bold">مشخصات فنی محصول</h3><p class="admin-help">برای متریال، ابعاد، رنگ، وزن، ظرفیت و سایر ویژگی‌ها ردیف اضافه کنید.</p></div><button type="button" @click="add()" class="admin-btn admin-btn-secondary">افزودن مشخصه</button></div>
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid gap-3 rounded-2xl border border-[var(--admin-border)] p-4 sm:grid-cols-[1fr_2fr_auto]">
                    <input class="admin-input" maxlength="100" :name="'specifications[' + index + '][label]'" x-model="row.label" placeholder="مثلاً جنس بدنه">
                    <input class="admin-input" maxlength="500" :name="'specifications[' + index + '][value]'" x-model="row.value" placeholder="مثلاً چوب راش">
                    <button type="button" @click="remove(index)" class="text-xs text-[var(--admin-danger)]">حذف</button>
                </div>
            </template>
            @error('specifications')<p class="admin-error">{{ $message }}</p>@enderror
            @error('specifications.*.label')<p class="admin-error">{{ $message }}</p>@enderror
            @error('specifications.*.value')<p class="admin-error">{{ $message }}</p>@enderror
        </section>
    @endif

    @if($includeCareGuide)
        <section class="space-y-3 border-t border-[var(--admin-border)] pt-6">
            <div><h3 class="text-sm font-bold">راهنمای نگهداری و استفاده</h3><p class="admin-help">روش نظافت، مراقبت از متریال و نکات استفاده را وارد کنید.</p></div>
            <textarea name="care_guide" rows="5" maxlength="6000" class="admin-textarea" placeholder="روش مراقبت و نگهداری محصول...">{{ $careGuide }}</textarea>
            @error('care_guide')<p class="admin-error">{{ $message }}</p>@enderror
        </section>
    @endif

    @if($includeComparison)
        <section x-data="{ rows: @js(array_values(is_array($comparisonRows) ? $comparisonRows : [])), add() { this.rows.push({label: '', value: ''}) }, remove(index) { this.rows.splice(index, 1) } }" class="space-y-4 border-t border-[var(--admin-border)] pt-6">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="text-sm font-bold">جدول مقایسه و معیارهای انتخاب</h3><p class="admin-help">هر ردیف یک معیار و توضیح مرتبط با همین دسته‌بندی است.</p></div><button type="button" @click="add()" class="admin-btn admin-btn-secondary">افزودن ردیف</button></div>
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid gap-3 rounded-2xl border border-[var(--admin-border)] p-4 sm:grid-cols-[1fr_2fr_auto]">
                    <input class="admin-input" maxlength="120" :name="'comparison_rows[' + index + '][label]'" x-model="row.label" placeholder="معیار">
                    <input class="admin-input" maxlength="500" :name="'comparison_rows[' + index + '][value]'" x-model="row.value" placeholder="توضیح یا مقدار">
                    <button type="button" @click="remove(index)" class="text-xs text-[var(--admin-danger)]">حذف</button>
                </div>
            </template>
            @error('comparison_rows')<p class="admin-error">{{ $message }}</p>@enderror
            @error('comparison_rows.*.label')<p class="admin-error">{{ $message }}</p>@enderror
            @error('comparison_rows.*.value')<p class="admin-error">{{ $message }}</p>@enderror
        </section>
    @endif

    <section x-data="{ rows: @js(array_values(is_array($faqItems) ? $faqItems : [])), add() { this.rows.push({question: '', answer: ''}) }, remove(index) { this.rows.splice(index, 1) } }" class="space-y-4 border-t border-[var(--admin-border)] pt-6">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><h3 class="text-sm font-bold">سؤالات متداول اختصاصی</h3><p class="admin-help">فقط پرسش‌های واقعی مشتری را ثبت کنید.</p></div><button type="button" @click="add()" class="admin-btn admin-btn-secondary">افزودن سؤال</button></div>
        <template x-for="(row, index) in rows" :key="index">
            <div class="space-y-3 rounded-2xl border border-[var(--admin-border)] p-4">
                <div class="flex items-center justify-between gap-3"><strong class="text-xs" x-text="'سؤال ' + (index + 1)"></strong><button type="button" @click="remove(index)" class="text-xs text-[var(--admin-danger)]">حذف</button></div>
                <input class="admin-input" maxlength="255" :name="'faq_items[' + index + '][question]'" x-model="row.question" placeholder="سؤال">
                <textarea class="admin-textarea" rows="3" maxlength="2000" :name="'faq_items[' + index + '][answer]'" x-model="row.answer" placeholder="پاسخ دقیق"></textarea>
            </div>
        </template>
        @error('faq_items')<p class="admin-error">{{ $message }}</p>@enderror
        @error('faq_items.*.question')<p class="admin-error">{{ $message }}</p>@enderror
        @error('faq_items.*.answer')<p class="admin-error">{{ $message }}</p>@enderror
    </section>

    @if($includeRelatedCategories)
        <section class="space-y-4 border-t border-[var(--admin-border)] pt-6">
            <div><h3 class="text-sm font-bold">دسته‌بندی‌های مرتبط</h3><p class="admin-help">برای ساخت مسیرهای داخلی مرتبط، دسته‌های فعال را انتخاب کنید.</p></div>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($availableRelatedCategories as $relatedCategory)
                    <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] p-3 text-xs"><input type="checkbox" class="admin-checkbox" name="related_category_ids[]" value="{{ $relatedCategory->id }}" @checked(in_array((int) $relatedCategory->id, $selectedRelatedCategoryIds, true))><span>{{ $relatedCategory->name }}</span></label>
                @endforeach
            </div>
        </section>
    @endif

    @if($includeRelatedProducts)
        <section class="space-y-4 border-t border-[var(--admin-border)] pt-6">
            <div><h3 class="text-sm font-bold">محصولات مرتبط</h3><p class="admin-help">محصولات مکمل یا جایگزین را برای نمایش در صفحه انتخاب کنید.</p></div>
            <div class="grid max-h-80 gap-2 overflow-y-auto sm:grid-cols-2">
                @foreach($availableRelatedProducts as $relatedProduct)
                    <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] p-3 text-xs"><input type="checkbox" class="admin-checkbox" name="related_product_ids[]" value="{{ $relatedProduct->id }}" @checked(in_array((int) $relatedProduct->id, $selectedRelatedProductIds, true))><span>{{ $relatedProduct->name }}</span></label>
                @endforeach
            </div>
        </section>
    @endif
</div>
