@props([
'current' => 1,
'total' => 1,
])

@if($total > 1)
    <nav
        class="flex items-center justify-center gap-2"
        aria-label="صفحه‌بندی"
    >

        {{-- Previous --}}
        <a
            href="#"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-stone)] transition-all duration-300 hover:border-[var(--livora-accent)] hover:text-[var(--livora-accent)]"
            aria-label="صفحه قبل"
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
                    d="M15.75 19.5 8.25 12l7.5-7.5"
                />
            </svg>
        </a>

        {{-- Pages --}}
        @for($page = 1; $page <= $total; $page++)
            <a
                href="#"
                class="flex h-10 min-w-10 items-center justify-center rounded-xl border px-3 text-sm font-medium transition-all duration-300
                    {{ $page === $current
                        ? 'border-[var(--livora-ink)] bg-[var(--livora-ink)] text-white'
                        : 'border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-ink)] hover:border-[var(--livora-accent)] hover:text-[var(--livora-accent)]'
                    }}"
                aria-current="{{ $page === $current ? 'page' : 'false' }}"
            >
                {{ $page }}
            </a>
        @endfor

        {{-- Next --}}
        <a
            href="#"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-stone)] transition-all duration-300 hover:border-[var(--livora-accent)] hover:text-[var(--livora-accent)]"
            aria-label="صفحه بعد"
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
        </a>

    </nav>
@endif
