@props([
'product',
])

@php
    $image = $product->images?->first()?->url;

    $hasDiscount =
        $product->compare_at_price !== null
        && (float) $product->compare_at_price > (float) $product->price;

    $discountPercent = $hasDiscount
        ? round(
            (
                ((float) $product->compare_at_price - (float) $product->price)
                / (float) $product->compare_at_price
            ) * 100
        )
        : 0;

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

    $cashAmount =
        $installmentEnabled && $cashPercent > 0
            ? round(
                (float) $product->price
                * ($cashPercent / 100),
                2
            )
            : 0;
@endphp

<article class="product-card group relative flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-[var(--sg-border)] bg-[var(--sg-white)] shadow-sm transition duration-300 hover:-translate-y-1 hover:border-[var(--sg-accent)]/40 hover:shadow-lg" aria-labelledby="product-card-title-{{ $product->id }}">

    {{-- Image --}}
    <div class="relative overflow-hidden bg-[var(--sg-surface)]" data-card-media>

        <a
            href="{{ route('product.show', $product->slug) }}"
            class="product-card-media relative block aspect-[4/5] overflow-hidden"
        >

            @if($image)

                <span class="sg-card-image-skeleton sg-skeleton absolute inset-0" aria-hidden="true"></span>
                <img
                    src="{{ $image }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    decoding="async"
                    data-card-image
                    class="product-card-image h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.045]"
                >
                <span data-card-image-fallback class="hidden h-full w-full items-center justify-center text-xs tracking-[0.2em] text-[var(--sg-stone)]" aria-hidden="true">SillaGallery</span>

            @else

                <div class="flex h-full w-full items-center justify-center">
                    <span class="text-xs tracking-[0.2em] text-[var(--sg-stone)]">
                        SillaGallery
                    </span>
                </div>

            @endif

        </a>

        {{-- Gradient --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/25 to-transparent opacity-0 transition duration-500 group-hover:opacity-100"></div>

        {{-- Badges --}}
        <div class="absolute left-3 top-3 flex flex-col gap-2">

            @if($product->is_new)

                <span class="rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-medium text-[var(--sg-ink)] backdrop-blur">
                    جدید
                </span>

            @endif

            @if($hasDiscount)

                <span class="rounded-full bg-[var(--sg-accent)] px-3 py-1.5 text-[10px] font-medium text-white">
                    {{ number_format($discountPercent) }}٪
                </span>

            @endif

        </div>

        {{-- Installment Badge --}}
        @if($installmentEnabled)

            <div class="absolute right-3 top-3">

                <span class="rounded-full border border-white/30 bg-[var(--sg-ink)]/90 px-3 py-1.5 text-[10px] font-medium text-white backdrop-blur">
                    اقساطی
                </span>

            </div>

        @endif

        {{-- Wishlist --}}
        @auth

            @if(auth()->user()->isCustomer())

                <form
                    action="{{ route('account.wishlist.store', $product) }}"
                    method="POST"
                    class="absolute bottom-3 right-3 transition duration-300"
                >
                    @csrf

                    <button
                        type="submit"
                        aria-label="افزودن به علاقه‌مندی‌ها"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white shadow-sm backdrop-blur-xl transition hover:bg-white hover:text-[var(--sg-ink)] focus-visible:bg-white focus-visible:text-[var(--sg-ink)]"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-[18px] w-[18px]"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                            />
                        </svg>
                    </button>
                </form>

            @endif

        @endauth

    </div>


    {{-- Product Information --}}
    <div class="product-card-content flex flex-1 flex-col p-4 sm:p-5">

        {{-- Category --}}
        @if($product->category)

            <p class="product-card-category min-h-4 line-clamp-1 text-[10px] text-[var(--sg-stone)]">
                <span class="font-medium text-[var(--sg-accent)]">دسته‌بندی:</span>
                <span class="mr-1 uppercase tracking-[0.12em]">{{ $product->category->name }}</span>
            </p>

        @else
            <p class="product-card-category min-h-4" aria-hidden="true"></p>
        @endif

        {{-- Name --}}
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="mt-2 block"
        >
            <h3 id="product-card-title-{{ $product->id }}" class="min-h-[3rem] line-clamp-2 text-sm font-semibold leading-6 text-[var(--sg-ink)] transition duration-300 group-hover:text-[var(--sg-accent)] sm:text-[15px]">
                {{ $product->name }}
            </h3>
        </a>

        @if(filled($product->short_description))
            <p class="mt-2 min-h-10 line-clamp-2 text-xs leading-6 text-[var(--sg-stone)]">{{ $product->short_description }}</p>
        @else
            <p class="mt-2 min-h-10" aria-hidden="true"></p>
        @endif

        {{-- Price --}}
        <div class="product-card-price mt-3 min-h-10 flex-col">
            <span class="text-[10px] font-medium text-[var(--sg-stone)]">قیمت محصول</span>

            @if($hasDiscount)

                <div class="flex flex-wrap items-center gap-2">

                    <span class="text-sm font-semibold text-[var(--sg-ink)]">
                        {{ number_format((float) $product->price) }}
                        <span class="text-[10px] font-normal text-[var(--sg-stone)]">
                            تومان
                        </span>
                    </span>

                    <span class="text-xs text-[var(--sg-stone)] line-through">
                        {{ number_format((float) $product->compare_at_price) }}
                    </span>

                </div>

            @else

                <p class="text-sm font-semibold text-[var(--sg-ink)]">
                    {{ number_format((float) $product->price) }}
                    <span class="text-[10px] font-normal text-[var(--sg-stone)]">
                        تومان
                    </span>
                </p>

            @endif

        </div>

        {{-- Installment --}}
        @if($installmentEnabled && $cashAmount > 0)

            <section aria-label="شرایط خرید اقساطی" class="mt-3 min-h-[74px] rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-surface)] p-3">

                <p class="mb-3 text-[10px] font-semibold text-[var(--sg-accent)]">شرایط خرید اقساطی</p>
                <div class="flex items-center justify-between gap-3">

                    <div>

                        <p class="text-[10px] text-[var(--sg-stone)]">
                            پیش‌پرداخت
                        </p>

                        <p class="mt-1 text-xs font-semibold text-[var(--sg-ink)]">
                            {{ number_format($cashAmount) }}
                            تومان
                        </p>

                    </div>

                    <div class="text-left">

                        <p class="text-[10px] text-[var(--sg-stone)]">
                            شرایط
                        </p>

                        <p class="mt-1 text-xs font-medium text-[var(--sg-accent)]">
                            {{ number_format($cashPercent) }}٪
                            @if($chequeCount)
                                + {{ number_format($chequeCount) }} چک
                            @endif
                        </p>

                    </div>

                </div>

            </section>

        @else
            <div class="product-card-installment-placeholder mt-3 min-h-[74px]" aria-hidden="true"></div>

        @endif

        {{-- CTA --}}
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="mt-auto mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[var(--sg-border)] bg-[var(--sg-surface)] px-4 py-3 text-xs font-semibold text-[var(--sg-ink)] transition duration-300 hover:border-[var(--sg-ink)] hover:bg-[var(--sg-ink)] hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sg-accent)]"
        >
            مشاهده محصول
            <span class="mr-2 transition-transform duration-300 group-hover:-translate-x-1">
                ←
            </span>
        </a>

    </div>

</article>
