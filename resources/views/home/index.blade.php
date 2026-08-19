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
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "LIVORA",
        "url": @json(url('/')),
        "potentialAction": {
            "@type": "SearchAction",
            "target": @json(url('/shop') . '?search={search_term_string}'),
            "query-input": "required name=search_term_string"
        }
    }
    </script>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
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

        $heroProduct = $featuredProducts->first();

        $featuredWithoutHero = $featuredProducts
            ->when(
                $heroProduct,
                fn ($items) => $items->skip(1)
            );

        $installmentProducts = $featuredProducts
            ->filter(
                fn ($product) =>
                    (bool) $product->installment_enabled
            )
            ->take(4);

        $heroImage =
            $heroProduct?->images?->first()?->url;

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

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition duration-300 hover:bg-[var(--livora-accent)]"
                                >
                                    کشف مجموعه
                                </a>

                                <a
                                    href="{{ route('categories.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition duration-300 hover:border-[var(--livora-ink)]"
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
                         HERO IMAGE
                    ================================================== --}}

                    <div class="relative overflow-hidden rounded-[2rem] bg-[var(--livora-white)]">

                        @if($heroImage)

                            <img
                                src="{{ $heroImage }}"
                                alt="{{ $heroProduct?->name ?? 'LIVORA' }}"
                                class="h-full min-h-[520px] w-full object-cover lg:min-h-full"
                            >

                            <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-transparent to-transparent"></div>


                            {{-- Hero Product Info --}}

                            <div class="absolute bottom-5 left-5 right-5 sm:bottom-7 sm:left-7 sm:right-7">

                                <div class="max-w-md rounded-3xl border border-white/20 bg-black/20 p-5 text-white backdrop-blur-xl">

                                    <p class="text-[10px] uppercase tracking-[0.18em] text-white/60">
                                        Signature Pick
                                    </p>

                                    <h2 class="mt-2 text-lg font-semibold">
                                        {{ $heroProduct->name }}
                                    </h2>

                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

                                    <span class="text-sm text-white/80">

                                        {{ number_format((float) $heroProduct->price) }}

                                        تومان

                                    </span>

                                        <a
                                            href="{{ route('product.show', $heroProduct->slug) }}"
                                            class="inline-flex items-center rounded-full bg-white px-4 py-2 text-xs font-medium text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]"
                                        >
                                            مشاهده محصول
                                        </a>

                                    </div>

                                </div>

                            </div>

                        @else

                            <div class="flex h-full min-h-[520px] items-center justify-center bg-[var(--livora-surface)]">

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
                            class="inline-flex items-center text-sm font-medium text-[var(--livora-accent)]"
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
                            class="inline-flex items-center text-sm font-medium text-[var(--livora-accent)]"
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


                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-8 inline-flex items-center rounded-2xl bg-white px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]"
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
                                class="text-sm font-medium text-[var(--livora-accent)]"
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
                            class="text-sm font-medium text-[var(--livora-accent)]"
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
                                class="mt-7 inline-flex items-center text-sm font-medium text-[var(--livora-ink)]"
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

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                ورود به فروشگاه
                            </a>

                            <a
                                href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-7 py-4 text-sm font-medium text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)]"
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
