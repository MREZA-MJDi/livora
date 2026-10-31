@extends('layouts.app')

@section('title', \App\Models\SeoPageSetting::forRoute('shop')?->meta_title ?: 'فروشگاه مبلمان | SillaGallery')

@section(
    'description',
    (\App\Models\SeoPageSetting::forRoute('shop')?->meta_description ?: 'خرید مبلمان و لوازم خانه از SillaGallery؛ مشاهده محصولات جدید، ویژه، تخفیف‌دار و دارای شرایط خرید اقساطی.')
)

@section('canonical', \App\Models\SeoPageSetting::forRoute('shop')?->canonical_url ?: route('shop.index'))
@section('robots', \App\Models\SeoPageSetting::forRoute('shop')?->robots() ?? \App\Models\SeoSetting::current()->robots())



@section('content')

    <div
        class="overflow-hidden bg-[var(--sg-cream)]"
        x-data="{
            filterOpen: false
        }"
    >

        {{-- =========================================================
             SHOP HEADER
        ========================================================== --}}

        <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-14 lg:py-16">

                    {{-- Breadcrumb --}}

                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--sg-stone)]"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--sg-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--sg-ink)]">
                            فروشگاه
                        </span>

                    </nav>


                    {{-- Header Content --}}

                    <div class="mt-7 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

                        <div class="max-w-3xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                                SillaGallery COLLECTION
                            </p>

                            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-[var(--sg-ink)] sm:text-5xl">
                                فروشگاه
                            </h1>

                            <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--sg-stone)]">
                                مجموعه‌ای منتخب از مبلمان و عناصر خانه برای فضاهایی که قرار است
                                شخصیت و ماندگاری داشته باشند.
                            </p>

                        </div>


                        <div class="text-sm text-[var(--sg-stone)]">

                            <span class="font-semibold text-[var(--sg-ink)]">
                                {{ number_format($products->total()) }}
                            </span>

                            محصول

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             SHOP CONTENT
        ========================================================== --}}

        <section>

            <x-layout.container>

                <div class="grid gap-8 py-8 lg:grid-cols-[250px_minmax(0,1fr)] lg:py-12">


                    {{-- =================================================
                         DESKTOP SIDEBAR
                    ================================================== --}}

                    <aside class="hidden min-w-0 lg:block">

                        <div class="sticky top-28 space-y-5">


                            {{-- =================================================
                                 FILTER CARD
                            ================================================== --}}

                            <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                                <div class="flex items-center justify-between gap-3">

                                    <div>

                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                            DISCOVER
                                        </p>

                                        <h2 class="mt-2 text-sm font-semibold">
                                            فیلترها
                                        </h2>

                                    </div>


                                    <a
                                        href="{{ route('shop.index') }}"
                                        class="shrink-0 text-[11px] text-[var(--sg-stone)] transition hover:text-[var(--sg-accent)]"
                                    >
                                        پاک کردن
                                    </a>

                                </div>


                                {{-- Desktop Filter has its own GET form --}}

                                <form
                                    method="GET"
                                    action="{{ route('shop.index') }}"
                                    class="mt-6"
                                >

                                    <x-shop.filters
                                        :categories="$categories"
                                        :selected-category="request('category')"
                                    />

                                </form>

                            </div>


                            {{-- =================================================
                                 DISCOVERY BOX
                            ================================================== --}}

                            <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-surface)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                    SillaGallery
                                </p>

                                <h3 class="mt-3 text-sm font-semibold">
                                    انتخاب آگاهانه
                                </h3>

                                <p class="mt-2 text-xs leading-7 text-[var(--sg-stone)]">
                                    قیمت، طراحی، موجودی و شرایط خرید اقساطی را
                                    قبل از انتخاب محصول بررسی کنید.
                                </p>

                            </div>

                        </div>

                    </aside>


                    {{-- =================================================
                         PRODUCTS AREA
                    ================================================== --}}

                    <div class="min-w-0">


                        {{-- =================================================
                             MOBILE TOOLBAR
                        ================================================== --}}

                        <div class="mb-5 flex items-center justify-between gap-3 lg:hidden">

                            <button
                                type="button"
                                @click="filterOpen = true"
                                class="inline-flex items-center gap-2 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3 text-sm font-medium text-[var(--sg-ink)] transition hover:border-[var(--sg-ink)]"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-4 w-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M6.75 12h10.5m-7.5 5.25h5.25"
                                    />
                                </svg>

                                فیلترها

                            </button>


                            <span class="text-xs text-[var(--sg-stone)]">

                                <span class="font-medium text-[var(--sg-ink)]">
                                    {{ number_format($products->total()) }}
                                </span>

                                محصول

                            </span>

                        </div>


                        {{-- =================================================
                             SORTING TOOLBAR
                        ================================================== --}}

                        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-4 sm:p-5">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="min-w-0">

                                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                        CURATED PRODUCTS
                                    </p>

                                    <p class="mt-2 text-sm font-medium text-[var(--sg-ink)]">
                                        مجموعه SillaGallery
                                    </p>

                                </div>


                                <div class="w-full sm:min-w-[220px] sm:w-auto">

                                    <x-shop.sorting
                                        :products="$products"
                                    />

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             ACTIVE FILTER
                        ================================================== --}}

                        @if(request()->filled('category'))

                            @php
                                $activeCategory = $categories->firstWhere(
                                    'slug',
                                    request('category')
                                );
                            @endphp


                            <div class="mt-5 flex flex-wrap items-center gap-2">

                                <span class="text-xs text-[var(--sg-stone)]">
                                    فیلتر فعال:
                                </span>

                                <span class="inline-flex items-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] px-3 py-1.5 text-[11px] text-[var(--sg-ink)]">
                                    {{ $activeCategory?->name ?? request('category') }}
                                </span>

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center rounded-full border border-[var(--sg-border)] px-3 py-1.5 text-[11px] text-[var(--sg-stone)] transition hover:border-[var(--sg-ink)] hover:text-[var(--sg-ink)]"
                                >
                                    حذف
                                </a>

                            </div>

                        @endif


                        {{-- =================================================
                             RESULT META
                        ================================================== --}}

                        @if($products->total() > 0)

                            <div class="mt-6">

                                <p class="text-xs text-[var(--sg-stone)]">

                                    نمایش

                                    <span class="font-medium text-[var(--sg-ink)]">
                                        {{ number_format($products->firstItem()) }}
                                    </span>

                                    تا

                                    <span class="font-medium text-[var(--sg-ink)]">
                                        {{ number_format($products->lastItem()) }}
                                    </span>

                                    از

                                    <span class="font-medium text-[var(--sg-ink)]">
                                        {{ number_format($products->total()) }}
                                    </span>

                                </p>

                            </div>

                        @endif


                        {{-- =================================================
                             PRODUCT GRID
                        ================================================== --}}

                        @if($products->isNotEmpty())

                            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-5 lg:grid-cols-2 xl:grid-cols-3">

                                @foreach($products as $product)

                                    <x-product.card
                                        :product="$product"
                                    />

                                @endforeach

                            </div>


                            {{-- =================================================
                                 PAGINATION
                            ================================================== --}}

                            @if($products->hasPages())

                                <nav
                                    class="mt-14"
                                    aria-label="صفحه‌بندی محصولات"
                                >

                                    {{ $products->withQueryString()->links() }}

                                </nav>

                            @endif


                        @else

                            {{-- =================================================
                                 EMPTY STATE
                            ================================================== --}}

                            <div class="mt-8 rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-24 text-center">

                                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-sm text-[var(--sg-stone)]">
                                    —
                                </span>


                                <h2 class="mt-6 text-2xl font-semibold text-[var(--sg-ink)]">
                                    محصولی پیدا نشد
                                </h2>


                                <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--sg-stone)]">
                                    فیلترها یا عبارت جستجو را تغییر دهید و دوباره مجموعه محصولات را بررسی کنید.
                                </p>


                                <a
                                    href="{{ route('shop.index') }}"
                                    class="mt-7 inline-flex rounded-2xl bg-[var(--sg-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]"
                                >
                                    مشاهده همه محصولات
                                </a>

                            </div>

                        @endif

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             MOBILE FILTER DRAWER
        ========================================================== --}}

        <x-shop.filter-drawer
            :categories="$categories"
            :selected-category="request('category')"
        />

    </div>

@endsection
