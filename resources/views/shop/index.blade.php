@extends('layouts.app')

@section('title', 'فروشگاه مبلمان | LIVORA')

@section(
    'description',
    'خرید مبلمان و لوازم خانه از LIVORA؛ مشاهده محصولات جدید، ویژه، تخفیف‌دار و دارای شرایط خرید اقساطی.'
)

@section('canonical', route('shop.index'))

@push('seo')
    <meta property="og:type" content="website">
    <meta property="og:title" content="فروشگاه مبلمان | LIVORA">
    <meta
        property="og:description"
        content="مجموعه مبلمان و لوازم خانه LIVORA را ببینید و محصول مناسب فضای خود را پیدا کنید."
    >
    <meta property="og:url" content="{{ route('shop.index') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="فروشگاه مبلمان | LIVORA">
    <meta
        name="twitter:description"
        content="مجموعه منتخب LIVORA برای خانه‌ای زیباتر و ماندگارتر."
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

@endpush

@section('content')

    <div class="bg-[var(--livora-cream)]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-14">

                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">
                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        فروشگاه
                    </span>
                    </div>

                    <div class="mt-7 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

                        <div class="max-w-3xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                LIVORA COLLECTION
                            </p>

                            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-[var(--livora-ink)] sm:text-5xl">
                                فروشگاه
                            </h1>

                            <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                                مجموعه‌ای منتخب از مبلمان و عناصر خانه برای فضاهایی که قرار است
                                شخصیت و ماندگاری داشته باشند.
                            </p>

                        </div>

                        <div class="text-sm text-[var(--livora-stone)]">
                            {{ number_format($products->total()) }}
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

                <form
                    method="GET"
                    action="{{ route('shop.index') }}"
                >

                    <div class="grid gap-8 py-8 lg:grid-cols-[250px_minmax(0,1fr)] lg:py-12">

                        {{-- =================================================
                             DESKTOP SIDEBAR
                        ================================================== --}}
                        <aside class="hidden lg:block">

                            <div class="sticky top-28 space-y-5">

                                <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                    <div class="flex items-center justify-between gap-3">

                                        <div>
                                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                                DISCOVER
                                            </p>

                                            <h2 class="mt-2 text-sm font-semibold">
                                                فیلترها
                                            </h2>
                                        </div>

                                        <a
                                            href="{{ route('shop.index') }}"
                                            class="text-[11px] text-[var(--livora-stone)] transition hover:text-[var(--livora-accent)]"
                                        >
                                            پاک کردن
                                        </a>

                                    </div>

                                    <div class="mt-6">

                                        <x-shop.filters
                                            :categories="$categories"
                                            :selected-category="request('category')"
                                        />

                                    </div>

                                    <button
                                        type="submit"
                                        class="mt-6 w-full rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                    >
                                        اعمال فیلتر
                                    </button>

                                </div>

                                {{-- SEO / Discovery box --}}
                                <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5">

                                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                        LIVORA
                                    </p>

                                    <h3 class="mt-3 text-sm font-semibold">
                                        انتخاب آگاهانه
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        قیمت، طراحی، موجودی و شرایط خرید اقساطی را
                                        قبل از انتخاب محصول بررسی کنید.
                                    </p>

                                </div>

                            </div>

                        </aside>


                        {{-- =================================================
                             PRODUCTS
                        ================================================== --}}
                        <div class="min-w-0">

                            {{-- Mobile toolbar --}}
                            <div class="mb-5 flex items-center justify-between gap-3 lg:hidden">

                                <button
                                    type="button"
                                    @click="filterOpen = true"
                                    class="inline-flex items-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm font-medium transition hover:border-[var(--livora-ink)]"
                                >
                                    فیلترها
                                </button>

                                <span class="text-xs text-[var(--livora-stone)]">
                                {{ number_format($products->total()) }}
                                محصول
                            </span>

                            </div>


                            {{-- Toolbar --}}
                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 sm:p-5">

                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                    <div>

                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                            CURATED PRODUCTS
                                        </p>

                                        <p class="mt-2 text-sm font-medium">
                                            مجموعه LIVORA
                                        </p>

                                    </div>

                                    <div class="sm:min-w-[220px]">
                                        <x-shop.sorting
                                            :products="$products"
                                        />
                                    </div>

                                </div>

                            </div>


                            {{-- Active category --}}
                            @if(request('category'))

                                <div class="mt-5 flex flex-wrap items-center gap-2">

                                <span class="text-xs text-[var(--livora-stone)]">
                                    فیلتر فعال:
                                </span>

                                    <span class="inline-flex items-center rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-1.5 text-[11px] text-[var(--livora-ink)]">
                                    {{ $categories->firstWhere('id', request('category'))?->name ?? request('category') }}
                                </span>

                                </div>

                            @endif


                            {{-- Product grid --}}
                            @if($products->count())

                                <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 xl:grid-cols-3">

                                    @foreach($products as $product)

                                        <x-product.card
                                            :product="$product"
                                        />

                                    @endforeach

                                </div>


                                {{-- Pagination --}}
                                <div class="mt-14">

                                    {{ $products->withQueryString()->links() }}

                                </div>

                            @else

                                <div class="mt-8 rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-24 text-center">

                                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-sm text-[var(--livora-stone)]">
                                    —
                                </span>

                                    <h2 class="mt-6 text-2xl font-semibold text-[var(--livora-ink)]">
                                        محصولی پیدا نشد
                                    </h2>

                                    <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--livora-stone)]">
                                        فیلترها یا عبارت جستجو را تغییر دهید و دوباره مجموعه محصولات را بررسی کنید.
                                    </p>

                                    <a
                                        href="{{ route('shop.index') }}"
                                        class="mt-7 inline-flex rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                    >
                                        مشاهده همه محصولات
                                    </a>

                                </div>

                            @endif

                        </div>

                    </div>

                </form>

            </x-layout.container>

        </section>

    </div>

@endsection
