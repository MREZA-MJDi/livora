@extends('layouts.app')

@section('title', 'LIVORA | مبلمان و لوازم خانه')

@section(
    'description',
    'LIVORA؛ انتخابی دقیق برای خانه‌ای که قرار است ماندگار باشد. کشف مجموعه مبلمان، دکوراسیون و خرید اقساطی.'
)

@section('canonical', url('/'))

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="LIVORA | مبلمان و لوازم خانه"
    >

    <meta
        property="og:description"
        content="کشف مجموعه منتخب LIVORA برای فضاهایی که قرار است شخصیت داشته باشند."
    >

    <meta
        property="og:url"
        content="{{ url('/') }}"
    >

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="LIVORA | مبلمان و لوازم خانه"
    >

    <meta
        name="twitter:description"
        content="کشف مجموعه منتخب LIVORA برای فضاهایی که قرار است شخصیت داشته باشند."
    >

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "LIVORA",
        "url": @json(url('/')),
        "potentialAction": {
            "@@type": "SearchAction",
            "target": @json(url('/shop') . '?search={search_term_string}'),
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Organization",
        "name": "LIVORA",
        "url": @json(url('/'))
        }
</script>

@endpush


@section('content')

    @php

        /*
        |--------------------------------------------------------------------------
        | Homepage Data
        |--------------------------------------------------------------------------
        */

        $heroProducts = $featuredProducts
            ->filter(
                fn ($product) =>
                    $product->images?->first()?->url
            )
            ->values();

        $heroProduct = $heroProducts->first();

        /*
         * Keep all featured products in the collection section.
         */
        $featuredWithoutHero = $featuredProducts;

        $installmentProducts = $featuredProducts
            ->filter(
                fn ($product) =>
                    (bool) $product->installment_enabled
            )
            ->take(4);

    @endphp


    <div class="overflow-hidden bg-[var(--livora-cream)]">


        {{-- =========================================================
             HERO
        ========================================================== --}}

        <section class="relative">

            <div class="mx-auto max-w-[1600px] px-4 sm:px-6 lg:px-8">

                <div class="grid min-h-[calc(100vh-76px)] grid-cols-1 gap-8 py-5 lg:grid-cols-[0.92fr_1.08fr] lg:py-7">


                    {{-- =================================================
                         HERO CONTENT
                    ================================================== --}}

                    <div class="relative flex flex-col justify-center rounded-[2rem] bg-[var(--livora-surface)] px-7 py-12 sm:px-10 lg:px-14 lg:py-16">

                        <div class="max-w-xl">

                            <div class="flex items-center gap-3 text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">

                                <span class="h-px w-8 bg-[var(--livora-accent)]"></span>

                                Furniture & Living

                            </div>


                            <h1 class="mt-7 text-5xl font-semibold leading-[1.05] tracking-tight text-[var(--livora-ink)] sm:text-6xl xl:text-7xl">

                                خانه‌ای که

                                <span class="block text-[var(--livora-accent)]">
                                    شبیه توست.
                                </span>

                            </h1>


                            <p class="mt-7 max-w-lg text-sm leading-8 text-[var(--livora-stone)] sm:text-base">

                                مجموعه‌ای منتخب از مبلمان و عناصر خانه برای ساختن فضایی
                                گرم، ماندگار و دقیق؛ از انتخاب اول تا آخرین جزئیات.

                            </p>


                            <div class="mt-9 flex flex-col gap-3 sm:flex-row">

                                {{-- CTA 01 --}}

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                              >
                                    کشف مجموعه


                                </a>


                                {{-- CTA 02 --}}

                                <a
                                    href="{{ route('categories.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                                >
                                    مشاهده دسته‌بندی‌ها
                                </a>

                            </div>

                        </div>


                        {{-- Hero Stats --}}

                        <div class="mt-12 grid max-w-xl grid-cols-3 gap-3">

                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    Quality
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    انتخاب‌شده
                                </p>

                            </div>


                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    Payment
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    اقساطی
                                </p>

                            </div>


                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                                    Service
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    همراه شما
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         HERO PRODUCT SLIDER
                    ================================================== --}}

                    <div
                        id="livoraHeroSlider"
                        class="relative min-h-[520px] overflow-hidden rounded-[2rem] bg-[var(--livora-white)] lg:min-h-full"
                    >

                        @if($heroProducts->isNotEmpty())

                            <div class="absolute inset-0">

                                @foreach($heroProducts as $index => $product)

                                    @php
                                        $image = $product->images?->first()?->url;
                                    @endphp

                                    <div
                                        class="livora-hero-slide absolute inset-0 transition-all duration-1000 ease-[cubic-bezier(0.22,1,0.36,1)] {{ $index === 0 ? 'opacity-100 scale-100 z-10' : 'opacity-0 scale-105 z-0' }}"
                                        data-slide="{{ $index }}"
                                    >

                                        <img
                                            src="{{ $image }}"
                                            alt="{{ $product->name }}"
                                            class="h-full w-full object-cover"
                                        >


                                        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent"></div>

                                        <div class="absolute inset-0 bg-gradient-to-r from-black/10 via-transparent to-black/10"></div>


                                        {{-- Product info --}}

                                        <div class="absolute bottom-5 left-5 right-5 sm:bottom-7 sm:left-7 sm:right-7">

                                            <div
                                                class="livora-hero-info max-w-md translate-y-3 rounded-3xl border border-white/20 bg-black/20 p-5 text-white opacity-0 backdrop-blur-xl transition-all duration-1000 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                            >

                                                <div class="flex items-center justify-between gap-4">

                                                    <div class="min-w-0">

                                                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">
                                                            Featured Product
                                                        </p>

                                                        <h2 class="mt-2 truncate text-lg font-semibold sm:text-xl">
                                                            {{ $product->name }}
                                                        </h2>

                                                        <p class="mt-2 text-sm text-white/75">
                                                            {{ number_format((float) $product->price) }}
                                                            تومان
                                                        </p>

                                                    </div>


                                                    {{-- Product CTA --}}

                                                    <a
                                                        href="{{ route('product.show', $product->slug) }}"
                                                        class="inline-flex shrink-0 items-center rounded-full bg-white px-4 py-2 text-xs font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:bg-[var(--livora-cream)]"
                                                    >
                                                        مشاهده
                                                    </a>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>


                            {{-- Counter --}}

                            <div class="absolute right-5 top-5 z-30 sm:right-7 sm:top-7">

                                <div
                                    class="rounded-full border border-white/20 bg-black/20 px-4 py-2 text-[10px] font-medium tracking-[0.2em] text-white backdrop-blur-md"
                                >

                                    <span id="livoraHeroCurrent">
                                        01
                                    </span>

                                    <span class="mx-1 text-white/40">
                                        /
                                    </span>

                                    <span class="text-white/50">
                                        {{ str_pad($heroProducts->count(), 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                </div>

                            </div>


                            {{-- Progress --}}

                            <div class="absolute bottom-0 left-0 right-0 z-30 h-[2px] bg-white/15">

                                <div
                                    id="livoraHeroProgress"
                                    class="h-full origin-right bg-white"
                                    style="transform: scaleX(1);"
                                ></div>

                            </div>


                            {{-- Controls --}}

                            @if($heroProducts->count() > 1)

                                <div class="absolute left-5 top-5 z-30 flex gap-2 sm:left-7 sm:top-7">

                                    <button
                                        type="button"
                                        id="livoraHeroPrev"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/20 text-white backdrop-blur-md transition-colors duration-300 hover:bg-white hover:text-black"
                                        aria-label="محصول قبلی"
                                    >
                                        →
                                    </button>


                                    <button
                                        type="button"
                                        id="livoraHeroNext"
                                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/20 text-white backdrop-blur-md transition-colors duration-300 hover:bg-white hover:text-black"
                                        aria-label="محصول بعدی"
                                    >
                                        ←
                                    </button>

                                </div>

                            @endif


                        @else

                            <div class="flex min-h-[520px] items-center justify-center bg-[var(--livora-surface)]">

                                <span class="text-sm tracking-[0.2em] text-[var(--livora-stone)]">
                                    LIVORA
                                </span>

                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </section>


        {{-- =========================================================
             CATEGORY DISCOVERY
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div class="max-w-2xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                Shop by space
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                برای هر گوشه، یک انتخاب دقیق.
                            </h2>

                            <p class="mt-4 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">

                                از نشیمن و اتاق خواب تا میز ناهارخوری و جزئیات کوچک‌تر،
                                دسته‌بندی مناسب فضای خودتان را پیدا کنید.

                            </p>

                        </div>


                        <a
                            href="{{ route('categories.index') }}"
                            class="inline-flex items-center text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                        >
                            همه دسته‌بندی‌ها

                            <span class="mr-2">
                                ←
                            </span>

                        </a>

                    </div>


                    <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                        @foreach($categories->take(4) as $category)

                            @if($category->image)

                                <x-shop.category-card
                                    :name="$category->name"
                                    :image="$category->image"
                                    :href="route('categories.show', $category->slug)"
                                    :count="$category->products_count"
                                />

                            @endif

                        @endforeach

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FEATURED COLLECTION
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                Curated collection
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                انتخاب‌های این فصل
                            </h2>

                            <p class="mt-3 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                                محصولاتی که برای فرم، کیفیت و حضورشان در فضا انتخاب شده‌اند.
                            </p>

                        </div>


                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex items-center text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                        >
                            مشاهده همه محصولات

                            <span class="mr-2">
                                ←
                            </span>

                        </a>

                    </div>


                    @if($featuredWithoutHero->isNotEmpty())

                        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-8 lg:grid-cols-4">

                            @foreach($featuredWithoutHero as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                    @else

                        <div class="mt-10 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-10 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                محصولات منتخب به‌زودی اینجا نمایش داده می‌شوند.
                            </p>

                        </div>

                    @endif

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSTALLMENT HERO
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">


                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-white/45">
                                LIVORA INSTALLMENTS
                            </p>

                            <h2 class="mt-4 max-w-xl text-3xl font-semibold tracking-tight sm:text-4xl lg:text-5xl">

                                خانه‌تان را انتخاب کنید.

                                <span class="block text-white/50">
                                    پرداختش را برنامه‌ریزی کنید.
                                </span>

                            </h2>


                            <p class="mt-6 max-w-xl text-sm leading-8 text-white/60">

                                بعضی از محصولات Livora می‌توانند با شرایط اقساطی
                                تعریف‌شده توسط فروشگاه خریداری شوند؛
                                پیش‌پرداخت، تعداد چک و فاصله سررسید از قبل مشخص است.

                            </p>


                            {{-- Installment CTA --}}

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-8 inline-flex items-center rounded-2xl bg-white px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:bg-[var(--livora-cream)]"
                            >
                                مشاهده محصولات اقساطی
                            </a>

                        </div>


                        <div class="grid grid-cols-2 gap-3">

                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                                    Today
                                </span>

                                <p class="mt-4 text-2xl font-semibold">
                                    50%
                                </p>

                                <p class="mt-2 text-xs leading-6 text-white/45">
                                    پیش‌پرداخت نمونه
                                </p>

                            </div>


                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                                    Cheques
                                </span>

                                <p class="mt-4 text-2xl font-semibold">
                                    2+
                                </p>

                                <p class="mt-2 text-xs leading-6 text-white/45">
                                    قابل تنظیم توسط فروشگاه
                                </p>

                            </div>


                            <div class="col-span-2 rounded-3xl border border-white/10 bg-white/[0.07] p-5 sm:p-6">

                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                    <div>

                                        <p class="text-sm font-semibold">
                                            شرایط هر محصول متفاوت است
                                        </p>

                                        <p class="mt-2 text-xs leading-6 text-white/45">
                                            درصد پیش‌پرداخت و برنامه تسویه را در صفحه محصول ببینید.
                                        </p>

                                    </div>


                                    <span class="inline-flex w-fit rounded-full border border-white/10 px-4 py-2 text-[11px] text-white/60">
                                        Transparent pricing
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSTALLMENT PRODUCT PICKS
        ========================================================== --}}

        @if($installmentProducts->isNotEmpty())

            <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

                <x-layout.container>

                    <div class="py-16 sm:py-20">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                    Flexible payment
                                </p>

                                <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                    محصولاتی با امکان خرید اقساطی
                                </h2>

                                <p class="mt-3 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                                    شرایط پرداخت را قبل از خرید ببینید و آگاهانه انتخاب کنید.
                                </p>

                            </div>


                            <a
                                href="{{ route('shop.index') }}"
                                class="text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                            >
                                مشاهده فروشگاه
                            </a>

                        </div>


                        <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">

                            @foreach($installmentProducts as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                    </div>

                </x-layout.container>

            </section>

        @endif


        {{-- =========================================================
             NEW ARRIVALS
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                New arrivals
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                                تازه‌های LIVORA
                            </h2>

                            <p class="mt-3 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                                تازه‌ترین انتخاب‌هایی که به مجموعه اضافه شده‌اند.
                            </p>

                        </div>


                        <a
                            href="{{ route('shop.index', ['sort' => 'newest']) }}"
                            class="text-sm font-medium text-[var(--livora-accent)] transition-colors duration-300 hover:text-[var(--livora-ink)]"
                        >
                            تازه‌ترین محصولات
                        </a>

                    </div>


                    @if($newProducts->isNotEmpty())

                        <div class="mt-10 grid grid-cols-2 gap-x-4 gap-y-8 lg:grid-cols-4">

                            @foreach($newProducts as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                    @else

                        <div class="mt-10 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-10 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                محصولات جدید به‌زودی اضافه می‌شوند.
                            </p>

                        </div>

                    @endif

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSPIRATION
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-16 sm:py-20">

                    <div class="grid items-end gap-8 lg:grid-cols-[0.85fr_1.15fr]">


                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                Inspiration
                            </p>

                            <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">

                                فقط مبلمان نیست.

                                <span class="block text-[var(--livora-accent)]">
                                    سبک زندگی است.
                                </span>

                            </h2>


                            <p class="mt-5 max-w-lg text-sm leading-8 text-[var(--livora-stone)]">

                                برای انتخاب بهتر، فقط قیمت کافی نیست؛
                                از اندازه‌گیری فضا و انتخاب رنگ تا ترکیب متریال،
                                تصمیم‌های درست را ساده‌تر می‌کنیم.

                            </p>


                            <a
                                href="{{ route('about') }}"
                                class="mt-7 inline-flex items-center text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:text-[var(--livora-accent)]"
                            >
                                درباره LIVORA

                                <span class="mr-2">
                                    ←
                                </span>

                            </a>

                        </div>


                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                            <article class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    01
                                </span>

                                <h3 class="mt-8 text-base font-semibold">
                                    اندازه‌گیری درست
                                </h3>

                                <p class="mt-3 text-xs leading-7 text-[var(--livora-stone)]">
                                    قبل از انتخاب، ابعاد فضا و مسیر ورود محصول را بررسی کنید.
                                </p>

                            </article>


                            <article class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    02
                                </span>

                                <h3 class="mt-8 text-base font-semibold">
                                    متریال و رنگ
                                </h3>

                                <p class="mt-3 text-xs leading-7 text-[var(--livora-stone)]">
                                    محصولی انتخاب کنید که با نور، رنگ و شخصیت فضای شما هماهنگ باشد.
                                </p>

                            </article>


                            <article class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <span class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    03
                                </span>

                                <h3 class="mt-8 text-base font-semibold">
                                    خرید آگاهانه
                                </h3>

                                <p class="mt-3 text-xs leading-7 text-[var(--livora-stone)]">
                                    قیمت، شرایط اقساط، مشخصات و خدمات را کنار هم مقایسه کنید.
                                </p>

                            </article>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             TRUST
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="grid grid-cols-1 divide-y divide-[var(--livora-border)] py-4 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-4">

                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Selection
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            انتخاب با دقت
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            محصولات با تمرکز بر فرم، کاربرد و کیفیت انتخاب می‌شوند.
                        </p>

                    </div>


                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Payment
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            پرداخت منعطف
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            برای برخی محصولات، شرایط خرید اقساطی در دسترس است.
                        </p>

                    </div>


                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Support
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            همراهی تا خرید
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            اطلاعات محصول و مسیر خرید را شفاف و ساده نگه می‌داریم.
                        </p>

                    </div>


                    <div class="px-2 py-8 sm:px-8">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                            Experience
                        </p>

                        <h3 class="mt-3 text-sm font-semibold">
                            تجربه‌ای آرام
                        </h3>

                        <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                            از کشف محصول تا پرداخت، همه‌چیز با کمترین اصطکاک طراحی شده است.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-20 sm:py-28">

                    <div class="mx-auto max-w-3xl text-center">

                        <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                            LIVORA
                        </p>

                        <h2 class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
                            چیزی برای ماندن پیدا کنید.
                        </h2>

                        <p class="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                            مجموعه را ببینید، فضای خودتان را تصور کنید
                            و انتخابی انجام دهید که سال‌ها با شما بماند.
                        </p>

                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

                            {{-- Final CTA 01 --}}

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition-colors duration-300 hover:bg-[var(--livora-accent)]"
                            >
                                ورود به فروشگاه
                            </a>


                            {{-- Final CTA 02 --}}

                            <a
                                href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-7 py-4 text-sm font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:border-[var(--livora-ink)]"
                            >
                                تماس با ما
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection


@push('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const slider = document.getElementById('livoraHeroSlider');

            if (!slider) {
                return;
            }


            const slides = Array.from(
                slider.querySelectorAll('.livora-hero-slide')
            );


            if (slides.length <= 1) {
                return;
            }


            const currentEl = document.getElementById(
                'livoraHeroCurrent'
            );

            const progressEl = document.getElementById(
                'livoraHeroProgress'
            );

            const prevButton = document.getElementById(
                'livoraHeroPrev'
            );

            const nextButton = document.getElementById(
                'livoraHeroNext'
            );


            /*
            |--------------------------------------------------------------------------
            | Slider timing
            |--------------------------------------------------------------------------
            */

            const interval = 7000;


            let current = 0;
            let timer = null;


            /*
            |--------------------------------------------------------------------------
            | Counter
            |--------------------------------------------------------------------------
            */

            const updateCounter = () => {

                if (!currentEl) {
                    return;
                }

                currentEl.textContent = String(
                    current + 1
                ).padStart(2, '0');
            };


            /*
            |--------------------------------------------------------------------------
            | Product info animation
            |--------------------------------------------------------------------------
            */

            const animateInfo = (slide) => {

                slides.forEach((item) => {

                    const info = item.querySelector(
                        '.livora-hero-info'
                    );

                    if (!info) {
                        return;
                    }

                    info.classList.remove(
                        'translate-y-0',
                        'opacity-100'
                    );

                    info.classList.add(
                        'translate-y-3',
                        'opacity-0'
                    );

                });


                const activeInfo = slide.querySelector(
                    '.livora-hero-info'
                );


                if (!activeInfo) {
                    return;
                }


                setTimeout(() => {

                    activeInfo.classList.remove(
                        'translate-y-3',
                        'opacity-0'
                    );

                    activeInfo.classList.add(
                        'translate-y-0',
                        'opacity-100'
                    );

                }, 180);
            };


            /*
            |--------------------------------------------------------------------------
            | Progress
            |--------------------------------------------------------------------------
            */

            const resetProgress = () => {

                if (!progressEl) {
                    return;
                }


                progressEl.style.transition = 'none';

                progressEl.style.transform = 'scaleX(0)';


                requestAnimationFrame(() => {

                    requestAnimationFrame(() => {

                        progressEl.style.transition =
                            `transform ${interval}ms linear`;

                        progressEl.style.transform =
                            'scaleX(1)';

                    });

                });

            };


            /*
            |--------------------------------------------------------------------------
            | Show slide
            |--------------------------------------------------------------------------
            */

            const showSlide = (index) => {

                slides.forEach(
                    (slide, slideIndex) => {

                        if (slideIndex === index) {

                            slide.classList.remove(
                                'opacity-0',
                                'scale-105',
                                'z-0'
                            );

                            slide.classList.add(
                                'opacity-100',
                                'scale-100',
                                'z-10'
                            );

                        } else {

                            slide.classList.remove(
                                'opacity-100',
                                'scale-100',
                                'z-10'
                            );

                            slide.classList.add(
                                'opacity-0',
                                'scale-105',
                                'z-0'
                            );

                        }

                    }
                );


                current = index;

                updateCounter();

                animateInfo(slides[current]);

                resetProgress();
            };


            /*
            |--------------------------------------------------------------------------
            | Next
            |--------------------------------------------------------------------------
            */

            const nextSlide = () => {

                const next =
                    (current + 1) % slides.length;

                showSlide(next);
            };


            /*
            |--------------------------------------------------------------------------
            | Previous
            |--------------------------------------------------------------------------
            */

            const prevSlide = () => {

                const previous =
                    (current - 1 + slides.length) % slides.length;

                showSlide(previous);
            };


            /*
            |--------------------------------------------------------------------------
            | Autoplay
            |--------------------------------------------------------------------------
            */

            const stopAutoPlay = () => {

                if (!timer) {
                    return;
                }

                clearInterval(timer);

                timer = null;
            };


            const startAutoPlay = () => {

                stopAutoPlay();

                timer = setInterval(() => {

                    nextSlide();

                }, interval);
            };


            /*
            |--------------------------------------------------------------------------
            | Controls
            |--------------------------------------------------------------------------
            */

            if (nextButton) {

                nextButton.addEventListener('click', () => {

                    nextSlide();

                    startAutoPlay();

                });
            }


            if (prevButton) {

                prevButton.addEventListener('click', () => {

                    prevSlide();

                    startAutoPlay();

                });
            }


            /*
            |--------------------------------------------------------------------------
            | Initial state
            |--------------------------------------------------------------------------
            */

            updateCounter();

            animateInfo(slides[0]);

            resetProgress();

            startAutoPlay();

        });
    </script>

@endpush
