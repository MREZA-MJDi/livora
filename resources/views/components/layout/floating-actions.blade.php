@php
    $settings = $storeSettings ?? null;
    $phone = preg_replace('/[^0-9+]/', '', (string) ($settings?->phone ?? ''));
@endphp

@if($settings?->is_active && $settings->floating_actions_enabled && ($phone || $settings->whatsapp_url || $settings->telegram_url))
    <nav aria-label="راه‌های ارتباط سریع" class="fixed bottom-24 left-4 z-40 flex flex-col gap-2 lg:bottom-6 lg:left-6">
        @if($settings->whatsapp_url)<a href="{{ $settings->whatsapp_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-[#25D366] px-4 py-3 text-xs font-bold text-white shadow-lg">واتساپ</a>@endif
        @if($settings->telegram_url)<a href="{{ $settings->telegram_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-[#229ED9] px-4 py-3 text-xs font-bold text-white shadow-lg">تلگرام</a>@endif
        @if($phone)<a href="tel:{{ $phone }}" class="rounded-full bg-[var(--sg-ink)] px-4 py-3 text-xs font-bold text-white shadow-lg">تماس</a>@endif
    </nav>
@endif
