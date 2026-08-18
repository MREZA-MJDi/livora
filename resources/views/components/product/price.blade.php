@props([
'product',
])

<div class="flex flex-wrap items-center gap-3">

    <div class="flex items-baseline gap-1.5">

        <span class="text-xl font-semibold text-[var(--livora-ink)]">
            {{ number_format((float) $product->price) }}
        </span>

        <span class="text-xs text-[var(--livora-stone)]">
            تومان
        </span>

    </div>

    @if($product->compare_at_price)

        <span class="text-sm text-[var(--livora-stone)] line-through">
            {{ number_format((float) $product->compare_at_price) }}
        </span>

    @endif

    @if($product->discount_percentage)

        <x-ui.badge variant="accent">
            {{ $product->discount_percentage }}٪ تخفیف
        </x-ui.badge>

    @endif

</div>
