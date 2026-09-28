@php
    $editing = isset($productImage);
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

            <select
                id="product_id"
                name="product_id"
                class="admin-select"
                required
            >
                <option value="">
                    انتخاب محصول
                </option>

                @foreach($products as $product)

                    <option
                        value="{{ $product->id }}"
                        @selected(
                        old(
                    'product_id',
                    $productImage->product_id ?? $selectedProductId ?? ''
                    ) == $product->id
                    )
                    >
                    {{ $product->name }}
                    </option>

                @endforeach

            </select>

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


            <div class="admin-image-preview mb-5 aspect-[4/5] w-full max-w-[380px] overflow-hidden rounded-2xl">

                <img
                    id="product-image-preview"
                    src="{{ $editing && $productImage->url ? $productImage->url : '' }}"
                    alt="{{ $productImage->alt ?: 'Product image' }}"
                    class="admin-image {{ $editing && $productImage->url ? '' : 'hidden' }}"
                >

                <div
                    id="product-image-placeholder"
                    class="{{ $editing && $productImage->url ? 'hidden' : 'flex' }} h-full w-full flex-col items-center justify-center rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-6 text-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor"
                         class="mb-3 h-9 w-9 text-[var(--admin-muted)]">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 1 21.75 18V6A1.5 1.5 0 0 1 20.25 4.5H3.75A1.5 1.5 0 0 1 2.25 6v12A1.5 1.5 0 0 1 3.75 19.5Z" />
                    </svg>

                    <span class="text-xs text-[var(--admin-muted)]">
                        {{ $editing ? 'تصویر جدیدی انتخاب نشده است.' : 'هنوز تصویری انتخاب نشده است.' }}
                    </span>
                </div>

            </div>


            <label for="image" class="admin-label">
                {{ $editing ? 'تصویر جدید' : 'تصویر محصول' }}
            </label>

            <input
                id="image"
                name="image"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                data-image-preview-target="product-image-preview"
                data-image-preview-placeholder="product-image-placeholder"
                class="admin-input"
                {{ $editing ? '' : 'required' }}
            >

            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                فرمت‌های مجاز: JPG، JPEG، PNG، WEBP — حداکثر ۵ مگابایت.
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
                        placeholder="مثلاً نمای روبه‌روی مبل SilaGallery"
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
