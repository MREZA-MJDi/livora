@props([
'name' => 'modal',
'title' => null,
'size' => 'md',
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'max-w-md',
        'lg' => 'max-w-4xl',
        'xl' => 'max-w-6xl',
        default => 'max-w-2xl',
    };
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $name }}-title"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        x-on:click="open = false"
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="relative w-full {{ $sizeClasses }} overflow-hidden rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-cream)] shadow-2xl"
    >

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-[var(--livora-border)] px-5 py-4 sm:px-6">

            @if($title)
                <h2
                    id="{{ $name }}-title"
                    class="text-base font-semibold text-[var(--livora-ink)]"
                >
                    {{ $title }}
                </h2>
            @endif

            <button
                type="button"
                x-on:click="open = false"
                aria-label="بستن"
                class="mr-auto flex h-9 w-9 items-center justify-center rounded-full text-[var(--livora-stone)] transition-all duration-300 hover:bg-[var(--livora-white)] hover:text-[var(--livora-ink)]"
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

        {{-- Body --}}
        <div class="max-h-[80vh] overflow-y-auto p-5 sm:p-6">
            {{ $slot }}
        </div>

    </div>
</div>
