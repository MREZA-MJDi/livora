@php
    $editing = isset($product);
@endphp

@csrf

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    {{-- Main Information --}}
    <div class="space-y-6 xl:col-span-2">

        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات محصول
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    اطلاعات اصلی محصول را وارد کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                {{-- Category --}}
                <div class="sm:col-span-2">

                    <label for="category_id" class="admin-label">
                        دسته‌بندی
                    </label>

                    <select
                        id="category_id"
                        name="category_id"
                        class="admin-select"
                        required
                    >
                        <option value="">
                            انتخاب دسته‌بندی
                        </option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category->id }}"
                                @selected(old('category_id', $product->category_id ?? '') == $category->id)
                            >
                            {{ $category->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('category_id')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Name --}}
                <div class="sm:col-span-2">

                    <label for="name" class="admin-label">
                        نام محصول
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $product->name ?? '') }}"
                        class="admin-input"
                        required
                    >

                    @error('name')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Slug --}}
                <div>

                    <label for="slug" class="admin-label">
                        Slug
                    </label>

                    <input
                        id="slug"
                        name="slug"
                        type="text"
                        value="{{ old('slug', $product->slug ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                        placeholder="product-slug"
                    >

                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                        در صورت خالی بودن، از نام محصول ساخته می‌شود.
                    </p>

                    @error('slug')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- SKU --}}
                <div>

                    <label for="sku" class="admin-label">
                        SKU
                    </label>

                    <input
                        id="sku"
                        name="sku"
                        type="text"
                        value="{{ old('sku', $product->sku ?? '') }}"
                        dir="ltr"
                        class="admin-input text-left"
                        required
                    >

                    @error('sku')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Short Description --}}
                <div class="sm:col-span-2">

                    <label for="short_description" class="admin-label">
                        توضیح کوتاه
                    </label>

                    <textarea
                        id="short_description"
                        name="short_description"
                        rows="3"
                        maxlength="255"
                        class="admin-textarea"
                    >{{ old('short_description', $product->short_description ?? '') }}</textarea>

                    @error('short_description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Description --}}
                <div class="sm:col-span-2">

                    <label for="description" class="admin-label">
                        توضیحات کامل
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="9"
                        class="admin-textarea"
                    >{{ old('description', $product->description ?? '') }}</textarea>

                    @error('description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>


        {{-- Pricing --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    قیمت و موجودی
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    قیمت فروش، قیمت قبل و موجودی محصول را مشخص کنید.
                </p>
            </div>


            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                {{-- Price --}}
                <div>

                    <label for="price" class="admin-label">
                        قیمت فروش
                    </label>

                    <div class="relative">

                        <input
                            id="price"
                            name="price"
                            type="number"
                            min="0"
                            step="0.01"
                            value="{{ old('price', $product->price ?? '') }}"
                            class="admin-input pl-16"
                            required
                        >

                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]">
                            تومان
                        </span>

                    </div>

                    @error('price')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Compare Price --}}
                <div>

                    <label for="compare_at_price" class="admin-label">
                        قیمت قبل
                    </label>

                    <div class="relative">

                        <input
                            id="compare_at_price"
                            name="compare_at_price"
                            type="number"
                            min="0"
                            step="0.01"
                            value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}"
                            class="admin-input pl-16"
                        >

                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]">
                            تومان
                        </span>

                    </div>

                    @error('compare_at_price')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                {{-- Stock --}}
                <div>

                    <label for="stock" class="admin-label">
                        موجودی
                    </label>

                    <input
                        id="stock"
                        name="stock"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ old('stock', $product->stock ?? 0) }}"
                        class="admin-input"
                    >

                    @error('stock')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>


        {{-- SEO --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    SEO
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    اطلاعات سئو محصول را در صورت نیاز وارد کنید.
                </p>
            </div>


            <div class="space-y-5">

                <div>

                    <label for="meta_title" class="admin-label">
                        Meta Title
                    </label>

                    <input
                        id="meta_title"
                        name="meta_title"
                        type="text"
                        maxlength="255"
                        value="{{ old('meta_title', $product->meta_title ?? '') }}"
                        class="admin-input"
                    >

                    @error('meta_title')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>


                <div>

                    <label for="meta_description" class="admin-label">
                        Meta Description
                    </label>

                    <textarea
                        id="meta_description"
                        name="meta_description"
                        rows="5"
                        class="admin-textarea"
                    >{{ old('meta_description', $product->meta_description ?? '') }}</textarea>

                    @error('meta_description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

            </div>

        </div>

    </div>


    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Publish --}}
        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    انتشار
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    وضعیت نمایش محصول را تعیین کنید.
                </p>
            </div>


            <div>

                <label for="status" class="admin-label">
                    وضعیت
                </label>

                <select
                    id="status"
                    name="status"
                    class="admin-select"
                    required
                >
                    <option
                        value="draft"
                        @selected(old('status', $product->status ?? 'draft') === 'draft')
                    >
                    پیش‌نویس
                    </option>

                    <option
                        value="active"
                        @selected(old('status', $product->status ?? '') === 'active')
                    >
                    فعال
                    </option>

                    <option
                        value="archived"
                        @selected(old('status', $product->status ?? '') === 'archived')
                    >
                    آرشیو
                    </option>
                </select>

                @error('status')
                <p class="mt-2 text-xs text-[var(--admin-danger)]">
                    {{ $message }}
                </p>
                @enderror

            </div>

        </div>


        {{-- Flags --}}
        <div class="admin-card p-6">

            <div class="mb-5">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    ویژگی‌های محصول
                </h3>
            </div>


            <div class="space-y-3">

                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">

                    <input
                        type="hidden"
                        name="is_featured"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="is_featured"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked(old('is_featured', $product->is_featured ?? false))
                    >

                    <span>
                        <span class="block text-sm font-semibold text-[var(--admin-text)]">
                            محصول ویژه
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            محصول در بخش محصولات ویژه قرار می‌گیرد.
                        </span>
                    </span>

                </label>


                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">

                    <input
                        type="hidden"
                        name="is_new"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="is_new"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked(old('is_new', $product->is_new ?? false))
                    >

                    <span>
                        <span class="block text-sm font-semibold text-[var(--admin-text)]">
                            محصول جدید
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            برچسب «جدید» روی محصول نمایش داده می‌شود.
                        </span>
                    </span>

                </label>

            </div>

        </div>


        {{-- Save --}}
        <div class="admin-card p-6">

            <div class="flex flex-col gap-3">

                <button
                    type="submit"
                    class="admin-btn admin-btn-primary w-full"
                >
                    {{ $editing ? 'ذخیره تغییرات' : 'ایجاد محصول' }}
                </button>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-btn admin-btn-secondary w-full"
                >
                    انصراف
                </a>

            </div>

        </div>

    </div>

</div>
