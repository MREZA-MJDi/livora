@php
    $editing = isset($productImage);

    $resolvedSelectedProduct =
        $selectedProduct
        ?? ($productImage?->product ?? null);

@endphp

@csrf

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    <div class="space-y-6 xl:col-span-2">

        {{-- Product --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                    PRODUCT LINK
                </p>

                <h3 class="mt-2 text-base font-bold text-[var(--admin-text)]">
                    اتصال تصویر به محصول
                </h3>

                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                    نام محصول یا SKU را جستجو کنید؛ پنل هیچ‌وقت کل کاتالوگ را یکجا لود نمی‌کند.
                </p>
            </div>


            @include('admin.partials.product-selector', [
                'inputName' => 'product_id',
                'fieldId' => 'product-image-product',
                'selectedProduct' => $resolvedSelectedProduct,
                'label' => 'محصول',
                'placeholder' => 'حداقل ۲ حرف از نام محصول یا SKU...',
                'required' => true,
            ])

            @error('product_id')
                <p class="mt-2 text-xs text-[var(--admin-danger)]">
                    {{ $message }}
                </p>
            @enderror

        </div>


        {{-- Image --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                    IMAGE ASSET
                </p>

                <h3 class="mt-2 text-base font-bold text-[var(--admin-text)]">
                    تصویر
                </h3>

                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                    تصویر اصلی یا گالری محصول را با فرمت وب مناسب آپلود کنید.
                </p>
            </div>


            @if($editing && $productImage->url)

                <div class="admin-image-preview mb-5 aspect-video max-w-xl">

                    <img
                        src="{{ $productImage->url }}"
                        alt="{{ $productImage->alt ?: 'تصویر محصول' }}"
                        class="admin-image"
                    >

                </div>

            @endif


            <label for="image" class="admin-label">
                {{ $editing ? 'تصویر جدید' : 'تصویر محصول' }}
            </label>

            <input
                id="image"
                name="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="admin-input p-2"
                {{ $editing ? '' : 'required' }}
            >

            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                JPG، JPEG، PNG، WEBP — حداکثر ۵ مگابایت.
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
                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                    IMAGE METADATA
                </p>

                <h3 class="mt-2 text-base font-bold text-[var(--admin-text)]">
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
                        placeholder="مثلاً نمای روبه‌روی مبل آریا"
                    >

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        برای SEO و دسترسی‌پذیری استفاده می‌شود.
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
                        max="9999"
                        step="1"
                        inputmode="numeric"
                        value="{{ old('sort_order', $productImage->sort_order ?? 0) }}"
                        class="admin-input"
                        placeholder="0"
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
                                این تصویر در کارت و صفحه لیست محصول استفاده می‌شود.
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

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                ASSET RULES
            </p>

            <h3 class="mt-2 text-base font-bold text-[var(--admin-text)]">
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
                        5 MB
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
