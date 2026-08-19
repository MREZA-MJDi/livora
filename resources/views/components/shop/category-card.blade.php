@props([
'name',
'image' => null,
'href' => '#',
'count' => null,
])

@php
    $hasCount = $count !== null;
@endphp

<article class="group relative min-w-0">

    <a
        href="{{ $href }}"
        class="block"
        aria-label="مشاهده دسته‌بندی {{ $name }}"
    >

        <div class="relative overflow-hidden rounded-[2rem] bg-[var(--livora-surface)]">

            {{-- Image --}}
            <div class="aspect-[4/5] overflow-hidden">

                @if($image)

                    <img
                        src="{{ $image }}"
                        alt="{{ $name }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.05]"
                    >

                @else

                    <div class="flex h-full w-full items-center justify-center">
                        <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                            LIVORA
                        </span>
                    </div>

                @endif

            </div>


            {{-- Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent"></div>


            {{-- Top label --}}
            <div class="absolute left-4 top-4">

                <span class="rounded-full border border-white/20 bg-black/20 px-3 py-1.5 text-[10px] font-medium text-white backdrop-blur-xl">
                    LIVORA
                </span>

            </div>


            {{-- Content --}}
            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-6">

                <div class="flex items-end justify-between gap-4">

                    <div class="min-w-0">

                        <p class="text-[10px] uppercase tracking-[0.18em] text-white/55">
                            COLLECTION
                        </p>

                        <h3 class="mt-2 truncate text-lg font-semibold text-white sm:text-xl">
                            {{ $name }}
                        </h3>

                        @if($hasCount)

                            <p class="mt-2 text-[11px] text-white/55">
                                {{ number_format($count) }}
                                محصول
                            </p>

                        @endif

                    </div>


                    {{-- Arrow --}}
                    <span
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white backdrop-blur-xl transition duration-300 group-hover:-translate-x-1 group-hover:bg-white group-hover:text-[var(--livora-ink)]"
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

                    </span>

                </div>

            </div>

        </div>

    </a>

</article>
