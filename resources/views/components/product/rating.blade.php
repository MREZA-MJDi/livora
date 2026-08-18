@props([
'rating' => 0,
'count' => null,
'size' => 'sm',
])

@php
    $sizeClass = match ($size) {
        'lg' => 'h-5 w-5',
        'md' => 'h-4 w-4',
        default => 'h-3.5 w-3.5',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>

    <div class="flex items-center gap-0.5 text-[var(--livora-accent)]">

        @for($i = 1; $i <= 5; $i++)
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="{{ $i <= round($rating) ? 'currentColor' : 'none' }}"
                stroke="currentColor"
                stroke-width="1.5"
                class="{{ $sizeClass }}"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M10.788 3.21c.47-1.128 2.068-1.128 2.539 0l1.231 2.95a1.375 1.375 0 0 0 1.054.812l3.178.347c1.214.133 1.698 1.636.78 2.45l-2.402 2.133a1.375 1.375 0 0 0-.412 1.337l.716 3.117c.274 1.194-1.015 2.13-2.067 1.497l-2.73-1.644a1.375 1.375 0 0 0-1.42 0l-2.73 1.644c-1.052.633-2.341-.303-2.067-1.497l.716-3.117a1.375 1.375 0 0 0-.412-1.337L4.09 9.77c-.918-.814-.434-2.317.78-2.45l3.178-.347a1.375 1.375 0 0 0 1.054-.812l1.231-2.95Z"
                />
            </svg>
        @endfor

    </div>

    <span class="text-xs text-[var(--livora-stone)]">
        {{ number_format((float) $rating, 1) }}
    </span>

    @if($count !== null)
        <span class="text-xs text-[var(--livora-stone)]">
            ({{ $count }})
        </span>
    @endif

</div>
