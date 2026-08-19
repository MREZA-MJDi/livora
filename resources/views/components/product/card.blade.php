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

<article class="group relative min-w-0">

    {{-- Image --}}
    <div class="relative overflow-hidden rounded-[1.5rem] bg-[var(--livora-surface)]">

        <a
            href="{{ route('product.show', $product->slug) }}"
            class="block aspect-[4/5] overflow-hidden"
        >

            @if($image)

                <img
                    src="{{ $image }}"
                    alt="{{ $product->name }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.045]"
                >

            @else

                <div class="flex h-full w-full items-center justify-center">
                    <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                        LIVORA
                    </span>
                </div>

            @endif

        </a>

        {{-- Gradient --}}
        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/25 to-transparent opacity-0 transition duration-500 group-hover:opacity-100"></div>

        {{-- Badges --}}
        <div class="absolute left-3 top-3 flex flex-col gap-2">

            @if($product->is_new)

                <span class="rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-medium text-[var(--livora-ink)] backdrop-blur">
                    جدید
                </span>

            @endif

            @if($hasDiscount)

                <span class="rounded-full bg-[var(--livora-accent)] px-3 py-1.5 text-[10px] font-medium text-white">
                    {{ number_format($discountPercent) }}٪
                </span>

            @endif

        </div>

        {{-- Installment Badge --}}
        @if($installmentEnabled)

            <div class="absolute right-3 top-3">

                <span class="rounded-full border border-white/30 bg-[var(--livora-ink)]/90 px-3 py-1.5 text-[10px] font-medium text-white backdrop-blur">
                    اقساطی
                </span>

            </div>

        @endif

        {{-- Wishlist --}}
        @auth

            @if(auth()->user()->isCustomer())

                <form
                    action="{{ route('wishlist.store', $product) }}"
                    method="POST"
                    class="absolute bottom-3 right-3 opacity-0 transition duration-300 group-hover:opacity-100"
                >
                    @csrf

                    <button
                        type="submit"
                        aria-label="افزودن به علاقه‌مندی‌ها"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 bg-black/25 text-white backdrop-blur-xl transition hover:bg-white hover:text-[var(--livora-ink)]"
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
    <div class="pt-4">

        {{-- Category --}}
        @if($product->category)

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                {{ $product->category->name }}
            </p>

        @endif

        {{-- Name --}}
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="mt-2 block"
        >
            <h3 class="line-clamp-2 text-sm font-semibold leading-6 text-[var(--livora-ink)] transition duration-300 group-hover:text-[var(--livora-accent)] sm:text-[15px]">
                {{ $product->name }}
            </h3>
        </a>

        {{-- Price --}}
        <div class="mt-3">

            @if($hasDiscount)

                <div class="flex flex-wrap items-center gap-2">

                    <span class="text-sm font-semibold text-[var(--livora-ink)]">
                        {{ number_format((float) $product->price) }}
                        <span class="text-[10px] font-normal text-[var(--livora-stone)]">
                            تومان
                        </span>
                    </span>

                    <span class="text-xs text-[var(--livora-stone)] line-through">
                        {{ number_format((float) $product->compare_at_price) }}
                    </span>

                </div>

            @else

                <p class="text-sm font-semibold text-[var(--livora-ink)]">
                    {{ number_format((float) $product->price) }}
                    <span class="text-[10px] font-normal text-[var(--livora-stone)]">
                        تومان
                    </span>
                </p>

            @endif

        </div>

        {{-- Installment --}}
        @if($installmentEnabled && $cashAmount > 0)

            <div class="mt-3 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-3">

                <div class="flex items-center justify-between gap-3">

                    <div>

                        <p class="text-[10px] text-[var(--livora-stone)]">
                            پیش‌پرداخت
                        </p>

                        <p class="mt-1 text-xs font-semibold text-[var(--livora-ink)]">
                            {{ number_format($cashAmount) }}
                            تومان
                        </p>

                    </div>

                    <div class="text-left">

                        <p class="text-[10px] text-[var(--livora-stone)]">
                            شرایط
                        </p>

                        <p class="mt-1 text-xs font-medium text-[var(--livora-accent)]">
                            {{ number_format($cashPercent) }}٪
                            @if($chequeCount)
                                + {{ number_format($chequeCount) }} چک
                            @endif
                        </p>

                    </div>

                </div>

            </div>

        @endif

        {{-- CTA --}}
        <a
            href="{{ route('product.show', $product->slug) }}"
            class="mt-4 inline-flex items-center text-xs font-medium text-[var(--livora-ink)] transition duration-300 group-hover:text-[var(--livora-accent)]"
        >
            مشاهده محصول
            <span class="mr-2 transition-transform duration-300 group-hover:-translate-x-1">
                ←
            </span>
        </a>

    </div>

</article>
