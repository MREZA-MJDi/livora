@props([
'product',
])

@php
    $images = $product->images;
@endphp

<div
    x-data="{
        images: @js($images->pluck('url')->values()),
        activeImage: 0,

        next() {
            if (!this.images.length) return;
            this.activeImage = (this.activeImage + 1) % this.images.length;
        },

        previous() {
            if (!this.images.length) return;
            this.activeImage =
                (this.activeImage - 1 + this.images.length) % this.images.length;
        },

        select(index) {
            this.activeImage = index;
        }
    }"
    class="grid gap-4 lg:grid-cols-[88px_minmax(0,1fr)]"
>

    <div class="order-2 flex gap-3 overflow-x-auto lg:order-1 lg:flex-col">

        @forelse($images as $index => $image)

            <button
                type="button"
                @click="select({{ $index }})"
                class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border-2 transition-all"
                :class="activeImage === {{ $index }}
                    ? 'border-[var(--livora-accent)]'
                    : 'border-[var(--livora-border)]'"
            >

                <img
                    src="{{ $image->url }}"
                    alt="{{ $image->alt ?: $product->name }}"
                    class="h-full w-full object-cover"
                >

            </button>

        @empty

            <div class="flex h-20 w-20 items-center justify-center rounded-xl bg-[var(--livora-white)] text-xs text-[var(--livora-stone)]">
                بدون تصویر
            </div>

        @endforelse

    </div>


    <div class="order-1 relative overflow-hidden rounded-2xl bg-[var(--livora-white)] lg:order-2">

        @if($images->count())

            <div class="relative aspect-[4/5]">

                <template x-for="(image, index) in images" :key="index">

                    <img
                        x-show="activeImage === index"
                        x-transition
                        :src="image"
                        :alt="'{{ $product->name }}'"
                        class="absolute inset-0 h-full w-full object-cover"
                    >

                </template>

            </div>

        @else

            <div class="flex aspect-[4/5] items-center justify-center">
                <span class="text-sm text-[var(--livora-stone)]">
                    تصویر محصول موجود نیست
                </span>
            </div>

        @endif

    </div>

</div>
