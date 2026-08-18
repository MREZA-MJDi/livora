@props([
'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'success' =>
            'bg-green-500/10 text-green-700',

        'warning' =>
            'bg-yellow-500/10 text-yellow-700',

        'danger' =>
            'bg-red-500/10 text-red-700',

        'accent' =>
            'bg-[var(--livora-accent)]/10 text-[var(--livora-accent-dark)]',

        'dark' =>
            'bg-[var(--livora-ink)] text-white',

        default =>
            'bg-[var(--livora-sand)]/50 text-[var(--livora-ink)]',
    };
@endphp

<span
    {{ $attributes->merge([
        'class' => "inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {$classes}",
    ]) }}
>
    {{ $slot }}
</span>
