@php
    $editing = isset($product);

    $installmentEnabled = (bool) old(
        'installment_enabled',
        $product->installment_enabled ?? false
    );

    $cashPercent = old(
        'installment_cash_percent',
        $product->installment_cash_percent ?? 50
    );

    $remainderMethod = old(
        'installment_remainder_method',
        $product->installment_remainder_method ?? 'cheque'
    );

    $chequeCount = old(
        'installment_cheque_count',
        $product->installment_cheque_count ?? 2
    );

    $intervalMonths = old(
        'installment_interval_months',
        $product->installment_interval_months ?? 2
    );
@endphp

@csrf

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

    {{-- Main Information --}}
    <div class="space-y-6 xl:col-span-2">

        {{-- Product Information --}}
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

        {{-- Installment --}}
        <div class="admin-card p-6">

            <div class="mb-6">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <h3 class="text-base font-bold text-[var(--admin-text)]">
                            فروش اقساطی
                        </h3>

                        <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                            شرایط فروش اقساطی این محصول را مشخص کنید.
                            محاسبه مبلغ نقدی و چک‌ها به‌صورت خودکار انجام می‌شود.
                        </p>
                    </div>

                    <span
                        id="installment_status_badge"
                        class="rounded-full border border-[var(--admin-border)] px-3 py-1 text-[11px] font-medium text-[var(--admin-muted)]"
                    >
                        {{ $installmentEnabled ? 'فعال' : 'غیرفعال' }}
                    </span>

                </div>

            </div>

            <div class="space-y-5">

                {{-- Enabled --}}
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">

                    <input
                        type="hidden"
                        name="installment_enabled"
                        value="0"
                    >

                    <input
                        id="installment_enabled"
                        type="checkbox"
                        name="installment_enabled"
                        value="1"
                        class="admin-checkbox mt-1 h-4 w-4"
                        @checked($installmentEnabled)
                    >

                    <span>
                        <span class="block text-sm font-semibold text-[var(--admin-text)]">
                            فعال‌سازی فروش اقساطی
                        </span>

                        <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">
                            مشتری می‌تواند این محصول را طبق شرایط تعریف‌شده به‌صورت اقساطی خریداری کند.
                        </span>
                    </span>

                </label>

                {{-- Installment Settings --}}
                <div
                    id="installment_settings"
                    class="{{ $installmentEnabled ? '' : 'hidden' }} space-y-5"
                >

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">

                        {{-- Cash Percent --}}
                        <div>

                            <label for="installment_cash_percent" class="admin-label">
                                درصد پیش‌پرداخت
                            </label>

                            <div class="relative">

                                <input
                                    id="installment_cash_percent"
                                    name="installment_cash_percent"
                                    type="number"
                                    min="1"
                                    max="99"
                                    step="1"
                                    value="{{ $cashPercent }}"
                                    class="admin-input pr-12"
                                >

                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]">
                                    %
                                </span>

                            </div>

                            @error('installment_cash_percent')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                        {{-- Remainder Method --}}
                        <div>

                            <label for="installment_remainder_method" class="admin-label">
                                روش تسویه باقی‌مانده
                            </label>

                            <select
                                id="installment_remainder_method"
                                name="installment_remainder_method"
                                class="admin-select"
                            >
                                <option
                                    value="cheque"
                                    @selected($remainderMethod === 'cheque')
                                >
                                چک
                                </option>
                            </select>

                            @error('installment_remainder_method')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                        {{-- Cheque Count --}}
                        <div>

                            <label for="installment_cheque_count" class="admin-label">
                                تعداد چک
                            </label>

                            <input
                                id="installment_cheque_count"
                                name="installment_cheque_count"
                                type="number"
                                min="1"
                                max="30"
                                step="1"
                                value="{{ $chequeCount }}"
                                class="admin-input"
                            >

                            @error('installment_cheque_count')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                        {{-- Interval --}}
                        <div>

                            <label for="installment_interval_months" class="admin-label">
                                فاصله سررسید
                            </label>

                            <div class="relative">

                                <input
                                    id="installment_interval_months"
                                    name="installment_interval_months"
                                    type="number"
                                    min="1"
                                    max="24"
                                    step="1"
                                    value="{{ $intervalMonths }}"
                                    class="admin-input pl-16"
                                >

                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-[var(--admin-muted)]">
                                    ماه
                                </span>

                            </div>

                            @error('installment_interval_months')
                            <p class="mt-2 text-xs text-[var(--admin-danger)]">
                                {{ $message }}
                            </p>
                            @enderror

                        </div>

                    </div>

                    {{-- Live Preview --}}
                    <div
                        id="installment_preview"
                        class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5"
                    >
                        <div class="mb-4 flex items-center justify-between">

                            <div>
                                <p class="text-sm font-semibold text-[var(--admin-text)]">
                                    پیش‌نمایش شرایط اقساط
                                </p>

                                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                    مبالغ بر اساس قیمت محصول محاسبه می‌شوند.
                                </p>
                            </div>

                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                            <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4">
                                <p class="text-xs text-[var(--admin-muted)]">
                                    قیمت محصول
                                </p>

                                <p
                                    id="installment_total_preview"
                                    class="mt-2 text-sm font-bold text-[var(--admin-text)]"
                                >
                                    ۰ تومان
                                </p>
                            </div>

                            <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4">
                                <p class="text-xs text-[var(--admin-muted)]">
                                    پیش‌پرداخت
                                </p>

                                <p
                                    id="installment_cash_preview"
                                    class="mt-2 text-sm font-bold text-[var(--admin-text)]"
                                >
                                    ۰ تومان
                                </p>
                            </div>

                            <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-4">
                                <p class="text-xs text-[var(--admin-muted)]">
                                    باقی‌مانده
                                </p>

                                <p
                                    id="installment_deferred_preview"
                                    class="mt-2 text-sm font-bold text-[var(--admin-text)]"
                                >
                                    ۰ تومان
                                </p>
                            </div>

                        </div>

                        <div
                            id="installment_cheques_preview"
                            class="mt-4 space-y-3"
                        ></div>

                    </div>

                </div>

            </div>

        </div>

        {{-- SEO --}}
        {{-- SEO --}}
        <div class="admin-card p-6">

            <div class="mb-6">
                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    بهینه‌سازی موتور جستجو
                </h3>

                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">
                    عنوان و توضیحات صفحه محصول را برای نتایج جستجو تنظیم کنید.
                </p>
            </div>

            <div class="space-y-6">

                {{-- Meta Title --}}
                <div>

                    <div class="mb-2 flex items-center justify-between gap-3">

                        <label
                            for="meta_title"
                            class="admin-label mb-0"
                        >
                            Meta Title
                        </label>

                        <span
                            id="meta_title_counter"
                            class="text-[11px] text-[var(--admin-muted)]"
                        >
                    0 / 255
                </span>

                    </div>

                    <input
                        id="meta_title"
                        name="meta_title"
                        type="text"
                        maxlength="255"
                        value="{{ old('meta_title', $product->meta_title ?? '') }}"
                        class="admin-input"
                        placeholder="مثلاً خرید مبل راحتی مدل Milano | LIVORA"
                    >

                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                        عنوانی که در تب مرورگر و نتایج جستجو نمایش داده می‌شود.
                        در صورت خالی بودن، عنوان محصول استفاده می‌شود.
                    </p>

                    @error('meta_title')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

                {{-- Meta Description --}}
                <div>

                    <div class="mb-2 flex items-center justify-between gap-3">

                        <label
                            for="meta_description"
                            class="admin-label mb-0"
                        >
                            Meta Description
                        </label>

                        <span
                            id="meta_description_counter"
                            class="text-[11px] text-[var(--admin-muted)]"
                        >
                    0
                </span>

                    </div>

                    <textarea
                        id="meta_description"
                        name="meta_description"
                        rows="5"
                        class="admin-textarea"
                        placeholder="توضیح کوتاه و جذاب درباره محصول برای موتورهای جستجو..."
                    >{{ old('meta_description', $product->meta_description ?? '') }}</textarea>

                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                        توضیح مختصر و واقعی درباره محصول، ویژگی‌ها و کاربرد آن بنویسید.
                    </p>

                    @error('meta_description')
                    <p class="mt-2 text-xs text-[var(--admin-danger)]">
                        {{ $message }}
                    </p>
                    @enderror

                </div>

                {{-- Google-like Preview --}}
                <div class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5">

                    <div class="mb-4">
                        <p class="text-sm font-semibold text-[var(--admin-text)]">
                            پیش‌نمایش نتیجه جستجو
                        </p>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            یک Preview تقریبی برای بررسی عنوان و توضیحات صفحه.
                        </p>
                    </div>

                    <div class="rounded-xl bg-[var(--admin-surface)] p-4">

                        <p
                            id="seo_preview_title"
                            class="text-base font-medium text-[#1a0dab]"
                        >
                            {{ old('meta_title', $product->meta_title ?? '') ?: 'عنوان محصول شما' }}
                        </p>

                        <p
                            id="seo_preview_url"
                            class="mt-1 text-xs text-emerald-700"
                        >
                            {{ isset($product) && $product->slug
                                ? url('/product/' . $product->slug)
                                : url('/product/example') }}
                        </p>

                        <p
                            id="seo_preview_description"
                            class="mt-2 text-sm leading-7 text-[var(--admin-muted)]"
                        >
                            {{ old('meta_description', $product->meta_description ?? '') ?: 'توضیحات متا محصول شما اینجا نمایش داده می‌شود.' }}
                        </p>

                    </div>

                </div>

            </div>

        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {

                    const titleInput =
                        document.getElementById('meta_title');

                    const descriptionInput =
                        document.getElementById('meta_description');

                    const titleCounter =
                        document.getElementById('meta_title_counter');

                    const descriptionCounter =
                        document.getElementById('meta_description_counter');

                    const previewTitle =
                        document.getElementById('seo_preview_title');

                    const previewDescription =
                        document.getElementById('seo_preview_description');

                    const nameInput =
                        document.getElementById('name');

                    function updateSeoPreview() {

                        const title =
                            titleInput.value.trim();

                        const description =
                            descriptionInput.value.trim();

                        titleCounter.textContent =
                            `${title.length} / 255`;

                        descriptionCounter.textContent =
                            `${description.length} کاراکتر`;

                        previewTitle.textContent =
                            title
                            || nameInput.value.trim()
                            || 'عنوان محصول شما';

                        previewDescription.textContent =
                            description
                            || 'توضیحات متا محصول شما اینجا نمایش داده می‌شود.';
                    }

                    titleInput.addEventListener(
                        'input',
                        updateSeoPreview
                    );

                    descriptionInput.addEventListener(
                        'input',
                        updateSeoPreview
                    );

                    nameInput.addEventListener(
                        'input',
                        updateSeoPreview
                    );

                    updateSeoPreview();
                });
            </script>
        @endpush
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const enabledInput = document.getElementById('installment_enabled');
            const settings = document.getElementById('installment_settings');
            const statusBadge = document.getElementById('installment_status_badge');

            const priceInput = document.getElementById('price');
            const cashPercentInput = document.getElementById('installment_cash_percent');
            const chequeCountInput = document.getElementById('installment_cheque_count');
            const intervalInput = document.getElementById('installment_interval_months');

            const totalPreview = document.getElementById('installment_total_preview');
            const cashPreview = document.getElementById('installment_cash_preview');
            const deferredPreview = document.getElementById('installment_deferred_preview');
            const chequesPreview = document.getElementById('installment_cheques_preview');

            const formatter = new Intl.NumberFormat('fa-IR');

            function formatMoney(value) {
                return `${formatter.format(Math.round(value))} تومان`;
            }

            function updateStatus() {
                const enabled = enabledInput.checked;

                settings.classList.toggle('hidden', !enabled);

                statusBadge.textContent = enabled
                    ? 'فعال'
                    : 'غیرفعال';

                if (enabled) {
                    updatePreview();
                }
            }

            function updatePreview() {
                if (!enabledInput.checked) {
                    return;
                }

                const total = Number(priceInput.value || 0);
                const cashPercent = Number(cashPercentInput.value || 0);
                const chequeCount = Math.max(
                    1,
                    Number(chequeCountInput.value || 1)
                );
                const intervalMonths = Math.max(
                    1,
                    Number(intervalInput.value || 1)
                );

                if (total <= 0 || cashPercent <= 0) {
                    totalPreview.textContent = formatMoney(0);
                    cashPreview.textContent = formatMoney(0);
                    deferredPreview.textContent = formatMoney(0);
                    chequesPreview.innerHTML = '';
                    return;
                }

                const cashAmount = Math.round(
                    total * (cashPercent / 100)
                );

                const deferredAmount = Math.max(
                    0,
                    total - cashAmount
                );

                totalPreview.textContent =
                    formatMoney(total);

                cashPreview.textContent =
                    formatMoney(cashAmount);

                deferredPreview.textContent =
                    formatMoney(deferredAmount);

                const baseAmount = Math.floor(
                    deferredAmount / chequeCount
                );

                let distributed = 0;

                const rows = [];

                for (let index = 1; index <= chequeCount; index++) {
                    let amount;

                    if (index === chequeCount) {
                        amount =
                            deferredAmount - distributed;
                    } else {
                        amount = baseAmount;
                    }

                    distributed += amount;

                    const months =
                        intervalMonths * index;

                    rows.push(`
                <div class="flex items-center justify-between rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-3">
                    <div>
                        <p class="text-xs text-[var(--admin-muted)]">
                            چک ${formatter.format(index)}
                        </p>
                        <p class="mt-1 text-sm font-semibold text-[var(--admin-text)]">
                            ${formatMoney(amount)}
                        </p>
                    </div>

                    <span class="rounded-full border border-[var(--admin-border)] px-3 py-1 text-xs text-[var(--admin-muted)]">
                        ${formatter.format(months)} ماه بعد
                    </span>
                </div>
            `);
                }

                chequesPreview.innerHTML = rows.join('');
            }

            enabledInput.addEventListener(
                'change',
                updateStatus
            );

            [
                priceInput,
                cashPercentInput,
                chequeCountInput,
                intervalInput
            ].forEach(function (input) {
                input.addEventListener(
                    'input',
                    updatePreview
                );
            });

            updateStatus();
        });
    </script>
@endpush
