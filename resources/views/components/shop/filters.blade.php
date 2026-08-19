@props([
'categories' => collect(),
'selectedCategory' => null,
])

<div class="space-y-5">

    {{-- =========================================================
         CATEGORY
    ========================================================== --}}
    <section>

        <div class="flex items-center justify-between gap-3">

            <div>
                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                    CATEGORY
                </p>

                <h3 class="mt-2 text-sm font-semibold text-[var(--livora-ink)]">
                    دسته‌بندی
                </h3>
            </div>

            @if(request('category'))

                <a
                    href="{{ route('shop.index', request()->except('category')) }}"
                    class="text-[10px] text-[var(--livora-stone)] transition hover:text-[var(--livora-accent)]"
                >
                    حذف
                </a>

            @endif

        </div>


        @if($categories->isNotEmpty())

            <div class="mt-4 space-y-1.5">

                @foreach($categories as $category)

                    <label class="group flex cursor-pointer items-center justify-between gap-3 rounded-2xl px-3 py-3 transition hover:bg-[var(--livora-surface)]">

                        <span class="flex min-w-0 items-center gap-3">

                            <input
                                type="radio"
                                name="category"
                                value="{{ $category->id }}"
                                @checked(
                                (string) request('category')
                                    ===
                                    (string) $category->id
                                )
                                class="h-4 w-4 border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                            >

                            <span class="truncate text-xs text-[var(--livora-ink)]">
                                {{ $category->name }}
                            </span>

                        </span>

                        @if(isset($category->products_count))

                            <span class="shrink-0 text-[10px] text-[var(--livora-stone)]">
                                {{ number_format($category->products_count) }}
                            </span>

                        @endif

                    </label>

                @endforeach

            </div>

        @else

            <p class="mt-4 text-xs leading-6 text-[var(--livora-stone)]">
                دسته‌بندی‌ای برای نمایش وجود ندارد.
            </p>

        @endif

    </section>


    {{-- =========================================================
         SEARCH
    ========================================================== --}}
    <section class="border-t border-[var(--livora-border)] pt-5">

        <div>

            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                SEARCH
            </p>

            <h3 class="mt-2 text-sm font-semibold text-[var(--livora-ink)]">
                جستجو
            </h3>

        </div>

        <div class="mt-4">

            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="نام یا مدل محصول..."
                class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-4 py-3 text-xs text-[var(--livora-ink)] outline-none transition placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-ink)]"
            >

        </div>

    </section>


    {{-- =========================================================
         PAYMENT
    ========================================================== --}}
    <section class="border-t border-[var(--livora-border)] pt-5">

        <div>

            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                PAYMENT
            </p>

            <h3 class="mt-2 text-sm font-semibold text-[var(--livora-ink)]">
                روش خرید
            </h3>

        </div>

        <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl bg-[var(--livora-surface)] px-3 py-3">

            <input
                type="checkbox"
                name="installment"
                value="1"
                @checked(request()->boolean('installment'))
            class="mt-0.5 h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
            >

            <span>

                <span class="block text-xs font-medium text-[var(--livora-ink)]">
                    خرید اقساطی
                </span>

                <span class="mt-1 block text-[10px] leading-5 text-[var(--livora-stone)]">
                    فقط محصولاتی که شرایط خرید اقساطی دارند.
                </span>

            </span>

        </label>

    </section>


    {{-- =========================================================
         PRICE
    ========================================================== --}}
    <section class="border-t border-[var(--livora-border)] pt-5">

        <div>

            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                PRICE
            </p>

            <h3 class="mt-2 text-sm font-semibold text-[var(--livora-ink)]">
                محدوده قیمت
            </h3>

        </div>

        <div class="mt-4 grid grid-cols-2 gap-2">

            <div>

                <label
                    for="min_price"
                    class="mb-2 block text-[10px] text-[var(--livora-stone)]"
                >
                    حداقل
                </label>

                <input
                    id="min_price"
                    type="number"
                    name="min_price"
                    min="0"
                    value="{{ request('min_price') }}"
                    placeholder="0"
                    class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs outline-none transition focus:border-[var(--livora-ink)]"
                >

            </div>

            <div>

                <label
                    for="max_price"
                    class="mb-2 block text-[10px] text-[var(--livora-stone)]"
                >
                    حداکثر
                </label>

                <input
                    id="max_price"
                    type="number"
                    name="max_price"
                    min="0"
                    value="{{ request('max_price') }}"
                    placeholder="100M"
                    class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs outline-none transition focus:border-[var(--livora-ink)]"
                >

            </div>

        </div>

        <p class="mt-2 text-[10px] leading-5 text-[var(--livora-stone)]">
            مبلغ را بدون جداکننده وارد کنید.
        </p>

    </section>


    {{-- =========================================================
         DISCOVERY
    ========================================================== --}}
    <section class="border-t border-[var(--livora-border)] pt-5">

        <div>

            <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                DISCOVER
            </p>

            <h3 class="mt-2 text-sm font-semibold text-[var(--livora-ink)]">
                انتخاب بیشتر
            </h3>

        </div>

        <div class="mt-4 space-y-2">

            <label class="flex cursor-pointer items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-[var(--livora-surface)]">

                <input
                    type="checkbox"
                    name="featured"
                    value="1"
                    @checked(request()->boolean('featured'))
                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                >

                <span class="text-xs text-[var(--livora-ink)]">
                    محصولات ویژه
                </span>

            </label>

            <label class="flex cursor-pointer items-center gap-3 rounded-2xl px-3 py-3 transition hover:bg-[var(--livora-surface)]">

                <input
                    type="checkbox"
                    name="new"
                    value="1"
                    @checked(request()->boolean('new'))
                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                >

                <span class="text-xs text-[var(--livora-ink)]">
                    محصولات جدید
                </span>

            </label>

        </div>

    </section>


    {{-- =========================================================
         RESET
    ========================================================== --}}
    <section class="border-t border-[var(--livora-border)] pt-5">

        <a
            href="{{ route('shop.index') }}"
            class="flex w-full items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3.5 text-xs font-medium text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)] hover:bg-[var(--livora-surface)]"
        >
            پاک کردن همه فیلترها
        </a>

    </section>

</div>
