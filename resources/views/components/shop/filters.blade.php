@props([
'categories' => collect(),
'selectedCategory' => null,
'minPrice' => request('min_price'),
'maxPrice' => request('max_price'),
'inStock' => request()->boolean('in_stock'),
])

<div class="space-y-0">

    <div class="border-b border-[var(--livora-border)] pb-6">

        <h3 class="text-sm font-semibold text-[var(--livora-ink)]">
            محدوده قیمت
        </h3>

        <div class="mt-5 grid grid-cols-2 gap-3">

            <input
                type="number"
                name="min_price"
                value="{{ $minPrice }}"
                placeholder="حداقل"
                class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-2.5 text-sm outline-none focus:border-[var(--livora-accent)]"
            >

            <input
                type="number"
                name="max_price"
                value="{{ $maxPrice }}"
                placeholder="حداکثر"
                class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-2.5 text-sm outline-none focus:border-[var(--livora-accent)]"
            >

        </div>

    </div>


    <div class="border-b border-[var(--livora-border)] py-6">

        <h3 class="text-sm font-semibold text-[var(--livora-ink)]">
            دسته‌بندی
        </h3>

        <div class="mt-4 space-y-3">

            @foreach($categories as $category)

                <label class="flex cursor-pointer items-center justify-between gap-3 text-sm text-[var(--livora-stone)]">

                    <span class="flex items-center gap-3">

                        <input
                            type="radio"
                            name="category"
                            value="{{ $category->slug }}"
                            @checked($selectedCategory === $category->slug)
                            class="h-4 w-4 accent-[var(--livora-accent)]"
                        >

                        {{ $category->name }}

                    </span>

                    <span class="text-xs">
                        {{ $category->products_count }}
                    </span>

                </label>

            @endforeach

        </div>

    </div>


    <div class="py-6">

        <label class="flex cursor-pointer items-center gap-3 text-sm text-[var(--livora-stone)]">

            <input
                type="checkbox"
                name="in_stock"
                value="1"
                @checked($inStock)
                class="h-4 w-4 accent-[var(--livora-accent)]"
            >

            فقط کالاهای موجود

        </label>

    </div>

</div>
