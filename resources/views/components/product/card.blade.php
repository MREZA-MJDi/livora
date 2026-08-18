@props([
'product',
])

@php
    $image = $product->images->first();

    $imageUrl = $image?->url;

    $price = number_format((float) $product->price);

    $oldPrice = $product->compare_at_price
        ? number_format((float) $product->compare_at_price)
        : null;
@endphp

<article class="group relative">

    <div class="relative overflow-hidden rounded-2xl bg-[var(--livora-white)]">

        <a
            href="{{ route('product.show', $product->slug) }}"
            class="block aspect-[4/5] overflow-hidden"
        >
            @if($imageUrl)
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $image?->alt ?: $product->name }}"
                    loading="lazy"
                    class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
                >
            @else
                <div class="flex h-full w-full items-center justify-center text-sm text-[var(--livora-stone)]">
                    بدون تصویر
                </div>
            @endif
        </a>

        @if($product->is_new)
            <x-ui.badge
                variant="accent"
                class="absolute right-4 top-4"
            >
                جدید
            </x-ui.badge>
        @elseif($product->discount_percentage)
            <x-ui.badge
                variant="accent"
                class="absolute right-4 top-4"
            >
                {{ $product->discount_percentage }}٪ تخفیف
            </x-ui.badge>
        @endif

        @auth
            <form
                action="{{ route('account.wishlist.store', $product) }}"
                method="POST"
            >
                @csrf

                <button
                    type="submit"
                    aria-label="افزودن به علاقه‌مندی‌ها"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-[var(--livora-white)]/90 text-[var(--livora-ink)] opacity-0 shadow-sm backdrop-blur-sm transition-all duration-300 hover:text-[var(--livora-accent)] group-hover:opacity-100"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-5 w-5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                        />
                    </svg>
                </button>
            </form>
        @endauth

        <form
            action="{{ route('cart.add', $product) }}"
            method="POST"
            class="absolute bottom-4 left-4 right-4 translate-y-3 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100"
        >
            @csrf

            <input
                type="hidden"
                name="quantity"
                value="1"
            >

            <button
                type="submit"
                class="w-full rounded-xl bg-[var(--livora-ink)] px-4 py-3 text-sm font-medium text-white transition-colors duration-300 hover:bg-[var(--livora-accent)]"
                @if($product->stock < 1) disabled @endif
            >
                {{ $product->stock > 0 ? 'افزودن به سبد' : 'ناموجود' }}
            </button>
        </form>

    </div>

    <div class="mt-4">

        <p class="text-xs text-[var(--livora-stone)]">
            {{ $product->category?->name }}
        </p>

        <a
            href="{{ route('product.show', $product->slug) }}"
            class="mt-1 block text-base font-medium text-[var(--livora-ink)] transition-colors duration-300 hover:text-[var(--livora-accent)]"
        >
            {{ $product->name }}
        </a>

        <div class="mt-2 flex items-center gap-2">

            <span class="text-sm font-semibold text-[var(--livora-ink)]">
                {{ $price }}
                تومان
            </span>

            @if($oldPrice)
                <span class="text-xs text-[var(--livora-stone)] line-through">
                    {{ $oldPrice }}
                </span>
            @endif

        </div>

    </div>

</article>
