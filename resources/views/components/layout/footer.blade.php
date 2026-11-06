@php
    $footerBrandName = $storeSettings?->brand_name ?: config('app.name');
    $footerLogoUrl = $storeSettings?->logo_url;
    $footerMenus = $storeSettings?->footer_menu ?? [];
    $footerPayments = $storeSettings?->payment_methods ?? [];
@endphp

<footer class="border-t border-white/10 bg-[var(--sg-ink)] text-[var(--sg-cream)]">
    <x-layout.container>
        <div class="grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[1.3fr_0.8fr_0.9fr_1fr] lg:gap-12 lg:py-18">
            <div class="max-w-md">
                <a href="{{ route('home') }}" aria-label="{{ $footerBrandName }}" class="inline-flex items-center gap-3">
                    @if($footerLogoUrl)
                        <img src="{{ $footerLogoUrl }}" alt="{{ $footerBrandName }}" class="max-h-12 max-w-44 object-contain">
                    @else
                        <span class="text-2xl font-semibold tracking-[0.12em] text-white">{{ $footerBrandName }}</span>
                    @endif
                </a>
                @if(filled($storeSettings?->brand_description))
                    <p class="mt-5 max-w-sm text-sm leading-8 text-white/55">{{ $storeSettings->brand_description }}</p>
                @endif
                @php($footerSocials = ['WhatsApp' => $storeSettings?->whatsapp_url, 'Telegram' => $storeSettings?->telegram_url, 'Instagram' => $storeSettings?->instagram_url, 'Eitaa' => $storeSettings?->eitaa_url, 'Bale' => $storeSettings?->bale_url])
                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach($footerSocials as $network => $url)
                        @if(filled($url))
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $network }}" class="text-xs text-white/55 transition hover:text-white">{{ $network }}</a>
                        @endif
                    @endforeach
                </div>
                <div class="mt-6 flex flex-wrap gap-2">
                    @if(filled($storeSettings?->enamad_url))
                        <a href="{{ $storeSettings->enamad_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/15 px-3 py-2 text-[11px] text-white/70 transition hover:border-white/40 hover:text-white">نماد اعتماد</a>
                    @endif
                    @if(filled($storeSettings?->samandehi_url))
                        <a href="{{ $storeSettings->samandehi_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/15 px-3 py-2 text-[11px] text-white/70 transition hover:border-white/40 hover:text-white">ساماندهی</a>
                    @endif
                </div>
            </div>

            @foreach([
                'shop' => 'فروشگاه',
                'guide' => 'راهنمای مشتریان',
                'contact' => 'دسترسی و ارتباط',
            ] as $menuKey => $menuTitle)
                <div>
                    <h2 class="text-[11px] font-semibold tracking-[0.12em] text-white/45">{{ $menuTitle }}</h2>
                    <nav aria-label="{{ $menuTitle }}" class="mt-5 flex flex-col gap-3">
                        @foreach(data_get($footerMenus, $menuKey, []) as $link)
                            @php
                                $linkUrl = (string) ($link['url'] ?? '');
                                $externalLink = str_starts_with($linkUrl, 'https://');
                                $href = $externalLink ? $linkUrl : url($linkUrl ?: '/');
                            @endphp
                            @if(filled($link['label'] ?? null) && filled($linkUrl))
                                <a href="{{ $href }}" @if($externalLink) target="_blank" rel="noopener noreferrer" @endif class="text-sm text-white/60 transition hover:text-white">{{ $link['label'] }}</a>
                            @endif
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        @if(filled($storeSettings?->phone) || filled($storeSettings?->email) || filled($storeSettings?->address) || filled($storeSettings?->hours))
            <div class="grid gap-4 border-t border-white/10 py-6 text-sm text-white/55 sm:grid-cols-2 lg:grid-cols-4">
                @if(filled($storeSettings?->phone))<a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $storeSettings->phone) }}">{{ $storeSettings->phone }}</a>@endif
                @if(filled($storeSettings?->email))<a href="mailto:{{ $storeSettings->email }}">{{ $storeSettings->email }}</a>@endif
                @if(filled($storeSettings?->address))<p class="leading-7">{{ $storeSettings->address }}</p>@endif
                @if(filled($storeSettings?->hours))<p class="leading-7">ساعات پاسخ‌گویی: {{ $storeSettings->hours }}</p>@endif
            </div>
        @endif

        @if(count($footerPayments))
            <div class="flex flex-wrap items-center gap-4 border-t border-white/10 py-6">
                <span class="text-[10px] text-white/40">روش‌های پرداخت</span>
                @foreach($footerPayments as $payment)
                    @if(filled($payment['label'] ?? null))
                        @if(filled($payment['url'] ?? null))
                            <a href="{{ $payment['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2">
                        @else
                            <span class="inline-flex items-center gap-2">
                        @endif
                            @if(filled($payment['image'] ?? null))<img src="{{ $payment['image'] }}" alt="{{ $payment['label'] }}" loading="lazy" class="h-8 max-w-24 object-contain">@else<span class="rounded-lg border border-white/15 px-3 py-2 text-xs text-white/65">{{ $payment['label'] }}</span>@endif
                        @if(filled($payment['url'] ?? null))</a>@else</span>@endif
                    @endif
                @endforeach
            </div>
        @endif

        <div class="flex flex-col gap-3 border-t border-white/10 py-6 text-[11px] text-white/40 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ \App\Helpers\JalaliHelper::formatYear(now()) }} {{ $storeSettings?->copyright_text ?: ('تمام حقوق برای '.$footerBrandName.' محفوظ است.') }}</p>
            <a href="{{ route('home') }}" class="transition hover:text-white">{{ $footerBrandName }}</a>
        </div>
    </x-layout.container>
</footer>
