@props([
'product',
])

@php
    $images = $product->images ?? collect();

    $activeImage = $images->first();

    $mainImageUrl = $activeImage?->url;

    $productName = $product->name;

    $imageCount = $images->count();
@endphp

<div
    x-data="{
        active: 0,
        zoom: false,

        images: @js(
            $images->map(function ($image) {
                return [
                    'url' => $image->url,
                    'alt' => $image->alt_text ?? $product->name,
                ];
            })->values()
        ),

        next() {
            if (!this.images.length) return;

            this.active =
                (this.active + 1) % this.images.length;
        },

        previous() {
            if (!this.images.length) return;

            this.active =
                (this.active - 1 + this.images.length)
                % this.images.length;
        }
    }"
    class="w-full"
>

    {{-- =========================================================
         MAIN IMAGE
    ========================================================== --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-[var(--livora-surface)]">

        @if($imageCount > 0)

            <div
                class="relative aspect-[4/5] w-full cursor-zoom-in overflow-hidden"
                @click="zoom = true"
            >

                {{-- Images --}}
                @foreach($images as $index => $image)

                    <img
                        x-show="active === {{ $index }}"
                        x-transition:enter="transition duration-500"
                        x-transition:enter-start="opacity-0 scale-[1.02]"
                        x-transition:enter-end="opacity-100 scale-100"
                        src="{{ $image->url }}"
                        alt="{{ $image->alt_text ?? $productName }}"
                        @if($index !== 0)
                        loading="lazy"
                        @endif
                        class="absolute inset-0 h-full w-full object-cover"
                    >

                @endforeach


                {{-- Image Counter --}}
                @if($imageCount > 1)

                    <div class="absolute right-4 top-4 z-10">

                        <span class="rounded-full border border-white/20 bg-black/30 px-3 py-1.5 text-[10px] text-white backdrop-blur-xl">
                            <span x-text="active + 1"></span>
                            /
                            {{ $imageCount }}
                        </span>

                    </div>

                @endif


                {{-- Previous --}}
                @if($imageCount > 1)

                    <button
                        type="button"
                        aria-label="تصویر قبلی"
                        @click.stop="previous()"
                        class="absolute right-4 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/25 text-white opacity-0 backdrop-blur-xl transition duration-300 hover:bg-white hover:text-[var(--livora-ink)] group-hover:opacity-100"
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
                                d="m15.75 19.5-7.5-7.5 7.5-7.5"
                            />
                        </svg>

                    </button>


                    {{-- Next --}}
                    <button
                        type="button"
                        aria-label="تصویر بعدی"
                        @click.stop="next()"
                        class="absolute left-4 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/25 text-white opacity-0 backdrop-blur-xl transition duration-300 hover:bg-white hover:text-[var(--livora-ink)] group-hover:opacity-100"
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
                                d="m8.25 4.5 7.5 7.5-7.5 7.5"
                            />
                        </svg>

                    </button>

                @endif


                {{-- Bottom Gradient --}}
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-black/25 to-transparent"></div>


                {{-- Zoom Hint --}}
                <div class="absolute bottom-4 right-4 z-10 flex items-center gap-2 rounded-full border border-white/20 bg-black/25 px-3 py-2 text-[10px] text-white backdrop-blur-xl">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-3.5 w-3.5"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m15 15 4.5 4.5m-3-7.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm-4.5-2v4m-2-2h4"
                        />
                    </svg>

                    بزرگ‌نمایی

                </div>

            </div>

        @else

            <div class="flex aspect-[4/5] w-full items-center justify-center">

                <div class="text-center">

                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)]">
                        <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                            LV
                        </span>
                    </div>

                    <p class="mt-4 text-xs text-[var(--livora-stone)]">
                        تصویر محصول موجود نیست
                    </p>

                </div>

            </div>

        @endif

    </div>


    {{-- =========================================================
         THUMBNAILS
    ========================================================== --}}
    @if($imageCount > 1)

        <div class="mt-4 flex gap-3 overflow-x-auto pb-1">

            @foreach($images as $index => $image)

                <button
                    type="button"
                    @click="active = {{ $index }}"
                    :aria-current="active === {{ $index }} ? 'true' : 'false'"
                    aria-label="نمایش تصویر {{ $index + 1 }}"
                    class="group relative h-20 w-16 shrink-0 overflow-hidden rounded-2xl bg-[var(--livora-surface)] transition sm:h-24 sm:w-20"
                    :class="active === {{ $index }}
                        ? 'ring-2 ring-[var(--livora-ink)] ring-offset-2 ring-offset-[var(--livora-cream)]'
                        : 'opacity-60 hover:opacity-100'"
                >

                    <img
                        src="{{ $image->url }}"
                        alt="{{ $image->alt_text ?? $productName }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                    >

                </button>

            @endforeach

        </div>

    @endif


    {{-- =========================================================
         IMAGE META
    ========================================================== --}}
    <div class="mt-4 flex items-center justify-between gap-4">

        <div>

            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                PRODUCT GALLERY
            </p>

            <p class="mt-1 text-xs text-[var(--livora-stone)]">
                {{ $imageCount > 0 ? number_format($imageCount) . ' تصویر' : 'بدون تصویر' }}
            </p>

        </div>

        @if($product->sku)

            <span class="text-[10px] text-[var(--livora-stone)]">
                SKU: {{ $product->sku }}
            </span>

        @endif

    </div>


    {{-- =========================================================
         LIGHTBOX
    ========================================================== --}}
    <template x-teleport="body">

        <div
            x-show="zoom"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[100] bg-black/85 p-4 backdrop-blur-md sm:p-8"
            @keydown.escape.window="zoom = false"
        >

            <button
                type="button"
                aria-label="بستن تصویر"
                @click="zoom = false"
                class="absolute right-4 top-4 z-20 flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-xl transition hover:bg-white hover:text-[var(--livora-ink)] sm:right-7 sm:top-7"
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
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>

            </button>


            <div class="flex h-full items-center justify-center">

                <div class="relative max-h-full max-w-6xl">

                    <template
                        x-for="(image, index) in images"
                        :key="index"
                    >

                        <img
                            x-show="active === index"
                            x-transition.opacity
                            :src="image.url"
                            :alt="image.alt"
                            class="max-h-[88vh] max-w-full rounded-2xl object-contain shadow-2xl"
                        >

                    </template>


                    @if($imageCount > 1)

                        <button
                            type="button"
                            aria-label="تصویر قبلی"
                            @click="previous()"
                            class="absolute right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white backdrop-blur-xl transition hover:bg-white hover:text-[var(--livora-ink)] sm:right-5"
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
                                    d="m15.75 19.5-7.5-7.5 7.5-7.5"
                                />
                            </svg>

                        </button>


                        <button
                            type="button"
                            aria-label="تصویر بعدی"
                            @click="next()"
                            class="absolute left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white backdrop-blur-xl transition hover:bg-white hover:text-[var(--livora-ink)] sm:left-5"
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
                                    d="m8.25 4.5 7.5 7.5-7.5 7.5"
                                />

                            </svg>

                        </button>

                    @endif

                </div>

            </div>

        </div>

    </template>

</div>
