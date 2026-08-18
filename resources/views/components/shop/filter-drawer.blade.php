<div
    x-show="filterOpen"
    x-cloak
    class="fixed inset-0 z-[80] lg:hidden"
    role="dialog"
    aria-modal="true"
    aria-label="فیلتر محصولات"
>
    {{-- Backdrop --}}
    <div
        x-show="filterOpen"
        x-transition.opacity
        @click="filterOpen = false"
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
    ></div>

    {{-- Drawer --}}
    <div
        x-show="filterOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="absolute inset-x-0 bottom-0 max-h-[90vh] overflow-hidden rounded-t-3xl bg-[var(--livora-cream)]"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-[var(--livora-border)] px-5 py-4">

            <div>
                <p class="text-xs font-medium uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                    FILTERS
                </p>

                <h2 class="mt-1 text-base font-semibold text-[var(--livora-ink)]">
                    فیلتر محصولات
                </h2>
            </div>

            <button
                type="button"
                @click="filterOpen = false"
                aria-label="بستن فیلترها"
                class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] hover:bg-[var(--livora-white)]"
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

        {{-- Content --}}
        <div class="max-h-[calc(90vh-140px)] overflow-y-auto px-5 py-5">

            <x-shop.filters />

        </div>

        {{-- Footer --}}
        <div class="grid grid-cols-2 gap-3 border-t border-[var(--livora-border)] bg-[var(--livora-cream)] p-5">

            <button
                type="button"
                @click="filterOpen = false"
                class="rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm font-medium text-[var(--livora-ink)]"
            >
                بستن
            </button>

            <button
                type="button"
                @click="filterOpen = false"
                class="rounded-xl bg-[var(--livora-ink)] px-4 py-3 text-sm font-medium text-white transition-colors hover:bg-[var(--livora-accent)]"
            >
                اعمال فیلتر
            </button>

        </div>

    </div>
</div>
