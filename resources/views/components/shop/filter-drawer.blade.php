<div
    x-show="filterOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[80] lg:hidden"
>

    {{-- Backdrop --}}
    <button
        type="button"
        aria-label="بستن فیلترها"
        @click="filterOpen = false"
        class="absolute inset-0 bg-black/35 backdrop-blur-sm"
    ></button>


    {{-- Drawer --}}
    <aside
        x-show="filterOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 flex h-full w-[88%] max-w-md flex-col bg-[var(--livora-cream)] shadow-2xl"
    >

        {{-- Header --}}
        <div class="flex h-[76px] items-center justify-between border-b border-[var(--livora-border)] bg-[var(--livora-white)] px-5">

            <div>

                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    FILTERS
                </p>

                <h2 class="mt-1 text-sm font-semibold">
                    فیلتر محصولات
                </h2>

            </div>

            <button
                type="button"
                aria-label="بستن"
                @click="filterOpen = false"
                class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-surface)]"
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

        </div>


        {{-- Form --}}
        <form
            method="GET"
            action="{{ route('shop.index') }}"
            class="flex min-h-0 flex-1 flex-col"
        >

            {{-- Scroll Area --}}
            <div class="flex-1 overflow-y-auto px-5 py-6">

                <div class="space-y-5">

                    {{-- Category --}}
                    <section class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    CATEGORY
                                </p>

                                <h3 class="mt-2 text-sm font-semibold">
                                    دسته‌بندی
                                </h3>
                            </div>

                        </div>

                        <div class="mt-5">

                            @if(isset($categories) && $categories->isNotEmpty())

                                <div class="space-y-2">

                                    @foreach($categories as $category)

                                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-transparent bg-[var(--livora-surface)] px-4 py-3 transition hover:border-[var(--livora-border)]">

                                            <span class="flex min-w-0 items-center gap-3">

                                                <input
                                                    type="radio"
                                                    name="category"
                                                    value="{{ $category->id }}"
                                                    @checked((string) request('category') === (string) $category->id)
                                                    class="h-4 w-4 border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                                                >

                                                <span class="truncate text-xs text-[var(--livora-ink)]">
                                                    {{ $category->name }}
                                                </span>

                                            </span>

                                            @if(isset($category->products_count))

                                                <span class="text-[10px] text-[var(--livora-stone)]">
                                                    {{ number_format($category->products_count) }}
                                                </span>

                                            @endif

                                        </label>

                                    @endforeach

                                </div>

                            @else

                                <p class="text-xs leading-6 text-[var(--livora-stone)]">
                                    دسته‌بندی‌ای برای نمایش وجود ندارد.
                                </p>

                            @endif

                        </div>

                    </section>


                    {{-- Search --}}
                    <section class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                SEARCH
                            </p>

                            <h3 class="mt-2 text-sm font-semibold">
                                جستجو
                            </h3>
                        </div>

                        <div class="mt-5">

                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="نام یا مدل محصول..."
                                class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-4 py-3.5 text-sm outline-none transition placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-ink)]"
                            >

                        </div>

                    </section>


                    {{-- Installment --}}
                    <section class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                PAYMENT
                            </p>

                            <h3 class="mt-2 text-sm font-semibold">
                                روش خرید
                            </h3>
                        </div>

                        <div class="mt-5 space-y-2">

                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3">

                                <input
                                    type="checkbox"
                                    name="installment"
                                    value="1"
                                    @checked(request()->boolean('installment'))
                                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                                >

                                <span class="text-xs">
                                    فقط محصولات قابل خرید اقساطی
                                </span>

                            </label>

                        </div>

                    </section>


                    {{-- Price --}}
                    <section class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                PRICE
                            </p>

                            <h3 class="mt-2 text-sm font-semibold">
                                محدوده قیمت
                            </h3>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">

                            <div>

                                <label
                                    for="min_price_mobile"
                                    class="mb-2 block text-[10px] text-[var(--livora-stone)]"
                                >
                                    حداقل
                                </label>

                                <input
                                    id="min_price_mobile"
                                    type="number"
                                    name="min_price"
                                    min="0"
                                    value="{{ request('min_price') }}"
                                    placeholder="0"
                                    class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs outline-none focus:border-[var(--livora-ink)]"
                                >

                            </div>

                            <div>

                                <label
                                    for="max_price_mobile"
                                    class="mb-2 block text-[10px] text-[var(--livora-stone)]"
                                >
                                    حداکثر
                                </label>

                                <input
                                    id="max_price_mobile"
                                    type="number"
                                    name="max_price"
                                    min="0"
                                    value="{{ request('max_price') }}"
                                    placeholder="مثلاً 100000000"
                                    class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] px-3 py-3 text-xs outline-none focus:border-[var(--livora-ink)]"
                                >

                            </div>

                        </div>

                    </section>


                    {{-- Status --}}
                    <section class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                        <div>
                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                DISCOVERY
                            </p>

                            <h3 class="mt-2 text-sm font-semibold">
                                محصولات ویژه
                            </h3>
                        </div>

                        <div class="mt-5 space-y-2">

                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3">

                                <input
                                    type="checkbox"
                                    name="featured"
                                    value="1"
                                    @checked(request()->boolean('featured'))
                                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                                >

                                <span class="text-xs">
                                    فقط محصولات ویژه
                                </span>

                            </label>

                            <label class="flex cursor-pointer items-center gap-3 rounded-2xl bg-[var(--livora-surface)] px-4 py-3">

                                <input
                                    type="checkbox"
                                    name="new"
                                    value="1"
                                    @checked(request()->boolean('new'))
                                class="h-4 w-4 rounded border-[var(--livora-border)] text-[var(--livora-ink)] focus:ring-[var(--livora-ink)]"
                                >

                                <span class="text-xs">
                                    فقط محصولات جدید
                                </span>

                            </label>

                        </div>

                    </section>

                </div>

            </div>


            {{-- Bottom Actions --}}
            <div class="border-t border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                <div class="grid grid-cols-2 gap-3">

                    <a
                        href="{{ route('shop.index') }}"
                        @click="filterOpen = false"
                        class="flex items-center justify-center rounded-2xl border border-[var(--livora-border)] px-4 py-3.5 text-xs font-medium text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)]"
                    >
                        پاک کردن
                    </a>

                    <button
                        type="submit"
                        @click="filterOpen = false"
                        class="rounded-2xl bg-[var(--livora-ink)] px-4 py-3.5 text-xs font-medium text-white transition hover:bg-[var(--livora-accent)]"
                    >
                        اعمال فیلتر
                    </button>

                </div>

            </div>

        </form>

    </aside>

</div>
