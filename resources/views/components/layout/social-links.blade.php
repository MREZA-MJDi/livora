@props([
    'settings' => null,
    'variant' => 'footer',
])

@php
    $socialLinks = collect([
        ['key' => 'instagram_url', 'label' => 'Instagram'],
        ['key' => 'whatsapp_url', 'label' => 'WhatsApp'],
        ['key' => 'telegram_url', 'label' => 'Telegram'],
    ])->filter(fn (array $item) => $settings?->is_active && filled($settings->{$item['key']}))
      ->values();

    $wrapperClasses = $variant === 'footer'
        ? 'flex items-center gap-2'
        : 'flex items-center justify-center gap-3';

    $linkClasses = $variant === 'footer'
        ? 'flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/65 transition hover:border-white/25 hover:bg-white/10 hover:text-white'
        : 'flex h-11 w-11 items-center justify-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-ink)] shadow-sm transition hover:-translate-y-0.5 hover:border-[var(--sg-accent)] hover:text-[var(--sg-accent)] hover:shadow-md';
@endphp

@if($socialLinks->isNotEmpty())
    <nav class="{{ $wrapperClasses }}" aria-label="شبکه‌های اجتماعی">
        @foreach($socialLinks as $social)
            <a
                href="{{ $settings->{$social['key']} }}"
                target="_blank"
                rel="noopener noreferrer"
                aria-label="{{ $social['label'] }}"
                title="{{ $social['label'] }}"
                class="{{ $linkClasses }}"
            >
                @if($social['key'] === 'instagram_url')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" class="h-5 w-5" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="5"/>
                        <circle cx="12" cy="12" r="4"/>
                        <circle cx="17.6" cy="6.6" r="1" fill="currentColor" stroke="none"/>
                    </svg>
                @elseif($social['key'] === 'whatsapp_url')
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.2 11.7a8.2 8.2 0 0 1-12.1 7.2L4 20l1.1-3.9a8.2 8.2 0 1 1 15.1-4.4Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.4c.2-.5.5-.5.8-.5h.5c.2 0 .4.1.5.4l.8 1.9c.1.2.1.4-.1.6l-.6.7c-.2.2-.2.4 0 .6.6 1 1.4 1.7 2.4 2.2.2.1.4.1.6-.1l.8-.9c.2-.2.4-.2.6-.1l1.8.9c.2.1.3.3.3.5 0 .4-.2 1-.6 1.3-.4.4-1 .6-1.6.5-1-.2-2.3-.8-3.7-2-1.2-1.1-2-2.4-2.2-3.4-.2-.8 0-1.8.5-2.6Z"/>
                    </svg>
                @else
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                        <path d="M21.7 4.4 18.5 20c-.2 1.1-.8 1.4-1.7.9l-4.7-3.5-2.3 2.2c-.3.3-.5.5-1 .5l.3-4.8 8.8-8c.4-.4-.1-.6-.6-.2L6.4 14.1l-4.6-1.4c-1-.3-1-1 .2-1.5L20 4.1c.9-.3 1.9.2 1.7.3Z"/>
                    </svg>
                @endif
            </a>
        @endforeach
    </nav>
@endif
