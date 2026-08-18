@props([
'name',
'image',
'href' => '#',
'count' => null,
])

<a
    href="{{ $href }}"
    class="group relative block overflow-hidden rounded-2xl bg-[var(--livora-charcoal)]"
>
    <div class="aspect-[4/3] overflow-hidden">
        <img
            src="{{ $image }}"
            alt="{{ $name }}"
            class="h-full w-full object-cover transition-transform duration-700 ease-out group-hover:scale-105"
        >
    </div>

    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

    <div class="absolute inset-x-0 bottom-0 p-5 text-white">
        <div class="flex items-end justify-between gap-4">

            <div>
                <h3 class="text-lg font-semibold">
                    {{ $name }}
                </h3>

                @if($count !== null)
                    <p class="mt-1 text-xs text-white/70">
                        {{ $count }} محصول
                    </p>
                @endif
            </div>

            <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/30 bg-white/10 backdrop-blur-sm transition-all duration-300 group-hover:border-white/60 group-hover:bg-white group-hover:text-[var(--livora-ink)]"
                aria-hidden="true"
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
                        d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                    />
                </svg>
            </span>

        </div>
    </div>
</a>
