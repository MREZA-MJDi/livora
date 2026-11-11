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

    $variantImageMap = $product->variants
        ->mapWithKeys(fn ($variant) => [
            (string) $variant->id => $variant->images
                ->map(fn ($image) => [
                    'url' => $image->url,
                    'alt' => $image->alt ?: $product->name,
                ])
                ->values()
                ->all(),
        ])
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

    $productProperties = collect($product->key_features ?? [])
        ->merge($product->specifications ?? [])
        ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['value'] ?? null))
        ->map(fn ($item) => [
            '@type' => 'PropertyValue',
            'name' => (string) $item['label'],
            'value' => (string) $item['value'],
        ])
        ->values()
        ->all();

    if ($productProperties !== []) {
        $productStructuredData['additionalProperty'] = $productProperties;
    }

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
        hasSelectedVariant: false
    }"
        @product-variant-selected.window="hasSelectedVariant = true"
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
                            کد محصول:
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


                        @php
                            $visibleFeatures = collect($product->key_features ?? [])
                                ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['value'] ?? null))
                                ->values();
                            $visibleSpecs = collect($product->specifications ?? [])
                                ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['value'] ?? null))
                                ->values();
                            $hasMoreProductInfo = $visibleFeatures->count() > 4
                                || $visibleSpecs->isNotEmpty()
                                || filled($product->description)
                                || !empty($product->content_sections)
                                || filled($product->care_guide)
                                || !empty($product->faq_items);
                        @endphp

                        @if($visibleFeatures->isNotEmpty())
                            <div class="mt-5 flex flex-wrap gap-2" aria-label="ویژگی‌های مهم محصول">
                                @foreach($visibleFeatures->take(4) as $feature)
                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] px-3 py-2 text-[11px] leading-5 text-[var(--sg-ink)]">
                                        <span class="font-semibold">{{ $feature['label'] }}:</span>
                                        <span class="text-[var(--sg-stone)]">{{ $feature['value'] }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if($hasMoreProductInfo)
                            <details class="product-more-info mt-4 overflow-hidden rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)]">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 text-xs font-semibold text-[var(--sg-ink)]">
                                    <span>مشخصات و جزئیات بیشتر</span>
                                    <span class="text-[var(--sg-accent)]">+</span>
                                </summary>
                                <div class="space-y-5 border-t border-[var(--sg-border)] p-4">
                                    @if($visibleFeatures->count() > 4)
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($visibleFeatures->skip(4) as $feature)
                                                <span class="rounded-full bg-[var(--sg-surface)] px-3 py-2 text-[11px] leading-5">
                                                    <strong>{{ $feature['label'] }}:</strong>
                                                    <span class="text-[var(--sg-stone)]">{{ $feature['value'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($visibleSpecs->isNotEmpty())
                                        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            @foreach($visibleSpecs as $specification)
                                                <div class="flex items-start justify-between gap-3 rounded-xl bg-[var(--sg-surface)] px-3 py-2.5 text-[11px]">
                                                    <dt class="shrink-0 font-semibold text-[var(--sg-ink)]">{{ $specification['label'] }}</dt>
                                                    <dd class="text-left leading-5 text-[var(--sg-stone)]">{{ $specification['value'] }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif

                                    @if(filled($product->description))
                                        <div class="text-xs leading-7 text-[var(--sg-stone)]">{{ $product->description }}</div>
                                    @endif

                                    @foreach($product->content_sections ?? [] as $section)
                                        @if(filled($section['heading'] ?? null) && filled($section['body'] ?? null))
                                            <article>
                                                <h3 class="text-xs font-bold">{{ $section['heading'] }}</h3>
                                                <p class="mt-2 whitespace-pre-line text-xs leading-7 text-[var(--sg-stone)]">{{ $section['body'] }}</p>
                                            </article>
                                        @endif
                                    @endforeach

                                    @if(filled($product->care_guide))
                                        <article>
                                            <h3 class="text-xs font-bold">راهنمای نگهداری</h3>
                                            <p class="mt-2 whitespace-pre-line text-xs leading-7 text-[var(--sg-stone)]">{{ $product->care_guide }}</p>
                                        </article>
                                    @endif

                                    @if(!empty($product->faq_items))
                                        <div class="space-y-2">
                                            @foreach($product->faq_items as $faq)
                                                @if(filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
                                                    <details class="rounded-xl border border-[var(--sg-border)] p-3">
                                                        <summary class="cursor-pointer text-xs font-semibold">{{ $faq['question'] }}</summary>
                                                        <p class="mt-2 whitespace-pre-line text-xs leading-7 text-[var(--sg-stone)]">{{ $faq['answer'] }}</p>
                                                    </details>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </details>
                        @endif


                        {{-- Compact installment summary. Full cheque schedule stays optional. --}}
                        @if($installmentEnabled)
                            <section x-show="!hasSelectedVariant"
                                  x-cloak
                                  class="product-installment-summary mt-5 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-3 sm:p-4" aria-label="خلاصه خرید اقساطی">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="product-installment-summary__icon" aria-hidden="true">٪</span>
                                        <h2 class="text-sm font-semibold text-[var(--sg-ink)]">خرید اقساطی</h2>
                                    </div>
                                    <span class="rounded-full bg-[var(--sg-surface)] px-3 py-1.5 text-[10px] font-semibold text-[var(--sg-accent)]">
                                        {{ number_format($cashPercent) }}٪ پیش‌پرداخت
                                    </span>
                                </div>

                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <div class="rounded-xl bg-[var(--sg-surface)] px-2.5 py-3">
                                        <p class="text-[10px] text-[var(--sg-stone)]">پرداخت امروز</p>
                                        <p class="mt-1 text-xs font-bold leading-5 text-[var(--sg-ink)]">{{ number_format($cashAmount) }}</p>
                                        <p class="text-[9px] text-[var(--sg-stone)]">تومان</p>
                                    </div>
                                    <div class="rounded-xl bg-[var(--sg-surface)] px-2.5 py-3">
                                        <p class="text-[10px] text-[var(--sg-stone)]">باقی‌مانده</p>
                                        <p class="mt-1 text-xs font-bold leading-5 text-[var(--sg-ink)]">{{ number_format($deferredAmount) }}</p>
                                        <p class="text-[9px] text-[var(--sg-stone)]">تومان</p>
                                    </div>
                                    <div class="rounded-xl bg-[var(--sg-surface)] px-2.5 py-3">
                                        <p class="text-[10px] text-[var(--sg-stone)]">تسویه</p>
                                        <p class="mt-1 text-xs font-bold leading-5 text-[var(--sg-ink)]">{{ number_format($chequeCount) }} چک</p>
                                        <p class="text-[9px] text-[var(--sg-stone)]">هر {{ number_format($intervalMonths) }} ماه</p>
                                    </div>
                                </div>

                                @if(count($chequeAmounts))
                                    <details class="product-installment-details mt-3">
                                        <summary class="cursor-pointer rounded-xl border border-[var(--sg-border)] px-3 py-2.5 text-xs font-medium text-[var(--sg-ink)]">
                                            مشاهده مبلغ و سررسید چک‌ها
                                        </summary>
                                        <div class="mt-2 space-y-1.5">
                                            @foreach($chequeAmounts as $index => $amount)
                                                <div class="flex items-center justify-between gap-3 rounded-lg bg-[var(--sg-surface)] px-3 py-2.5">
                                                    <span class="text-[11px] text-[var(--sg-stone)]">
                                                        چک {{ number_format($index + 1) }} · {{ number_format(($index + 1) * $intervalMonths) }} ماه بعد
                                                    </span>
                                                    <strong class="text-xs text-[var(--sg-ink)]">{{ number_format($amount) }} تومان</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif
                                <p class="mt-2 text-[10px] leading-5 text-[var(--sg-stone)]">
                                    جزئیات نهایی پرداخت پیش از ثبت سفارش نمایش داده می‌شود.
                                </p>
                            </section>
                        @endif


                        {{-- Product / Cart --}}
                        <form
                            id="product-purchase"
                            data-cart-add-form
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
                                                :type="$type"
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
                                            {{ $variantGroups->isNotEmpty() ? 'پس از انتخاب ویژگی‌ها بررسی می‌شود' : number_format($product->stock) }}
                                        </p>

                                    </div>

                                    <x-shop.quantity
                                        name="quantity"
                                        min="1"
                                        :max="$variantGroups->isNotEmpty() ? 99 : max(1, $product->stock)"
                                    />

                                </div>

                            </div>


                            {{-- CTA --}}
                            <div class="border-t border-[var(--sg-border)] pt-7">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <button
                                        type="submit"
                                        data-cart-add-button
                                        @disabled(!$canPurchase)
                                        class="flex min-h-12 w-full items-center justify-center rounded-2xl bg-[var(--sg-ink)] px-5 py-4 text-sm font-medium text-white transition duration-300 hover:bg-[var(--sg-accent)] disabled:cursor-not-allowed disabled:opacity-40"
                                    >
                                        {{ $canPurchase ? 'افزودن به سبد خرید' : 'این محصول ناموجود است' }}
                                    </button>
                                    <a
                                        href="{{ filled($storeSettings?->whatsapp_url) ? $storeSettings->whatsapp_url : route('contact', ['product' => $product->slug]) }}"
                                        @if(filled($storeSettings?->whatsapp_url)) target="_blank" rel="noopener noreferrer" @endif
                                        class="flex min-h-12 w-full items-center justify-center rounded-2xl border border-[var(--sg-border)] px-5 py-4 text-sm font-medium text-[var(--sg-ink)] transition hover:border-[var(--sg-accent)] hover:text-[var(--sg-accent)]"
                                    >
                                        درخواست مشاوره خرید
                                    </a>
                                </div>
                                <div
                                    data-cart-feedback
                                    role="status"
                                    aria-live="polite"
                                    aria-atomic="true"
                                    class="pointer-events-none fixed bottom-24 right-4 z-[120] hidden max-w-[min(92vw,380px)] translate-y-3 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-5 py-4 text-sm text-[var(--sg-ink)] opacity-0 shadow-[0_18px_55px_rgba(24,23,21,.18)] transition-all duration-300 sm:bottom-8 sm:right-8"
                                ></div>
                                @unless($canPurchase)
                                    <a
                                        href="{{ route('contact', ['product' => $product->slug, 'request' => 'stock-notification']) }}"
                                        class="mt-3 flex min-h-11 items-center justify-center rounded-xl bg-[var(--sg-surface)] px-4 py-3 text-sm font-medium text-[var(--sg-accent)]"
                                    >
                                        درخواست اطلاع از موجودی
                                    </a>
                                @endunless
                            </div>

                        </form>


                        {{-- Service Cards --}}
                        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">

                            <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                    موجودی انبار
                                </p>

                                <p class="mt-2 text-xs font-medium">
                                    {{ $variantGroups->isNotEmpty() ? 'وابسته به گزینه انتخابی' : ($product->stock > 0 ? 'موجود' : 'ناموجود') }}
                                </p>

                            </div>

                            <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-4">

                                <p class="text-[10px] uppercase tracking-[0.16em] text-[var(--sg-stone)]">
                                    روش پرداخت
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
             محصولات مرتبط
        ========================================================== --}}
        @if($relatedProducts->count())

            <section class="border-t border-[var(--sg-border)]">

                <x-layout.container>

                    <div class="py-14 sm:py-18">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                                    شاید این محصولات را هم بپسندید
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
             اقدام سریع خرید
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
