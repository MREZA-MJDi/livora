@extends('layouts.app')

@section(
    'title',
    $category->meta_title ?: ($category->name . ' | خرید مبلمان و محصولات خانه | SillaGallery')
)

@section(
    'description',
    $category->meta_description
        ?? ('مشاهده و خرید محصولات دسته‌بندی ' . $category->name . ' در SillaGallery؛ بررسی قیمت، مشخصات و شرایط خرید اقساطی.')
)

@section('robots', \App\Models\SeoSetting::current()->robotsFor($category->seo_index, $category->seo_follow))
@section('og_title', $category->meta_title ?: ($category->name . ' | SillaGallery'))
@section('og_description', $category->meta_description ?: ('محصولات ' . $category->name . ' در SillaGallery'))
@section('og_image', $category->og_image ?: $category->image_url)

@if(!empty($category->faq_items))
    @push('seo')
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($category->faq_items)->filter(fn ($item) => filled($item['question'] ?? null) && filled($item['answer'] ?? null))->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
            ])->values()->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    @endpush
@endif

@section(
    'canonical',
    ($category->canonical_url ?: route('categories.show', $category->slug))
)


@section('content')

    <div class="bg-[var(--sg-cream)]">

        {{-- =========================================================
             CATEGORY HERO
        ========================================================== --}}
        <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

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

                        <a
                            href="{{ route('categories.index') }}"
                            class="transition hover:text-[var(--sg-ink)]"
                        >
                            دسته‌بندی‌ها
                        </a>

                        <span>/</span>

                        <span class="text-[var(--sg-ink)]">
                        {{ $category->name }}
                    </span>

                    </nav>

                    <div class="sg-category-detail-hero mt-8 grid items-center gap-8 lg:mt-12 lg:grid-cols-[minmax(0,0.88fr)_minmax(0,1.12fr)] lg:gap-12">
                        <div class="max-w-3xl py-3">
                            <p class="sg-eyebrow">مجموعه محصولات</p>
                            <h1 class="mt-4 text-4xl font-semibold leading-[1.35] tracking-tight text-[var(--sg-ink)] sm:text-5xl lg:text-6xl">
                                {{ $category->banner_title ?: $category->name }}
                            </h1>

                            <p class="mt-5 max-w-2xl text-sm leading-8 text-[var(--sg-stone)] sm:text-base">
                                @if(filled($category->banner_subtitle))
                                    {{ $category->banner_subtitle }}
                                @elseif(filled($category->description))
                                    {{ $category->description }}
                                @else
                                    مجموعه محصولات {{ $category->name }} را بررسی کنید؛ مشخصات، قیمت و شرایط خرید هر محصول را با هم مقایسه کنید.
                                @endif
                            </p>

                            <div class="mt-8 flex flex-wrap items-center gap-3">
                                <span id="category-product-count" class="inline-flex min-h-11 items-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-surface)] px-4 text-xs font-medium text-[var(--sg-ink)]">
                                    {{ number_format($products->total()) }} محصول
                                </span>
                                <a
                                    href="{{ route('shop.index', ['category' => $category->slug]) }}"
                                    class="sg-button sg-button-primary"
                                >
                                    مشاهده همه محصولات
                                    <span aria-hidden="true">←</span>
                                </a>
                            </div>
                        </div>

                        <div class="sg-category-detail-visual relative aspect-[16/7] overflow-hidden rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-surface)]">
                            @if($category->banner_image_url)
                                <img
                                    src="{{ $category->banner_image_url }}"
                                    alt="{{ $category->image_alt ?: $category->name }}"
                                    width="1440"
                                    height="1000"
                                    fetchpriority="high"
                                    decoding="async"
                                    class="h-full w-full object-cover"
                                >
                            @else
                                <div class="sg-category-detail-placeholder flex h-full min-h-[18rem] items-center justify-center">
                                    <span class="sillagallery-wordmark"><span class="sillagallery-wordmark__main">Silla</span><span class="sillagallery-wordmark__sub">Gallery</span></span>
                                </div>
                            @endif
                            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/25 to-transparent" aria-hidden="true"></div>
                        </div>
                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             PRODUCTS
        ========================================================== --}}
        <section>

            <x-layout.container>

                <form method="GET" action="{{ route('categories.show', $category->slug) }}" class="sg-category-filter mb-8" data-live-catalog-filters data-results-selector="#category-live-results" data-aux-selectors="#category-product-count">
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(0,1.5fr)_minmax(10rem,1fr)_minmax(8rem,.8fr)_minmax(8rem,.8fr)_auto]">
                        <label class="min-w-0">
                            <span class="mb-2 block text-[11px] font-medium text-[var(--sg-stone)]">جستجو در این مجموعه</span>
                            <input type="search" name="search" value="{{ request('search') }}" maxlength="150" placeholder="نام یا کد محصول..." class="sg-category-filter__input w-full" autocomplete="off">
                        </label>
                        <label class="min-w-0">
                            <span class="mb-2 block text-[11px] font-medium text-[var(--sg-stone)]">ترتیب نمایش</span>
                            <select name="sort" class="sg-category-filter__input w-full">
                                <option value="latest" @selected(request('sort', 'latest') === 'latest')>جدیدترین</option>
                                <option value="price_asc" @selected(request('sort') === 'price_asc')>ارزان‌ترین</option>
                                <option value="price_desc" @selected(request('sort') === 'price_desc')>گران‌ترین</option>
                                <option value="name_asc" @selected(request('sort') === 'name_asc')>نام محصول</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>قدیمی‌ترین</option>
                            </select>
                        </label>
                        <label class="min-w-0">
                            <span class="mb-2 block text-[11px] font-medium text-[var(--sg-stone)]">حداقل قیمت</span>
                            <input type="number" name="min_price" min="0" step="1" value="{{ request('min_price') }}" placeholder="از" class="sg-category-filter__input w-full" inputmode="numeric">
                        </label>
                        <label class="min-w-0">
                            <span class="mb-2 block text-[11px] font-medium text-[var(--sg-stone)]">حداکثر قیمت</span>
                            <input type="number" name="max_price" min="0" step="1" value="{{ request('max_price') }}" placeholder="تا" class="sg-category-filter__input w-full" inputmode="numeric">
                        </label>
                        <div class="flex items-end">
                            <p class="w-full rounded-xl bg-[var(--sg-surface)] px-3 py-3 text-center text-[11px] leading-6 text-[var(--sg-stone)] sm:col-span-2 xl:col-span-1">
                                نتایج با هر تغییر به‌صورت خودکار به‌روز می‌شوند.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-[var(--sg-border)] pt-4">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--sg-stone)]">
                            <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock')) class="accent-[var(--sg-accent)]">
                            فقط موجودها
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--sg-stone)]">
                            <input type="checkbox" name="installment" value="1" @checked(request()->boolean('installment')) class="accent-[var(--sg-accent)]">
                            خرید اقساطی
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--sg-stone)]">
                            <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured')) class="accent-[var(--sg-accent)]">
                            محصولات ویژه
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--sg-stone)]">
                            <input type="checkbox" name="new" value="1" @checked(request()->boolean('new')) class="accent-[var(--sg-accent)]">
                            محصولات جدید
                        </label>
                        <a href="{{ route('categories.show', $category->slug) }}" class="mr-auto text-xs font-semibold text-[var(--sg-accent)] hover:text-[var(--sg-ink)]">حذف همه فیلترها</a>
                    </div>
                </form>

                <div id="category-live-results" aria-live="polite" aria-busy="false">
                    <div class="py-10 sm:py-14">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                                PRODUCTS
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">
                                محصولات این مجموعه
                            </h2>

                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            class="text-sm font-medium text-[var(--sg-accent)]"
                        >
                            بازگشت به فروشگاه
                            <span class="mr-1">←</span>
                        </a>

                    </div>


                    @if($products->isNotEmpty())

                        <div class="mt-8 sg-product-grid sg-product-grid--category">

                            @foreach($products as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                        <div class="mt-14">
                            {{ $products->withQueryString()->onEachSide(1)->links('pagination.silla') }}
                        </div>

                    @else

                        <div class="mt-8 rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-24 text-center">

                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-sm text-[var(--sg-stone)]">
                                —
                            </div>

                            <h2 class="mt-6 text-2xl font-semibold">
                                محصولی در این دسته پیدا نشد
                            </h2>

                            <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--sg-stone)]">
                                با این عبارت جستجو یا فیلتر، محصولی پیدا نشد. فیلترها را تغییر دهید یا پاک کنید.
                            </p>

                            <a
                                href="{{ route('categories.show', $category->slug) }}"
                                class="mt-7 inline-flex rounded-2xl bg-[var(--sg-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]"
                            >
                                پاک کردن فیلترها
                            </a>

                        </div>

                    @endif

                </div>

                </div>

            </x-layout.container>

        </section>


        @if(!empty($category->content_sections) || !empty($category->comparison_rows) || !empty($category->faq_items))
            <section class="border-t border-[var(--sg-border)] bg-[var(--sg-white)]">
                <x-layout.container>
                    <div class="max-w-5xl space-y-12 py-14 sm:py-18">
                        @foreach($category->content_sections ?? [] as $section)
                            @if(filled($section['heading'] ?? null) && filled($section['body'] ?? null))
                                <article class="max-w-4xl">
                                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">CATEGORY GUIDE</p>
                                    <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $section['heading'] }}</h2>
                                    <p class="mt-4 whitespace-pre-line text-sm leading-9 text-[var(--sg-stone)]">{{ $section['body'] }}</p>
                                </article>
                            @endif
                        @endforeach

                        @if(!empty($category->comparison_rows))
                            <section>
                                <h2 class="text-2xl font-semibold">معیارهای انتخاب {{ $category->name }}</h2>
                                <div class="mt-5 overflow-hidden rounded-2xl border border-[var(--sg-border)]">
                                    <table class="w-full text-right text-sm">
                                        <tbody>
                                            @foreach($category->comparison_rows as $row)
                                                @if(filled($row['label'] ?? null) && filled($row['value'] ?? null))
                                                    <tr class="border-b border-[var(--sg-border)] last:border-0">
                                                        <th scope="row" class="w-1/3 bg-[var(--sg-surface)] px-4 py-4 font-semibold">{{ $row['label'] }}</th>
                                                        <td class="px-4 py-4 leading-7 text-[var(--sg-stone)]">{{ $row['value'] }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif

                        @if(!empty($category->faq_items))
                            <section>
                                <h2 class="text-2xl font-semibold">سؤالات متداول {{ $category->name }}</h2>
                                <div class="mt-5 space-y-3">
                                    @foreach($category->faq_items as $faq)
                                        @if(filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
                                            <details class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)] p-5">
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

        @if($category->children->isNotEmpty() || $category->relatedCategories->isNotEmpty())
            @if($category->children->isNotEmpty())
                <section class="border-t border-[var(--sg-border)]">
                    <x-layout.container><div class="py-12"><h2 class="text-2xl font-semibold">زیر‌دسته‌ها</h2><div class="sg-category-grid sg-category-grid--index mt-6 grid gap-5">@foreach($category->children as $child)<x-shop.category-card :name="$child->name" :image="$child->homepage_image_url" :href="route('categories.show', $child->slug)" :count="$child->products_count ?? null" />@endforeach</div></div></x-layout.container>
                </section>
            @endif
            <section class="border-t border-[var(--sg-border)]">
                <x-layout.container>
                    <div class="py-12">
                        <h2 class="text-2xl font-semibold">دسته‌بندی‌های مرتبط</h2>
                        <div class="sg-category-grid sg-category-grid--index mt-6 grid gap-5">
                            @foreach($category->relatedCategories as $relatedCategory)
                                <x-shop.category-card
                                    :name="$relatedCategory->name"
                                     :image="$relatedCategory->homepage_image_url"
                                    :href="route('categories.show', $relatedCategory->slug)"
                                    :count="$relatedCategory->products_count"
                                />
                            @endforeach
                        </div>
                    </div>
                </x-layout.container>
            </section>
        @endif

        @if($category->recommendedProducts->isNotEmpty())
            <section class="border-t border-[var(--sg-border)] bg-[var(--sg-surface)]">
                <x-layout.container>
                    <div class="py-12">
                        <h2 class="text-2xl font-semibold">محصولات پیشنهادی این مجموعه</h2>
                        <div class="sg-product-grid sg-product-grid--category mt-6">
                            @foreach($category->recommendedProducts as $relatedProduct)
                                <x-product.card :product="$relatedProduct" />
                            @endforeach
                        </div>
                    </div>
                </x-layout.container>
            </section>
        @endif

        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}
        <section class="border-t border-[var(--sg-border)] bg-[var(--sg-surface)]">

            <x-layout.container>

                <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--sg-accent)]">
                            SillaGallery
                        </p>

                        <h2 class="mt-2 text-2xl font-semibold">
                            هنوز انتخابت را پیدا نکردی؟
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">
                            مجموعه کامل فروشگاه را بررسی کن.
                        </p>

                    </div>

                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex w-fit rounded-2xl bg-[var(--sg-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]"
                    >
                        مشاهده فروشگاه
                    </a>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
