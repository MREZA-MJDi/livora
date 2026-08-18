@props([
'products',
])

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <p class="text-sm text-[var(--livora-stone)]">
        نمایش
        <span class="font-medium text-[var(--livora-ink)]">
            {{ $products->total() }}
        </span>
        محصول
    </p>

    <form
        method="GET"
        action="{{ route('shop.index') }}"
        class="flex items-center gap-3"
    >

        @foreach(request()->except('sort', 'page') as $key => $value)
            @if(is_array($value))
                @foreach($value as $item)
                    <input
                        type="hidden"
                        name="{{ $key }}[]"
                        value="{{ $item }}"
                    >
                @endforeach
            @else
                <input
                    type="hidden"
                    name="{{ $key }}"
                    value="{{ $value }}"
                >
            @endif
        @endforeach

        <label
            for="sort"
            class="text-sm text-[var(--livora-stone)]"
        >
            مرتب‌سازی:
        </label>

        <select
            id="sort"
            name="sort"
            onchange="this.form.submit()"
            class="rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-2.5 text-sm outline-none focus:border-[var(--livora-accent)]"
        >
            <option value="newest" @selected(request('sort', 'newest') === 'newest')>
            جدیدترین
            </option>

            <option value="popular" @selected(request('sort') === 'popular')>
            محبوب‌ترین
            </option>

            <option value="price_asc" @selected(request('sort') === 'price_asc')>
            ارزان‌ترین
            </option>

            <option value="price_desc" @selected(request('sort') === 'price_desc')>
            گران‌ترین
            </option>

            <option value="name_asc" @selected(request('sort') === 'name_asc')>
            الفبایی
            </option>
        </select>

    </form>

</div>
