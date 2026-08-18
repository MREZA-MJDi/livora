<div
    x-show="searchOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] bg-[var(--livora-ink)]/40 backdrop-blur-sm"
>

    {{-- Backdrop --}}
    <div
        class="absolute inset-0"
        @click="searchOpen = false"
    ></div>


    {{-- Search Panel --}}
    <div
        x-show="searchOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-4 opacity-0"
        class="relative z-10 w-full border-b border-[var(--livora-border)] bg-[var(--livora-cream)] shadow-2xl"
    >

        <x-layout.container>

            <div class="py-6">

                <div class="flex items-center justify-between gap-4">

                    <div>
                        <p class="text-xs font-medium tracking-[0.12em] text-[var(--livora-accent)]">
                            LIVORA SEARCH
                        </p>

                        <h2 class="mt-1 text-lg font-semibold text-[var(--livora-ink)]">
                            جستجو در محصولات
                        </h2>
                    </div>


                    <button
                        type="button"
                        aria-label="بستن جستجو"
                        @click="searchOpen = false"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-accent)]"
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


                <form
                    action="{{ route('shop.index') }}"
                    method="GET"
                    class="relative mt-6"
                    @submit="searchOpen = false"
                >

                    <input
                        x-ref="searchInput"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="نام محصول، SKU یا توضیحات..."
                        autocomplete="off"
                        class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-5 py-4 pl-14 text-sm text-[var(--livora-ink)] outline-none transition focus:border-[var(--livora-accent)] focus:ring-2 focus:ring-[var(--livora-accent)]/10"
                    >


                    <button
                        type="submit"
                        aria-label="جستجو"
                        class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)] hover:text-[var(--livora-accent)]"
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
                                d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                            />
                        </svg>
                    </button>

                </form>


                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-[var(--livora-stone)]">

                    <span>
                        جستجو بر اساس:
                    </span>

                    <span class="rounded-full border border-[var(--livora-border)] px-3 py-1">
                        نام محصول
                    </span>

                    <span class="rounded-full border border-[var(--livora-border)] px-3 py-1">
                        SKU
                    </span>

                    <span class="rounded-full border border-[var(--livora-border)] px-3 py-1">
                        توضیحات
                    </span>

                </div>

            </div>

        </x-layout.container>

    </div>

</div>
