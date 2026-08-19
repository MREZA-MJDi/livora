@props([
'variant' => 'primary',
'size' => 'md',
'type' => 'button',
'href' => null,
'disabled' => false,
'loading' => false,
])

@php
    $variants = [
        'primary' => [
            'base' => 'bg-[var(--livora-ink)] text-white border-[var(--livora-ink)] hover:bg-[var(--livora-accent)] hover:border-[var(--livora-accent)]',
        ],

        'secondary' => [
            'base' => 'bg-[var(--livora-white)] text-[var(--livora-ink)] border-[var(--livora-border)] hover:border-[var(--livora-ink)] hover:bg-[var(--livora-surface)]',
        ],

        'ghost' => [
            'base' => 'bg-transparent text-[var(--livora-ink)] border-transparent hover:bg-[var(--livora-surface)]',
        ],

        'dark' => [
            'base' => 'bg-black text-white border-black hover:bg-[var(--livora-accent)] hover:border-[var(--livora-accent)]',
        ],

        'danger' => [
            'base' => 'bg-red-600 text-white border-red-600 hover:bg-red-700 hover:border-red-700',
        ],

        'success' => [
            'base' => 'bg-emerald-600 text-white border-emerald-600 hover:bg-emerald-700 hover:border-emerald-700',
        ],

        'link' => [
            'base' => 'border-transparent bg-transparent text-[var(--livora-accent)] hover:text-[var(--livora-ink)]',
        ],
    ];

    $sizes = [
        'xs' => [
            'base' => 'min-h-9 rounded-xl px-3 text-[11px]',
            'icon' => 'h-4 w-4',
        ],

        'sm' => [
            'base' => 'min-h-10 rounded-xl px-4 text-xs',
            'icon' => 'h-4 w-4',
        ],

        'md' => [
            'base' => 'min-h-12 rounded-2xl px-5 text-sm',
            'icon' => 'h-4 w-4',
        ],

        'lg' => [
            'base' => 'min-h-14 rounded-2xl px-7 text-sm',
            'icon' => 'h-5 w-5',
        ],

        'xl' => [
            'base' => 'min-h-16 rounded-3xl px-8 text-base',
            'icon' => 'h-5 w-5',
        ],
    ];

    $selectedVariant =
        $variants[$variant]
        ?? $variants['primary'];

    $selectedSize =
        $sizes[$size]
        ?? $sizes['md'];

    $classes =
        'inline-flex w-fit items-center justify-center gap-2 border font-medium ' .
        'transition-all duration-300 ease-out ' .
        'focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--livora-accent)] focus-visible:ring-offset-2 ' .
        'disabled:cursor-not-allowed disabled:opacity-40 ' .
        $selectedVariant['base'] . ' ' .
        $selectedSize['base'];
@endphp

@if($href && ! $disabled)

    <a
        href="{{ $href }}"
        {{ $attributes->merge([
            'class' => $classes
        ]) }}
    >

        @if($loading)

            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
                class="{{ $selectedSize['icon'] }} animate-spin"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3a9 9 0 1 0 9 9"
                />
            </svg>

        @endif

        {{ $slot }}

    </a>

@else

    <button
        type="{{ $type }}"
        @disabled($disabled)
        @if($loading)
        aria-busy="true"
        @endif
        {{ $attributes->merge([
            'class' => $classes
        ]) }}
    >

        @if($loading)

            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.8"
                stroke="currentColor"
                class="{{ $selectedSize['icon'] }} animate-spin"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 3a9 9 0 1 0 9 9"
                />
            </svg>

        @endif

        {{ $slot }}

    </button>

@endif
