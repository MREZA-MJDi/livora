@csrf

@php
    $editing = isset($category);
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    <div class="space-y-6 xl:col-span-2">

        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات اصلی
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    اطلاعات پایه دسته‌بندی را وارد کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div class="sm:col-span-2">

                    <label for="name" class="admin-label">
                        نام دسته‌بندی
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $category->name ?? '') }}"
                        class="admin-input"
                        required
                    >

                    @error('name')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="sm:col-span-2">

                    <label for="slug" class="admin-label">
                        آدرس اختصاصی دسته‌بندی (اختیاری)
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        type="text"
                        value="{{ old('slug', $category->slug ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                    >

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        اگر با این بخش آشنا نیستید، آن را خالی بگذارید تا آدرس به‌صورت خودکار ساخته شود.
                    </p>

                    @error('slug')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="sm:col-span-2">

                    <label for="description" class="admin-label">
                        توضیحات
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        class="admin-textarea"
                    >{{ old('description', $category->description ?? '') }}</textarea>

                    @error('description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>


        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    نمایش و ترتیب
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    ترتیب و وضعیت نمایش دسته‌بندی را تعیین کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div>

                    <label for="sort_order" class="admin-label">
                        ترتیب نمایش
                    </label>

                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        value="{{ old('sort_order', $category->sort_order ?? 0) }}"
                        class="admin-input"
                    >

                    @error('sort_order')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div class="flex items-end">

                    <label class="flex w-full cursor-pointer items-center gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-4 py-3">

                        <input
                            type="hidden"
                            name="is_active"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="admin-checkbox h-4 w-4"
                            @checked(old('is_active', $category->is_active ?? true))
                        >

                        <span>
                            <span class="block text-sm font-semibold text-[var(--admin-text)]">
                                دسته‌بندی فعال باشد
                            </span>

                            <span class="mt-1 block text-xs text-[var(--admin-muted)]">
                                دسته‌بندی در بخش فروشگاه قابل نمایش خواهد بود.
                            </span>
                        </span>

                    </label>

                </div>

            </div>

            @error('is_active')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>

    </div>


    <div class="space-y-6">

        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    تصویر دسته‌بندی
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    تصویر اصلی دسته‌بندی را انتخاب کنید.
                </p>
            </div>


            @php
                $categoryImageUrl = $editing
                    ? $category->image_url
                    : null;
            @endphp

            <div class="admin-image-preview aspect-[4/3]">

                <img
                    id="category-image-preview"
                    src="{{ $categoryImageUrl ?: '' }}"
                    alt="{{ $category->name ?? 'تصویر دسته‌بندی' }}"
                    class="admin-image {{ $categoryImageUrl ? '' : 'hidden' }}"
                >

                <div
                    id="category-image-placeholder"
                    class="{{ $categoryImageUrl ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center p-6 text-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor"
                         class="mb-3 h-10 w-10 text-[var(--admin-muted)]">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 20.25 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8.25 8.25h.008v.008H8.25V8.25Z" />
                    </svg>

                    <span class="text-xs text-[var(--admin-muted)]">
                        {{ $editing ? 'تصویری برای این دسته‌بندی ثبت نشده است.' : 'هنوز تصویری انتخاب نشده است.' }}
                    </span>
                </div>

            </div>


            <div class="mt-5">

                <label for="image" class="admin-label">
                    انتخاب تصویر
                </label>

                <input
                    id="image"
                    name="image" data-crop-ratio="1.3333333" data-preview-target="category-image-preview" data-preview-placeholder="category-image-placeholder"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="admin-input p-2"
                >
                @include('admin.media.partials.picker', [
                    'mediaPickerField' => 'image',
                    'mediaPickerId' => old('media_id', $category->media_id ?? null),
                    'mediaPickerUrl' => $categoryImageUrl,
                    'mediaPickerName' => $category->name ?? '',
                    'mediaPickerPreviewId' => 'category-image-preview',
                    'mediaPickerRemoveName' => 'remove_image',
                ])

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    تصویر دسته‌بندی در کارت‌های عمومی با نسبت ثابت ۴:۳ نمایش داده می‌شود؛ قاب برش و پیش‌نمایش نیز همین نسبت را حفظ می‌کند.
                </p>
                <label for="image_alt" class="admin-label mt-3">متن جایگزین تصویر (Alt)</label>
                <input id="image_alt" name="image_alt" maxlength="255" class="admin-input" value="{{ old('image_alt', $category->image_alt ?? '') }}" placeholder="شرح دقیق تصویر دسته‌بندی">

                @error('image')
                <p class="mt-2 text-xs text-[var(--admin-danger)]">
                    {{ $message }}
                </p>
                @enderror

            </div>

        </div>


        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    ذخیره
                </h3>
            </div>

            <div class="flex flex-col gap-3">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary w-full"
                >
                    {{ $editing ? 'ذخیره تغییرات' : 'ایجاد دسته‌بندی' }}
                </button>

                <a
                    href="{{ route('admin.categories.index') }}"
                    class="admin-btn admin-btn-secondary w-full"
                >
                    انصراف
                </a>

            </div>

        </div>

    </div>


    <details class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)]">
        <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-[var(--admin-text)]">
            تنظیمات پیشرفته (اختیاری) — سئو
            <span class="mt-1 block text-xs font-normal text-[var(--admin-muted)]">برای ثبت یک دسته‌بندی معمولی نیازی به تغییر این بخش نیست.</span>
        </summary>
        <div class="space-y-4 border-t border-[var(--admin-border)] p-4 sm:p-6">
    <div class="admin-card space-y-4 p-6">
        <h3 class="text-base font-bold text-[var(--admin-text)]">تنظیمات SEO دسته‌بندی</h3>
        <div><label class="admin-label" for="focus_keyword">کلمه کلیدی کانونی</label><input id="focus_keyword" name="focus_keyword" maxlength="120" class="admin-input" value="{{ old('focus_keyword', $category->focus_keyword ?? '') }}" placeholder="مثلاً مبلمان مدرن"></div>
        @if(isset($seoAnalysis))
            <div class="rounded-xl border border-[var(--admin-border)] p-4">
                <div class="flex items-center justify-between gap-3"><strong class="text-xs">ارزیابی محتوای SEO</strong><strong class="text-sm">{{ $seoAnalysis['score'] }} / 100</strong></div>
                <p class="admin-help mt-1">{{ $seoAnalysis['passed'] }} مورد از {{ $seoAnalysis['total'] }} بررسی داخلی موفق است؛ این امتیاز تضمین رتبه نیست.</p>
                <ul class="mt-3 space-y-2">@foreach($seoAnalysis['checks'] as $check)<li class="flex items-start gap-2 text-xs"><span class="{{ $check['passed'] ? 'text-emerald-600' : 'text-amber-600' }}">{{ $check['passed'] ? '✓' : '•' }}</span><span><strong>{{ $check['label'] }}</strong><span class="block text-[var(--admin-muted)]">{{ $check['detail'] }}</span></span></li>@endforeach</ul>
            </div>
        @endif
        <div><label class="admin-label" for="meta_title">Meta Title</label><input id="meta_title" name="meta_title" maxlength="255" class="admin-input" value="{{ old('meta_title', $category->meta_title ?? '') }}">@error('meta_title')<p class="admin-error">{{ $message }}</p>@enderror</div>
        <div><label class="admin-label" for="meta_description">Meta Description</label><textarea id="meta_description" name="meta_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('meta_description', $category->meta_description ?? '') }}</textarea>@error('meta_description')<p class="admin-error">{{ $message }}</p>@enderror</div>
        @include('admin.seo.partials.canonical-picker', ['seoCanonical' => $category->canonical_url ?? ''])
        <div><label class="admin-label" for="og_image">Open Graph Image URL</label><input id="og_image" name="og_image" type="url" dir="ltr" class="admin-input" value="{{ old('og_image', $category->og_image ?? '') }}">@error('og_image')<p class="admin-error">{{ $message }}</p>@enderror</div>
    </div>

    <div class="admin-card p-6">
        <h3 class="text-base font-bold text-[var(--admin-text)]">وضعیت موقت SEO</h3>
        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تا بررسی کارشناس SEO، این دسته‌بندی Noindex/Nofollow می‌ماند.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_index" value="1" class="admin-checkbox" @checked(old('seo_index', $category->seo_index ?? false))><span class="text-sm">اجازه ایندکس این دسته‌بندی</span></label>
            <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_follow" value="1" class="admin-checkbox" @checked(old('seo_follow', $category->seo_follow ?? false))><span class="text-sm">اجازه دنبال‌کردن لینک‌ها</span></label>
        </div>
    </div>


        </div>
    </details>

    <details class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)]">
        <summary class="cursor-pointer list-none px-5 py-4 text-sm font-semibold text-[var(--admin-text)]">تنظیمات تکمیلی دسته‌بندی (اختیاری)<span class="mt-1 block text-xs font-normal text-[var(--admin-muted)]">زیر‌دسته، بنر اختصاصی، متن‌های تکمیلی و محتوای ویژه صفحه.</span></summary>
        <div class="space-y-5 border-t border-[var(--admin-border)] p-4 sm:p-6">
    <div class="admin-card space-y-5 p-6">
        <div><h3 class="text-base font-bold text-[var(--admin-text)]">ساختار دسته‌بندی و تصاویر صفحه</h3><p class="admin-help mt-1">تصویر کارت و بنر صفحه دسته‌بندی مستقل هستند. تصویر قدیمی فقط به‌عنوان fallback حفظ می‌شود.</p></div>
        <div>
            <label class="admin-label" for="parent_id">دسته‌بندی والد (برای زیر‌دسته)</label>
            <select id="parent_id" name="parent_id" class="admin-input">
                <option value="">دسته اصلی</option>
                @foreach(($availableRelatedCategories ?? collect()) as $parentCategory)
                    <option value="{{ $parentCategory->id }}" @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $parentCategory->id)>{{ $parentCategory->name }}</option>
                @endforeach
            </select>
            @error('parent_id')<p class="admin-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-5 lg:grid-cols-2">
            <div class="space-y-3">
                <h4 class="text-sm font-semibold">تصویر کارت</h4>
                <div class="overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)]"><img id="category-card-image-preview" src="{{ $category->card_image_url ?: '' }}" alt="پیش‌نمایش تصویر کارت دسته‌بندی" class="{{ $category->card_image_url ? '' : 'hidden' }} aspect-[4/3] w-full object-cover"><div id="category-card-image-placeholder" class="{{ $category->card_image_url ? 'hidden' : 'flex' }} aspect-[4/3] items-center justify-center text-xs text-[var(--admin-muted)]">پیش‌نمایش تصویر کارت</div></div>
                <input id="card_image" type="file" name="card_image" accept="image/jpeg,image/png,image/webp" data-crop-ratio="1.333333" data-preview-target="category-card-image-preview" data-preview-placeholder="category-card-image-placeholder" class="admin-input">
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="remove_card_image" value="1"> حذف تصویر کارت اختصاصی</label>
                @error('card_image')<p class="admin-error">{{ $message }}</p>@enderror
                <p class="admin-help">نسبت پیشنهادی ۴:۳؛ در صورت خالی بودن، تصویر قدیمی دسته‌بندی استفاده می‌شود.</p>
            </div>
            <div class="space-y-3">
                <h4 class="text-sm font-semibold">بنر صفحه دسته‌بندی</h4>
                <div class="overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)]"><img id="category-banner-image-preview" src="{{ $category->banner_image_url ?: '' }}" alt="پیش‌نمایش بنر دسته‌بندی" class="{{ $category->banner_image_url ? '' : 'hidden' }} aspect-[16/7] w-full object-cover"><div id="category-banner-image-placeholder" class="{{ $category->banner_image_url ? 'hidden' : 'flex' }} aspect-[16/7] items-center justify-center text-xs text-[var(--admin-muted)]">پیش‌نمایش بنر دسته‌بندی</div></div>
                <input id="banner_image" type="file" name="banner_image" accept="image/jpeg,image/png,image/webp" data-crop-ratio="2.285714" data-preview-target="category-banner-image-preview" data-preview-placeholder="category-banner-image-placeholder" class="admin-input">
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" name="remove_banner_image" value="1"> حذف بنر اختصاصی</label>
                @error('banner_image')<p class="admin-error">{{ $message }}</p>@enderror
                <p class="admin-help">نسبت پیشنهادی ۱۶:۷؛ اگر بنر اختصاصی ثبت نشود، تصویر کارت fallback می‌شود.</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="admin-label">عنوان بنر</span><input name="banner_title" maxlength="255" class="admin-input" value="{{ old('banner_title', $category->banner_title ?? '') }}">@error('banner_title')<p class="admin-error">{{ $message }}</p>@enderror</label>
            <label class="sm:col-span-2"><span class="admin-label">زیرعنوان بنر</span><textarea name="banner_subtitle" rows="3" maxlength="2000" class="admin-textarea">{{ old('banner_subtitle', $category->banner_subtitle ?? '') }}</textarea>@error('banner_subtitle')<p class="admin-error">{{ $message }}</p>@enderror</label>
        </div>
    </div>

    @include('admin.partials.content-builder', [
        'sections' => $category->content_sections ?? [],
        'comparisonRows' => $category->comparison_rows ?? [],
        'faqItems' => $category->faq_items ?? [],
        'availableRelatedCategories' => $availableRelatedCategories ?? collect(),
        'availableRelatedProducts' => $availableRelatedProducts ?? collect(),
        'selectedRelatedCategoryIds' => $selectedRelatedCategoryIds ?? [],
        'selectedRelatedProductIds' => $selectedRelatedProductIds ?? [],
        'includeComparison' => true,
        'includeRelatedCategories' => true,
        'includeRelatedProducts' => true,
    ])

        </div>
    </details>
</div>


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const imageInput =
            document.getElementById('image');

        const imagePreview =
            document.getElementById('category-image-preview');

        const imagePlaceholder =
            document.getElementById('category-image-placeholder');

        if (!imageInput || !imagePreview) {
            return;
        }

        imageInput.addEventListener('change', function () {

            const file =
                imageInput.files?.[0];

            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            const objectUrl =
                URL.createObjectURL(file);

            imagePreview.src =
                objectUrl;

            imagePreview.classList.remove('hidden');

            if (imagePlaceholder) {
                imagePlaceholder.classList.add('hidden');
                imagePlaceholder.classList.remove('flex');
            }

            imagePreview.onload =
                function () {
                    URL.revokeObjectURL(objectUrl);
                };

        });

    });
</script>
@endpush
