{{-- resources/views/admin/products/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'محصولات')
@section('page_title', 'محصولات')

@section('content')

    @php
        $activeFilters = collect([
            request('search'),
            request('category'),
            request('status'),
            request('stock'),
            request('feature'),
        ])->filter(fn ($value) => filled($value))->count();

        $statusLabels = [
            'active' => 'فعال',
            'draft' => 'پیش‌نویس',
            'archived' => 'آرشیو',
        ];
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">

            <div>
                <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--admin-accent)]">
                    CATALOG / PRODUCTS
                </p>

                <h1 class="admin-title mt-2">
                    محصولات
                </h1>

                <p class="admin-subtitle mt-2">
                    کنترل کاتالوگ، قیمت، موجودی، وضعیت انتشار و ویژگی‌های فروش
                </p>
            </div>

            <a
                href="{{ route('admin.products.create') }}"
                class="admin-btn admin-btn-primary"
            >
                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="1.7"
                     stroke="currentColor"
                     class="h-4 w-4">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 5.25v13.5M5.25 12h13.5"
                    />
                </svg>

                افزودن محصول
            </a>

        </div>


        {{-- Query / Filters --}}
        <section class="admin-card">

            <div class="flex flex-col gap-3 border-b border-[var(--admin-border)] p-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        CATALOG CONTROL
                    </p>

                    <h2 class="mt-1 text-sm font-bold text-[var(--admin-text)]">
                        جستجو و فیلتر
                    </h2>
                </div>

                <div class="text-xs text-[var(--admin-muted)]">
                    نمایش
                    <span class="font-semibold text-[var(--admin-text)]">
                        {{ number_format($products->firstItem() ?? 0) }}
                    </span>
                    تا
                    <span class="font-semibold text-[var(--admin-text)]">
                        {{ number_format($products->lastItem() ?? 0) }}
                    </span>
                    از
                    <span class="font-semibold text-[var(--admin-text)]">
                        {{ number_format($products->total()) }}
                    </span>
                    محصول
                </div>

            </div>


            <form
                method="GET"
                action="{{ route('admin.products.index') }}"
                class="p-5"
            >

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-6">

                    <div class="xl:col-span-2">
                        <label for="search" class="admin-label">
                            جستجو
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ request('search') }}"
                            class="admin-input"
                            placeholder="نام محصول یا SKU..."
                            autocomplete="off"
                        >
                    </div>


                    <div>
                        <label for="category" class="admin-label">
                            دسته‌بندی
                        </label>

                        <select
                            id="category"
                            name="category"
                            class="admin-select"
                        >
                            <option value="">
                                همه دسته‌ها
                            </option>

                            @foreach($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected((string) request('category') === (string) $category->id)
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <label for="status" class="admin-label">
                            وضعیت
                        </label>

                        <select id="status" name="status" class="admin-select">
                            <option value="">همه</option>

                            @foreach($statusLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(request('status') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <label for="stock" class="admin-label">
                            موجودی
                        </label>

                        <select id="stock" name="stock" class="admin-select">
                            <option value="">همه</option>
                            <option value="in_stock" @selected(request('stock') === 'in_stock')>
                                موجود
                            </option>
                            <option value="low_stock" @selected(request('stock') === 'low_stock')>
                                کم‌موجودی
                            </option>
                            <option value="out_of_stock" @selected(request('stock') === 'out_of_stock')>
                                ناموجود
                            </option>
                        </select>
                    </div>


                    <div>
                        <label for="feature" class="admin-label">
                            ویژگی
                        </label>

                        <select id="feature" name="feature" class="admin-select">
                            <option value="">همه</option>
                            <option value="featured" @selected(request('feature') === 'featured')>
                                ویژه
                            </option>
                            <option value="new" @selected(request('feature') === 'new')>
                                جدید
                            </option>
                            <option value="installment" @selected(request('feature') === 'installment')>
                                اقساطی
                            </option>
                        </select>
                    </div>

                </div>


                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex flex-wrap items-center gap-2">

                        @if($activeFilters > 0)
                            <span class="admin-badge admin-badge-info">
                                {{ number_format($activeFilters) }}
                                فیلتر فعال
                            </span>

                            <a
                                href="{{ route('admin.products.index') }}"
                                class="admin-btn admin-btn-ghost px-3"
                            >
                                پاک کردن
                            </a>
                        @endif

                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">

                        <div>
                            <label for="sort" class="sr-only">
                                مرتب‌سازی
                            </label>

                            <select
                                id="sort"
                                name="sort"
                                class="admin-select min-w-[190px]"
                            >
                                <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                                    جدیدترین
                                </option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>
                                    نام: الف تا ی
                                </option>
                                <option value="price_asc" @selected(request('sort') === 'price_asc')>
                                    ارزان‌ترین
                                </option>
                                <option value="price_desc" @selected(request('sort') === 'price_desc')>
                                    گران‌ترین
                                </option>
                                <option value="stock_low" @selected(request('sort') === 'stock_low')>
                                    کمترین موجودی
                                </option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            class="admin-btn admin-btn-secondary"
                        >
                            اعمال
                        </button>

                    </div>

                </div>

            </form>

        </section>


        {{-- Product Table --}}
        <section class="admin-card overflow-hidden">

            <div class="flex flex-col gap-3 border-b border-[var(--admin-border)] p-5 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        INVENTORY
                    </p>

                    <h2 class="mt-1 text-sm font-bold text-[var(--admin-text)]">
                        کاتالوگ محصولات
                    </h2>
                </div>

                <span class="text-xs text-[var(--admin-muted)]">
                    هر صفحه ۲۵ محصول
                </span>

            </div>

            <div class="admin-table-wrapper border-0 rounded-none">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>محصول</th>
                        <th>دسته</th>
                        <th>SKU</th>
                        <th>قیمت</th>
                        <th>موجودی</th>
                        <th>وضعیت</th>
                        <th>ویژگی</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($products as $product)

                        @php
                            $primaryImage = $product->primaryImage;

                            $stockState = match (true) {
                                (int) $product->stock <= 0 => 'empty',
                                (int) $product->stock <= 5 => 'low',
                                default => 'good',
                            };
                        @endphp

                        <tr>

                            <td>
                                <span class="text-xs text-[var(--admin-muted)]">
                                    {{ number_format($products->firstItem() + $loop->index) }}
                                </span>
                            </td>


                            <td>

                                <div class="flex min-w-[280px] items-center gap-3">

                                    <div class="admin-image-thumb shrink-0">

                                        @if($primaryImage?->url)

                                            <img
                                                src="{{ $primaryImage->url }}"
                                                alt="{{ $primaryImage->alt ?: $product->name }}"
                                                class="admin-image"
                                                loading="lazy"
                                            >

                                        @else

                                            <div class="flex h-full w-full items-center justify-center text-[var(--admin-muted)]">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     fill="none"
                                                     viewBox="0 0 24 24"
                                                     stroke-width="1.5"
                                                     stroke="currentColor"
                                                     class="h-5 w-5">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659m0 0 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"
                                                    />
                                                </svg>
                                            </div>

                                        @endif

                                    </div>

                                    <div class="min-w-0">

                                        <a
                                            href="{{ route('admin.products.show', $product) }}"
                                            class="block max-w-[24rem] truncate text-sm font-semibold text-[var(--admin-text)] transition hover:text-[var(--admin-accent)]"
                                        >
                                            {{ $product->name }}
                                        </a>

                                        <p class="mt-1 max-w-[24rem] truncate text-xs text-[var(--admin-muted)]">
                                            {{ $product->short_description ?: 'بدون توضیح کوتاه' }}
                                        </p>

                                    </div>

                                </div>

                            </td>


                            <td>
                                <span class="text-xs text-[var(--admin-text-soft)]">
                                    {{ $product->category?->name ?? '—' }}
                                </span>
                            </td>


                            <td>
                                <code class="font-mono text-xs text-[var(--admin-text-soft)]">
                                    {{ $product->sku ?: '—' }}
                                </code>
                            </td>


                            <td>
                                <div class="whitespace-nowrap">

                                    <span class="font-semibold text-[var(--admin-text)]">
                                        {{ number_format((float) $product->price) }}
                                    </span>

                                    <span class="mr-1 text-[11px] text-[var(--admin-muted)]">
                                        تومان
                                    </span>

                                    @if($product->compare_at_price !== null && (float) $product->compare_at_price > (float) $product->price)

                                        <div class="mt-1 text-[10px] text-[var(--admin-muted)] line-through">
                                            {{ number_format((float) $product->compare_at_price) }}
                                        </div>

                                    @endif

                                </div>
                            </td>


                            <td>

                                <div class="flex items-center gap-2">

                                    <span class="admin-stock-dot admin-stock-{{ $stockState }}"></span>

                                    <span class="text-xs font-semibold text-[var(--admin-text-soft)]">
                                        {{ $stockState === 'empty' ? 'ناموجود' : number_format($product->stock) }}
                                    </span>

                                </div>

                            </td>


                            <td>

                                @switch($product->status)

                                    @case('active')
                                        <span class="admin-badge admin-badge-success">
                                            فعال
                                        </span>
                                        @break

                                    @case('draft')
                                        <span class="admin-badge admin-badge-warning">
                                            پیش‌نویس
                                        </span>
                                        @break

                                    @case('archived')
                                        <span class="admin-badge admin-badge-neutral">
                                            آرشیو
                                        </span>
                                        @break

                                    @default
                                        <span class="admin-badge admin-badge-neutral">
                                            {{ $product->status ?: '—' }}
                                        </span>

                                @endswitch

                            </td>


                            <td>

                                <div class="flex flex-wrap gap-1.5">

                                    @if($product->is_featured)
                                        <span class="admin-badge admin-badge-info">
                                            ویژه
                                        </span>
                                    @endif

                                    @if($product->is_new)
                                        <span class="admin-badge admin-badge-success">
                                            جدید
                                        </span>
                                    @endif

                                    @if($product->installment_enabled)
                                        <span class="admin-badge admin-badge-warning">
                                            اقساطی
                                        </span>
                                    @endif

                                    @if(!$product->is_featured && !$product->is_new && !$product->installment_enabled)
                                        <span class="text-xs text-[var(--admin-muted)]">
                                            —
                                        </span>
                                    @endif

                                </div>

                            </td>


                            <td>

                                <div class="flex items-center justify-end gap-2">

                                    <a
                                        href="{{ route('admin.products.show', $product) }}"
                                        class="admin-btn admin-btn-ghost px-3"
                                    >
                                        مشاهده
                                    </a>

                                    <a
                                        href="{{ route('admin.products.edit', $product) }}"
                                        class="admin-btn admin-btn-secondary px-3"
                                    >
                                        ویرایش
                                    </a>

                                    <form
                                        action="{{ route('admin.products.destroy', $product) }}"
                                        method="POST"
                                        data-confirm="آیا از حذف این محصول مطمئن هستید؟"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="admin-btn admin-btn-danger px-3"
                                        >
                                            حذف
                                        </button>
                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9">

                                <div class="admin-empty py-16">

                                    <div class="admin-empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             fill="none"
                                             viewBox="0 0 24 24"
                                             stroke-width="1.5"
                                             stroke="currentColor"
                                             class="h-6 w-6">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="m21 8.25-9-5.25m0 0-9 5.25m9-5.25v10.5m0 0 9-5.25m-9 5.25-9-5.25M3 13.5v4.75a1.5 1.5 0 0 0 .75 1.299l7.5 4.33a1.5 1.5 0 0 0 1.5 0l7.5-4.33A1.5 1.5 0 0 0 21 18.25V13.5"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                        محصولی مطابق فیلترها پیدا نشد.
                                    </h3>

                                    <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                        جستجو یا فیلترها را تغییر دهید و دوباره امتحان کنید.
                                    </p>

                                    <a
                                        href="{{ route('admin.products.index') }}"
                                        class="admin-btn admin-btn-secondary mt-5"
                                    >
                                        پاک کردن فیلترها
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </section>


        @if($products->hasPages())

            <div class="flex justify-center">
                {{ $products->links() }}
            </div>

        @endif

    </div>

@endsection
