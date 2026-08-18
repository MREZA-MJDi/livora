@props([
'type' => 'button',
'variant' => 'primary',
'size' => 'md',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 rounded-xl font-medium transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variantClasses = match ($variant) {
        'secondary' =>
            'border border-[var(--livora-border)] bg-transparent text-[var(--livora-ink)] hover:border-[var(--livora-accent)] hover:text-[var(--livora-accent)]',

        'outline' =>
            'border border-[var(--livora-ink)] bg-transparent text-[var(--livora-ink)] hover:bg-[var(--livora-ink)] hover:text-white',

        'ghost' =>
            'bg-transparent text-[var(--livora-ink)] hover:bg-[var(--livora-white)]',

        'danger' =>
            'bg-red-700 text-white hover:bg-red-800',

        default =>
            'bg-[var(--livora-ink)] text-white hover:bg-[var(--livora-accent)]',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-4 py-2 text-xs',
        'lg' => 'px-6 py-3.5 text-base',
        default => 'px-5 py-2.5 text-sm',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => "{$baseClasses} {$variantClasses} {$sizeClasses}",
    ]) }}
>
    {{ $slot }}
</button>
