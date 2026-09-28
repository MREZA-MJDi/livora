@php
    $editing = isset($productImage);

    $resolvedSelectedProduct =
        $selectedProduct
        ?? ($productImage?->product ?? null);

    $selectedProductId =
        old(
            'product_id',
            $resolvedSelectedProduct?->id ?? ''
        );

    $selectedProductLabel =
        $resolvedSelectedProduct?->name
        ?? ($selectedProductId ? 'محصول شماره ' . $selectedProductId : '');
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


            <div
                data-admin-product-selector
                class="relative"
                data-search-url="{{ route('admin.product-images.product-options') }}"
                data-selected-id="{{ $selectedProductId }}"
                data-selected-label="{{ $selectedProductLabel }}"
            >

                <label
                    for="product-search"
                    class="admin-label"
                >
                    محصول
                </label>

                <div class="relative">

                    <div class="admin-search">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.6"
                            stroke="currentColor"
                            class="admin-search-icon h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                            />
                        </svg>

                        <input
                            id="product-search"
                            type="search"
                            class="admin-search-input"
                            value="{{ $selectedProductLabel }}"
                            placeholder="حداقل ۲ حرف از نام محصول یا SKU..."
                            autocomplete="off"
                            role="combobox"
                            aria-expanded="false"
                            aria-controls="admin-product-options"
                        >

                    </div>


                    <input
                        type="hidden"
                        id="product_id"
                        name="product_id"
                        value="{{ $selectedProductId }}"
                        data-product-value
                        required
                    >


                    <div
                        id="admin-product-options"
                        data-product-results
                        class="absolute inset-x-0 top-full z-40 mt-2 hidden max-h-72 overflow-y-auto rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-2 shadow-[var(--admin-shadow-lg)]"
                    ></div>

                </div>


                <div
                    data-product-selected
                    class="mt-3 {{ $selectedProductId ? '' : 'hidden' }}"
                >
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-3">

                        <div class="min-w-0">

                            <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--admin-muted)]">
                                PRODUCT
                            </p>

                            <p
                                data-product-selected-label
                                class="mt-1 truncate text-sm font-semibold text-[var(--admin-text)]"
                            >
                                {{ $selectedProductLabel }}
                            </p>

                        </div>

                        <button
                            type="button"
                            data-product-clear
                            class="admin-btn admin-btn-ghost px-3"
                        >
                            تغییر
                        </button>

                    </div>
                </div>


                <p
                    data-product-status
                    class="admin-help"
                >
                    برای جستجو حداقل ۲ حرف وارد کنید.
                </p>

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
