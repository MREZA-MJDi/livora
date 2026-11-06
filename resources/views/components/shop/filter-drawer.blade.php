@props([
'categories' => collect(),
'selectedCategory' => null,
])

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

    {{-- =========================================================
         BACKDROP
    ========================================================== --}}

    <button
        type="button"
        aria-label="بستن فیلترها"
        @click="filterOpen = false"
        class="absolute inset-0 bg-black/35 backdrop-blur-sm"
    ></button>


    {{-- =========================================================
         DRAWER
    ========================================================== --}}

    <aside
        x-show="filterOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 flex h-full w-[90%] max-w-sm flex-col bg-[var(--sg-cream)] shadow-2xl"
    >

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div
            class="flex h-[64px] shrink-0 items-center justify-between border-b border-[var(--sg-border)] bg-[var(--sg-white)] px-5"
        >

            <div>

                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--sg-accent)]">
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
                class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--sg-ink)] transition hover:bg-[var(--sg-surface)]"
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


        {{-- =====================================================
             MOBILE FILTER FORM
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('shop.index') }}"
            class="flex min-h-0 flex-1 flex-col"
            data-live-catalog-filters
            data-results-selector="#shop-live-results"
            data-aux-selectors="#shop-sorting,#shop-product-count,#shop-mobile-product-count"
        >
            <input type="hidden" name="sort" value="{{ request('sort', 'latest') }}">

            {{-- =================================================
                 SCROLL AREA
            ================================================== --}}

            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">

                <x-shop.filters
                    mobile
                    :categories="$categories"
                    :selected-category="$selectedCategory"
                />

            </div>


            <div class="shrink-0 border-t border-[var(--sg-border)] bg-[var(--sg-white)] p-4">
                <p class="text-center text-xs leading-6 text-[var(--sg-stone)]">
                    تغییرات شما خودکار اعمال می‌شوند؛ برای دیدن محصولات نیازی به دکمه ثبت نیست.
                </p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <a href="{{ route('shop.index') }}" class="flex items-center justify-center rounded-2xl border border-[var(--sg-border)] px-3 py-3.5 text-xs font-semibold text-[var(--sg-ink)]">
                        پاک کردن فیلترها
                    </a>
                    <button type="button" @click="filterOpen = false" class="rounded-2xl bg-[var(--sg-ink)] px-3 py-3.5 text-xs font-semibold text-white">
                        بستن فیلترها
                    </button>
                </div>
            </div>

        </form>

    </aside>

</div>
