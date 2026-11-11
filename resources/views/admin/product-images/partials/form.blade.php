@php
    $editing = isset($productImage) && $productImage->exists;
@endphp

@csrf

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    <div class="space-y-6 xl:col-span-2">

        {{-- Product --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    محصول
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    محصولی که این تصویر به آن تعلق دارد را انتخاب کنید.
                </p>
            </div>

            <label for="product_id" class="admin-label">
                محصول
            </label>

            <div x-data="adminCatalogueLookup(@js(route('admin.catalogue.lookups.products')), @js($selectedProduct ? [['id' => $selectedProduct->id, 'label' => $selectedProduct->name, 'meta' => $selectedProduct->sku]] : []), false)" class="space-y-2">
                <input type="hidden" name="product_id" :value="selected[0]?.id || ''">
                <input type="search" class="admin-input" x-model="term" x-on:input.debounce.300ms="search()" minlength="2" maxlength="80" autocomplete="off" placeholder="نام محصول یا SKU را جستجو کنید">
                <div x-show="results.length" x-cloak class="max-h-56 space-y-1 overflow-y-auto rounded-xl border border-[var(--admin-border)] bg-white p-2" role="listbox" aria-label="نتایج محصولات">
                    <template x-for="item in results" :key="item.id">
                        <button type="button" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-right text-sm hover:bg-[var(--admin-surface)]" @click="choose(item)" role="option"><span x-text="item.label"></span><small class="text-[var(--admin-muted)]" dir="ltr" x-text="item.meta || ''"></small></button>
                    </template>
                </div>
                <template x-for="item in selected" :key="item.id">
                    <div class="flex items-center justify-between rounded-lg border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3 py-2 text-sm"><span x-text="item.label"></span><button type="button" class="text-red-600" @click="remove(item.id)" aria-label="حذف محصول انتخاب‌شده">×</button></div>
                </template>
                <p class="admin-help" role="status" aria-live="polite" x-text="status"></p>
            </div>

            @error('product_id')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>


        {{-- Image --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    تصویر
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    تصویر محصول را انتخاب یا در حالت ویرایش جایگزین کنید.
                </p>
            </div>


            <label for="image" class="admin-label">
                {{ $editing ? 'تصویر جدید' : 'تصویر محصول' }}
            </label>

            <div class="admin-image-preview mb-5 flex min-h-64 items-center justify-center p-3">
                <img id="product-gallery-image-preview" @if(filled($productImage->url ?? null)) src="{{ $productImage->url }}" @endif alt="{{ $productImage->alt ?? 'تصویر محصول' }}" class="h-auto max-h-[32rem] w-auto max-w-full object-contain {{ filled($productImage->url ?? null) ? '' : 'hidden' }}">
                <div id="product-gallery-image-placeholder" class="{{ filled($productImage->url ?? null) ? 'hidden' : 'flex' }} h-full items-center justify-center rounded-xl border border-dashed border-[var(--admin-border)] text-xs text-[var(--admin-muted)]">تصویر گالری هنوز انتخاب نشده است</div>
            </div>
            <input
                id="image"
                name="image" data-preserve-original="1" data-preview-target="product-gallery-image-preview" data-preview-placeholder="product-gallery-image-placeholder"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="admin-input p-2"
                {{ $editing ? '' : '' }}
            >
            @include('admin.media.partials.picker', ['mediaPickerField' => 'image', 'mediaPickerId' => old('media_id', $productImage->media_id ?? null), 'mediaPickerUrl' => $productImage->url ?? null, 'mediaPickerName' => $productImage->alt ?? '', 'mediaPickerPreviewId' => 'product-gallery-image-preview'])

            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                فرمت‌های مجاز: JPG، JPEG، PNG، WEBP — حداکثر ۱۰ مگابایت.
            </p>

            @error('image')
            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                {{ $message }}
            </p>
            @enderror

        </div>


        {{-- Metadata --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات تصویر
                </h3>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                <div class="sm:col-span-2">

                    <label for="alt" class="admin-label">
                        متن جایگزین (Alt)
                    </label>

                    <input
                        id="alt"
                        name="alt"
                        type="text"
                        maxlength="255"
                        value="{{ old('alt', $productImage->alt ?? '') }}"
                        class="admin-input"
                        placeholder="مثلاً نمای روبه‌روی مبل SillaGallery"
                    >

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        برای SEO و دسترسی‌پذیری تصویر استفاده می‌شود.
                    </p>

                    @error('alt')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div>

                    <label for="sort_order" class="admin-label">
                        ترتیب نمایش
                    </label>

                    <input
                        id="sort_order"
                        name="sort_order"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('sort_order', $productImage->sort_order ?? 0) }}"
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
                            name="is_primary"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="is_primary"
                            value="1"
                            class="admin-checkbox h-4 w-4"
                            @checked(old('is_primary', $productImage->is_primary ?? false))
                        >

                        <span>
                            <span class="block text-sm font-semibold text-[var(--admin-text)]">
                                تصویر اصلی
                            </span>

                            <span class="mt-1 block text-xs text-[var(--admin-muted)]">
                                به‌عنوان تصویر اصلی محصول استفاده شود.
                            </span>
                        </span>

                    </label>

                </div>

            </div>

        </div>

    </div>


    {{-- Side Actions --}}
    <div class="space-y-6">

        <div class="admin-card p-6">

            <h3 class="text-base font-bold text-[var(--admin-text)]">
                خلاصه
            </h3>

            <div class="mt-5 space-y-4">

                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-[var(--admin-muted)]">
                        وضعیت
                    </span>

                    <span class="admin-badge admin-badge-info">
                        {{ $editing ? 'ویرایش' : 'جدید' }}
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-[var(--admin-muted)]">
                        حداکثر حجم
                    </span>

                    <span class="text-xs font-semibold text-[var(--admin-text-soft)]">
                        10 MB
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs text-[var(--admin-muted)]">
                        فرمت
                    </span>

                    <span class="text-left text-xs font-semibold text-[var(--admin-text-soft)]">
                        JPG / PNG / WEBP
                    </span>
                </div>

            </div>

        </div>


        <div class="admin-card p-6">

            <div class="flex flex-col gap-3">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary w-full"
                >
                    {{ $editing ? 'ذخیره تغییرات' : 'افزودن تصویر' }}
                </button>

                <a
                    href="{{ route('admin.product-images.index') }}"
                    class="admin-btn admin-btn-secondary w-full"
                >
                    انصراف
                </a>

            </div>

        </div>

    </div>

</div>
