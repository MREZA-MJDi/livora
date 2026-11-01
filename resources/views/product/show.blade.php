@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | Product SEO
    |--------------------------------------------------------------------------
    */

    $pageTitle = str_ireplace(
        'SillaGallery',
        'سیلاگالری',
        $product->meta_title
            ?: ($product->name . ' | سیلاگالری')
    );

    $pageDescription = str_ireplace(
        'SillaGallery',
        'سیلاگالری',
        $product->meta_description
            ?: ($product->short_description
                ?: ('خرید ' . $product->name . ' با مشاهده قیمت، مشخصات و شرایط خرید در سیلاگالری.'))
    );

    /*
    |--------------------------------------------------------------------------
    | Installment
    |--------------------------------------------------------------------------
    */

    $installmentEnabled =
        (bool) $product->installment_enabled;

    $cashPercent =
        $installmentEnabled
            ? (int) ($product->installment_cash_percent ?? 0)
            : 0;

    $chequeCount =
        $installmentEnabled
            ? (int) ($product->installment_cheque_count ?? 0)
            : 0;

    $intervalMonths =
        $installmentEnabled
            ? (int) ($product->installment_interval_months ?? 0)
            : 0;

    $productPrice =
        (float) $product->price;

    $cashAmount =
        $installmentEnabled && $cashPercent > 0
            ? round(
                $productPrice * ($cashPercent / 100),
                2
            )
            : 0;

    $deferredAmount =
        $installmentEnabled
            ? round(
                $productPrice - $cashAmount,
                2
            )
            : 0;

    $chequeAmounts = [];

    if (
        $installmentEnabled
        && $chequeCount > 0
        && $deferredAmount > 0
    ) {
        $baseChequeAmount = round(
            $deferredAmount / $chequeCount,
            2
        );

        $distributed = 0;

        for ($i = 1; $i <= $chequeCount; $i++) {

            if ($i === $chequeCount) {
                $amount = round(
                    $deferredAmount - $distributed,
                    2
                );
            } else {
                $amount = $baseChequeAmount;
            }

            $chequeAmounts[] = $amount;

            $distributed = round(
                $distributed + $amount,
                2
            );
        }
    }

    $galleryImageIndexes = $product->images
        ->values()
        ->mapWithKeys(
            fn ($image, $index) => [
                (int) $image->id => (int) $index,
            ]
        )
        ->all();

    $variantImageMap = $product->variants
        ->mapWithKeys(
            fn ($variant) => [
                (string) $variant->id => $variant->images
                    ->map(
                        fn ($image) =>
                            $galleryImageIndexes[$image->id] ?? null
                    )
                    ->filter(fn ($index) => $index !== null)
                    ->values()
                    ->all(),
            ]
        )
        ->all();


    $mainImage =
        $product->images?->first()?->url;

    $variantGroups =
        $product->variants
            ->groupBy('type');

    $variantOptionsAvailable =
        $variantGroups->isNotEmpty()
        && $variantGroups->every(
            fn ($options) =>
                $options->contains(
                    fn ($option) => (int) $option->stock > 0
                )
        );

    $canPurchase =
        $variantGroups->isNotEmpty()
            ? $variantOptionsAvailable
            : (int) $product->stock > 0;

    $availability =
        $canPurchase
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock';
@endphp

@php
    $productStructuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $pageDescription,
        'sku' => $product->sku,
        'image' => $product->images->pluck('url')->filter()->values()->all(),
        'brand' => ['@type' => 'Brand', 'name' => 'SillaGallery'],
    ];

    // Storefront prices are Toman; Schema.org IRR values are Rial.
    // Variant prices are dynamic, so avoid publishing a misleading single offer.
    if ($product->variants->isEmpty()) {
        $productStructuredData['offers'] = [
            '@type' => 'Offer',
            'url' => $product->canonical_url ?: route('product.show', $product->slug),
            'priceCurrency' => 'IRR',
            'price' => number_format($productPrice * 10, 0, '.', ''),
            'availability' => $availability,
            'itemCondition' => 'https://schema.org/NewCondition',
        ];
    }
@endphp

@section('title', $pageTitle)

@section('description', $pageDescription)
@section('robots', \App\Models\SeoSetting::current()->robotsFor($product->seo_index, $product->seo_follow))
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)
@section('og_image', $product->og_image ?: $mainImage)

@section(
    'canonical',
    ($product->canonical_url ?: route('product.show', $product->slug))
)

@push('seo')
<script type="application/ld+json">{!! json_encode($productStructuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@if(!empty($product->faq_items))
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($product->faq_items)->filter(fn ($item) => filled($item['question'] ?? null) && filled($item['answer'] ?? null))->map(fn ($item) => [
        '@type' => 'Question',
        'name' => $item['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endif
@endpush
@section('content')

    <div
        class="bg-[var(--sg-cream)] pb-28 lg:pb-0"
        x-data="{
        quantity: {{ max(1, min((int) $product->stock, 1)) }},
        showInstallmentDetails: false
    }"
    >

        {{-- =========================================================
             BREADCRUMB
        ========================================================== --}}
        <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)]">

            <x-layout.container>

                <nav
                    aria-label="breadcrumb"
                    class="flex flex-wrap items-center gap-2 py-5 text-[11px] text-[var(--sg-stone)]"
                >

                    <a
                        href="{{ route('home') }}"
                        class="transition hover:text-[var(--sg-ink)]"
                    >
                        خانه
                    </a>

                    <span>/</span>

                    <a
                        href="{{ route('shop.index') }}"
                        class="transition hover:text-[var(--sg-ink)]"
                    >
                        فروشگاه
                    </a>

                    @if($product->category)

                        <span>/</span>

                        <a
                            href="{{ route('categories.show', $product->category->slug) }}"
                            class="transition hover:text-[var(--sg-ink)]"
                        >
                            {{ $product->category->name }}
                        </a>

                    @endif

                    <span>/</span>

                    <span class="text-[var(--sg-ink)]">
                    {{ $product->name }}
                </span>

                </nav>

            </x-layout.container>

        </section>


        @if($product->banner_image_url)
            <section class="product-page-banner border-b border-[var(--sg-border)] bg-[var(--sg-white)] py-4 sm:py-6">
                <x-layout.container>
                    <figure class="relative overflow-hidden rounded-2xl bg-[var(--sg-surface)] sm:rounded-3xl">
                        <img
                            src="{{ $product->banner_image_url }}"
                            alt="{{ $product->banner_image_alt ?: $product->name }}"
                            width="1920"
                            height="800"
                            fetchpriority="high"
                            decoding="async"
                            class="product-page-banner-image block aspect-[12/5] w-full object-cover"
                        >
                    </figure>
                </x-layout.container>
            </section>
        @endif


        {{-- =========================================================
             PRODUCT HERO
        ========================================================== --}}
        <section>

            <x-layout.container class="product-detail-container">

                <div class="product-detail-grid grid items-start gap-8 py-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)] lg:gap-12 lg:py-12 xl:gap-16">

                    {{-- Gallery --}}
                    <div class="min-w-0">

                        <div class="xl:sticky xl:top-24">
                            <x-product.gallery
                                :product="$product"
                                :variant-image-map="$variantImageMap"
                            />
                        </div>

                    </div>


                    {{-- Product Details --}}
                    <div class="min-w-0">

                        {{-- Labels --}}
                        <div class="flex flex-wrap items-center gap-2">

                            @if($product->is_new)

                                <span class="rounded-full bg-[var(--sg-ink)] px-3 py-1.5 text-[10px] font-medium text-white">
                                جدید
                            </span>

                            @endif

                            @if($product->is_featured)

                                <span class="rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] px-3 py-1.5 text-[10px] font-medium text-[var(--sg-ink)]">
                                منتخب سیلاگالری
                            </span>

                            @endif

                            @if($installmentEnabled)

                                <span class="rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] px-3 py-1.5 text-[10px] font-medium text-[var(--sg-accent)]">
                                خرید اقساطی
                            </span>

                            @endif

                        </div>


                        {{-- Category --}}
                        @if($product->category)

                            <p class="mt-6 text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                                {{ $product->category->name }}
                            </p>

                        @endif


                        {{-- Title --}}
                        <h1 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-[var(--sg-ink)] sm:text-4xl xl:text-5xl">
                            {{ $product->name }}
                        </h1>


                        {{-- SKU --}}
                        <div class="mt-4 flex flex-wrap items-center gap-3 text-[11px] text-[var(--sg-stone)]">

                        <span>
                            SKU:
                            {{ $product->sku }}
                        </span>

                            <span class="h-1 w-1 rounded-full bg-[var(--sg-border)]"></span>

                            <span>
                            {{ $product->stock > 0 ? 'موجود' : 'ناموجود' }}
                        </span>

                        </div>


                        {{-- Price --}}
                        <div class="mt-7">

                            <x-product.price
                                :product="$product"
                            />

                        </div>


                        {{-- Short Description --}}
                        @if($product->short_description)

                            <div class="mt-7 border-t border-[var(--sg-border)] pt-7">

                                <p class="text-sm leading-8 text-[var(--sg-stone)]">
                                    {{ $product->short_description }}
                                </p>

                            </div>

                        @endif


                        {{-- Installment Box --}}
                        @if($installmentEnabled)

                            <div class="mt-7 overflow-hidden rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)]">

                                <div class="border-b border-[var(--sg-border)] bg-[var(--sg-surface)] p-5 sm:p-6">

                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                        <div>

                                            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--sg-accent)]">
                                                FLEXIBLE PAYMENT
                                            </p>

                                            <h2 class="mt-2 text-lg font-semibold">
                                                امکان خرید اقساطی
                                            </h2>

                                            <p class="mt-2 text-xs leading-7 text-[var(--sg-stone)]">
                                                بخشی از مبلغ امروز پرداخت می‌شود و باقی‌مانده
                                                طبق شرایط ثبت‌شده برای این محصول تسویه خواهد شد.
                                            </p>

                                        </div>

                                        <span class="rounded-full bg-[var(--sg-ink)] px-3 py-1.5 text-[10px] font-medium text-white">
                                        {{ number_format($cashPercent) }}٪ پیش‌پرداخت
                                    </span>

                                    </div>

                                </div>

                                <div class="p-5 sm:p-6">

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                                        <div class="rounded-2xl border border-[var(--sg-border)] p-4">

                                            <p class="text-[10px] text-[var(--sg-stone)]">
                                                پرداخت امروز
                                            </p>

                                            <p class="mt-2 text-base font-semibold">
                                                {{ number_format($cashAmount) }}
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--sg-stone)]">
                                                تومان
                                            </p>

                                        </div>

                                        <div class="rounded-2xl border border-[var(--sg-border)] p-4">

                                            <p class="text-[10px] text-[var(--sg-stone)]">
                                                باقی‌مانده
                                            </p>

                                            <p class="mt-2 text-base font-semibold">
                                                {{ number_format($deferredAmount) }}
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--sg-stone)]">
                                                تومان
                                            </p>

                                        </div>

                                        <div class="rounded-2xl border border-[var(--sg-border)] p-4">

                                            <p class="text-[10px] text-[var(--sg-stone)]">
                                                برنامه تسویه
                                            </p>

                                            <p class="mt-2 text-base font-semibold">
                                                {{ number_format($chequeCount) }}
                                                چک
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--sg-stone)]">
                                                هر {{ number_format($intervalMonths) }} ماه
                                            </p>

                                        </div>

                                    </div>


                                    @if(count($chequeAmounts))

                                        <button
                                            type="button"
                                            @click="showInstallmentDetails = !showInstallmentDetails"
                                            class="mt-5 flex w-full items-center justify-between rounded-2xl bg-[var(--sg-surface)] px-4 py-4 text-right"
                                        >

                                        <span class="text-xs font-medium">
                                            مشاهده جزئیات سررسیدها
                                        </span>

                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke-width="1.5"
                                                stroke="currentColor"
                                                class="h-4 w-4 transition-transform"
                                                :class="showInstallmentDetails ? 'rotate-180' : ''"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                                />
                                            </svg>

                                        </button>


                                        <div
                                            x-show="showInstallmentDetails"
                                            x-cloak
                                            x-collapse
                                            class="mt-3 space-y-2"
                                        >

                                            @foreach($chequeAmounts as $index => $amount)

                                                <div class="flex items-center justify-between gap-4 rounded-xl border border-[var(--sg-border)] px-4 py-3">

                                                    <div>

                                                        <p class="text-xs font-medium">
                                                            چک {{ number_format($index + 1) }}
                                                        </p>

                                                        <p class="mt-1 text-[10px] text-[var(--sg-stone)]">
                                                            {{ number_format(($index + 1) * $intervalMonths) }}
                                                            ماه بعد
                                                        </p>

                                                    </div>

                                                    <p class="text-sm font-semibold">
                                                        {{ number_format($amount) }}
                                                        تومان
                                                    </p>

                                                </div>

                                            @endforeach

                                        </div>

                                    @endif


                                    <div class="mt-5 flex items-start gap-3 rounded-2xl border border-[var(--sg-border)] p-4">

                                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--sg-surface)] text-xs font-semibold">
                                        i
                                    </span>

                                        <p class="text-[11px] leading-6 text-[var(--sg-stone)]">
                                            شرایط نهایی پرداخت اقساطی هنگام Checkout و بر اساس
                                            تنظیمات ثبت‌شده برای سفارش به شما نمایش داده می‌شود.
                                        </p>

                                    </div>

                                </div>

                            </div>

                        @endif


                        {{-- Product / Cart --}}
                        <form
                            id="product-purchase"
                            action="{{ route('cart.add', $product) }}"
                            method="POST"
                            class="mt-8 space-y-7"
                        >

                            @csrf

                            {{-- Variants --}}
                            @if($product->variants->isNotEmpty())

                                <div class="border-t border-[var(--sg-border)] pt-7">

                                    <div class="mb-5">

                                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--sg-accent)]">
                                            CUSTOMIZE
                                        </p>

                                        <h2 class="mt-2 text-sm font-semibold">
                                            انتخاب ویژگی
                                        </h2>

                                    </div>

                                    <div class="space-y-6">

                                        @foreach($product->variants->groupBy('type') as $type => $variants)

                                            <x-product.variant-selector
                                                :title="$variants->first()->name"
                                                :options="$variants"
                                                :name="$type"
                                            />

                                        @endforeach

                                    </div>

                                    @if(
                                        $errors->has('variants')
                                        || collect($errors->keys())
                                            ->contains(
                                                fn ($key) =>
                                                    str_starts_with($key, 'variants.')
                                            )
                                    )
                                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-6 text-red-800">
                                            لطفاً تمام ویژگی‌های ضروری محصول را انتخاب کنید.
                                        </div>
                                    @endif

                                </div>

                            @endif


                            {{-- Quantity --}}
                            <div class="border-t border-[var(--sg-border)] pt-7">

                                <div class="flex items-center justify-between gap-4">

                                    <div>

                                        <p class="text-sm font-semibold">
                                            تعداد
                                        </p>

                                        <p class="mt-1 text-[11px] text-[var(--sg-stone)]">
                                            موجودی:
                                            {{ number_format($product->stock) }}
                                        </p>

                                    </div>

                                    <x-shop.quantity
                                        name="quantity"
                                        min="1"
                                        :max="max(1, $product->stock)"
                                    />

                                </div>

                            </div>


                            {{-- CTA --}}
                            <div class="border-t border-[var(--sg-border)] pt-7">

                                <button
                                    type="submit"
                                    @disabled(!$canPurchase)
                                    class="flex w-full items-center justify-center rounded-2xl bg-[var(--sg-ink)] px-6 py-4 text-sm font-medium text-white transition duration-300 hover:bg-[var(--sg-accent)] disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                    {{ $canPurchase ? 'افزودن به سبد خرید' : 'این محصول ناموجود است' }}
                                </button>

                            </div>

                        </form>


                        {{-- Service Cards --}}
                        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">

                            <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                    STOCK
                                </p>

                                <p class="mt-2 text-xs font-medium">
                                    {{ $product->stock > 0 ? 'موجود' : 'ناموجود' }}
                                </p>

                            </div>

                            <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                    PAYMENT
                                </p>

                                <p class="mt-2 text-xs font-medium">
                                    {{ $installmentEnabled ? 'نقدی و اقساطی' : 'پرداخت آنلاین' }}
                                </p>

                            </div>

                            <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                    SKU
                                </p>

                                <p class="mt-2 truncate text-xs font-medium">
                                    {{ $product->sku }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             PRODUCT INFORMATION
        ========================================================== --}}
        <section class="border-t border-[var(--sg-border)] bg-[var(--sg-white)]">

            <x-layout.container>

                <div class="grid gap-10 py-14 lg:grid-cols-[0.75fr_1.25fr] lg:py-20">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                            PRODUCT DETAILS
                        </p>

                        <h2 class="mt-3 text-3xl font-semibold tracking-tight">
                            درباره این محصول
                        </h2>

                        <p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">
                            اطلاعات و مشخصات محصول را بررسی کنید تا انتخاب دقیق‌تری برای فضای خود داشته باشید.
                        </p>

                    </div>

                    <div>

                        @if($product->description)

                            <div class="text-sm leading-9 text-[var(--sg-stone)]">
                                {!! nl2br(e($product->description)) !!}
                            </div>

                        @else

                            <p class="text-sm leading-8 text-[var(--sg-stone)]">
                                توضیحات کامل این محصول هنوز توسط فروشگاه ثبت نشده است.
                            </p>

                        @endif

                    </div>

                </div>

            </x-layout.container>

        </section>


        @if(!empty($product->content_sections) || !empty($product->specifications) || filled($product->care_guide) || !empty($product->faq_items))
            <section class="border-t border-[var(--sg-border)] bg-[var(--sg-white)]">
                <x-layout.container>
                    <div class="space-y-12 py-14 sm:py-18">
                        @foreach($product->content_sections ?? [] as $section)
                            @if(filled($section['heading'] ?? null) && filled($section['body'] ?? null))
                                <article class="max-w-4xl">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">PRODUCT GUIDE</p>
                                    <h2 class="mt-3 text-2xl font-semibold sm:text-3xl">{{ $section['heading'] }}</h2>
                                    <p class="mt-4 whitespace-pre-line text-sm leading-9 text-[var(--sg-stone)]">{{ $section['body'] }}</p>
                                </article>
                            @endif
                        @endforeach

                        @if(!empty($product->specifications))
                            <section class="max-w-4xl">
                                <h2 class="text-2xl font-semibold">مشخصات فنی {{ $product->name }}</h2>
                                <div class="mt-5 overflow-hidden rounded-2xl border border-[var(--sg-border)]">
                                    <table class="w-full text-right text-sm">
                                        <tbody>
                                            @foreach($product->specifications as $specification)
                                                @if(filled($specification['label'] ?? null) && filled($specification['value'] ?? null))
                                                    <tr class="border-b border-[var(--sg-border)] last:border-0">
                                                        <th scope="row" class="w-1/3 bg-[var(--sg-surface)] px-4 py-4 font-semibold">{{ $specification['label'] }}</th>
                                                        <td class="px-4 py-4 leading-7 text-[var(--sg-stone)]">{{ $specification['value'] }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif

                        @if(filled($product->care_guide))
                            <section class="max-w-4xl">
                                <h2 class="text-2xl font-semibold">راهنمای نگهداری و استفاده</h2>
                                <p class="mt-4 whitespace-pre-line text-sm leading-9 text-[var(--sg-stone)]">{{ $product->care_guide }}</p>
                            </section>
                        @endif

                        @if(!empty($product->faq_items))
                            <section class="max-w-4xl">
                                <h2 class="text-2xl font-semibold">سؤالات متداول درباره {{ $product->name }}</h2>
                                <div class="mt-5 space-y-3">
                                    @foreach($product->faq_items as $faq)
                                        @if(filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
                                            <details class="rounded-2xl border border-[var(--sg-border)] p-5">
                                                <summary class="cursor-pointer text-sm font-semibold">{{ $faq['question'] }}</summary>
                                                <p class="mt-3 whitespace-pre-line text-sm leading-8 text-[var(--sg-stone)]">{{ $faq['answer'] }}</p>
                                            </details>
                                        @endif
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </div>
                </x-layout.container>
            </section>
        @endif

        {{-- =========================================================
             PRODUCT FACTS
        ========================================================== --}}
        <section class="border-t border-[var(--sg-border)]">

            <x-layout.container>

                <div class="py-14 sm:py-18">

                    <div class="mb-8">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                            AT A GLANCE
                        </p>

                        <h2 class="mt-3 text-2xl font-semibold">
                            اطلاعات سریع
                        </h2>

                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

                        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                CATEGORY
                            </p>

                            <p class="mt-3 text-sm font-semibold">
                                {{ $product->category?->name ?? '—' }}
                            </p>

                        </div>

                        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                SKU
                            </p>

                            <p class="mt-3 break-all text-sm font-semibold">
                                {{ $product->sku }}
                            </p>

                        </div>

                        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                AVAILABILITY
                            </p>

                            <p class="mt-3 text-sm font-semibold">
                                {{ $product->stock > 0 ? 'موجود در انبار' : 'ناموجود' }}
                            </p>

                        </div>

                        <div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                PAYMENT
                            </p>

                            <p class="mt-3 text-sm font-semibold">
                                {{ $installmentEnabled ? 'قابل خرید اقساطی' : 'پرداخت کامل' }}
                            </p>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSTALLMENT SEO / EXPLANATION
        ========================================================== --}}
        @if($installmentEnabled)

            <section class="border-t border-[var(--sg-border)] bg-[var(--sg-surface)]">

                <x-layout.container>

                    <div class="grid gap-8 py-14 lg:grid-cols-[0.8fr_1.2fr] lg:py-18">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                                INSTALLMENT GUIDE
                            </p>

                            <h2 class="mt-3 text-2xl font-semibold">
                                شرایط خرید اقساطی {{ $product->name }}
                            </h2>

                        </div>

                        <div class="text-sm leading-8 text-[var(--sg-stone)]">

                            <p>
                                این محصول با
                                {{ number_format($cashPercent) }}٪
                                پیش‌پرداخت قابلیت خرید اقساطی دارد.
                                مبلغ پیش‌پرداخت
                                {{ number_format($cashAmount) }}
                                تومان است و باقی‌مانده
                                {{ number_format($deferredAmount) }}
                                تومان در
                                {{ number_format($chequeCount) }}
                                فقره چک برنامه‌ریزی می‌شود.
                            </p>

                            @if($intervalMonths)

                                <p class="mt-4">
                                    فاصله سررسید چک‌ها
                                    {{ number_format($intervalMonths) }}
                                    ماه است و برنامه دقیق پرداخت هنگام Checkout نمایش داده می‌شود.
                                </p>

                            @endif

                        </div>

                    </div>

                </x-layout.container>

            </section>

        @endif


        {{-- =========================================================
             RELATED PRODUCTS
        ========================================================== --}}
        @if($relatedProducts->count())

            <section class="border-t border-[var(--sg-border)]">

                <x-layout.container>

                    <div class="py-14 sm:py-18">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                                    YOU MAY ALSO LIKE
                                </p>

                                <h2 class="mt-3 text-2xl font-semibold">
                                    محصولات مرتبط
                                </h2>

                            </div>

                            @if($product->category)

                                <a
                                    href="{{ route('categories.show', $product->category->slug) }}"
                                    class="text-sm font-medium text-[var(--sg-accent)]"
                                >
                                    مشاهده دسته‌بندی
                                    <span class="mr-1">←</span>
                                </a>

                            @endif

                        </div>

                        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 lg:grid-cols-4">

                            @foreach($relatedProducts as $relatedProduct)

                                <x-product.card
                                    :product="$relatedProduct"
                                />

                            @endforeach

                        </div>

                    </div>

                </x-layout.container>

            </section>

        @endif


        {{-- =========================================================
             MOBILE STICKY CTA
        ========================================================== --}}
        <div class="fixed inset-x-0 bottom-[5.5rem] z-[80] border-y border-[var(--sg-border)] bg-[var(--sg-white)]/95 p-3 shadow-[0_-10px_30px_rgba(24,23,21,.08)] backdrop-blur-xl lg:hidden">

            <x-layout.container>

                <div class="flex items-center gap-3">

                    <div class="min-w-0 flex-1">

                        <p class="truncate text-[11px] text-[var(--sg-stone)]">
                            {{ $product->name }}
                        </p>

                        <p class="mt-1 text-sm font-semibold">
                            {{ number_format($productPrice) }}
                            <span class="text-[10px] font-normal text-[var(--sg-stone)]">
                            تومان
                        </span>
                        </p>

                    </div>

                    <a
                        href="#product-purchase"
                        class="inline-flex shrink-0 items-center rounded-2xl bg-[var(--sg-ink)] px-5 py-3.5 text-xs font-medium text-white"
                    >
                        خرید
                    </a>

                </div>

            </x-layout.container>

        </div>

    </div>

@endsection
