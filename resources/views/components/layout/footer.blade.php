@php
    $footerBrandName = $storeSettings?->brand_name ?: config('app.name');
    $footerLogoUrl = $storeSettings?->logo_url;
    $footerMenus = $storeSettings?->footer_menu ?? [];
    $footerPayments = $storeSettings?->payment_methods ?? [];
    $footerSocials = [
        ['label' => 'اینستاگرام', 'url' => $storeSettings?->instagram_url],
        ['label' => 'واتساپ', 'url' => $storeSettings?->whatsapp_url],
        ['label' => 'تلگرام', 'url' => $storeSettings?->telegram_url],
        ['label' => 'ایتا', 'url' => $storeSettings?->eitaa_url],
        ['label' => 'بله', 'url' => $storeSettings?->bale_url],
    ];
    $footerDefaultMenus = [
        'shop' => [
            ['label' => 'مشاهده همه محصولات', 'url' => '/shop'],
            ['label' => 'دسته‌بندی محصولات', 'url' => '/categories'],
        ],
        'guide' => [
            ['label' => 'درباره ما', 'url' => '/about'],
            ['label' => 'قوانین و مقررات', 'url' => '/terms'],
            ['label' => 'مقالات و راهنماها', 'url' => '/blog'],
        ],
        'contact' => [
            ['label' => 'تماس با ما', 'url' => '/contact'],
            ['label' => 'صفحه اصلی', 'url' => '/'],
        ],
    ];
    $footerBrandDescription = trim((string) ($storeSettings?->brand_description ?? ''));
@endphp

<footer class="border-t border-white/10 bg-[var(--sg-ink)] text-[var(--sg-cream)]">
    <x-layout.container>
        <section class="relative mb-10 mt-10 overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/[0.045] px-6 py-7 sm:px-8 sm:py-9 lg:mt-12 lg:flex lg:items-center lg:justify-between lg:gap-10">
            <div class="pointer-events-none absolute -left-16 -top-24 h-56 w-56 rounded-full bg-[var(--sg-accent)]/10 blur-3xl"></div>
            <div class="relative max-w-2xl">
                <p class="text-[10px] font-semibold tracking-[0.2em] text-[var(--sg-accent)]">DISCOVER SILLAGALLERY</p>
                <h2 class="mt-3 text-2xl font-semibold leading-tight text-white sm:text-3xl">انتخاب بعدی‌تان را در {{ $footerBrandName }} پیدا کنید</h2>
                <p class="mt-3 max-w-xl text-sm leading-8 text-white/65">در فروشگاه گردش کنید، محصولات را با جزئیات ببینید و هر زمان برای انتخاب یا ثبت سفارش سوالی داشتید، از راه‌های ارتباطی ما کمک بگیرید.</p>
            </div>
            <div class="relative mt-6 flex shrink-0 flex-wrap gap-3 lg:mt-0">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--sg-accent)] px-6 py-3 text-sm font-semibold text-[var(--sg-ink)] transition hover:brightness-110">رفتن به فروشگاه</a>
                <a href="{{ route('about') }}" class="inline-flex items-center justify-center rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">داستان ما</a>
            </div>
        </section>

        <div class="grid gap-10 pb-10 sm:grid-cols-2 lg:grid-cols-[1.25fr_0.8fr_0.9fr_1fr] lg:gap-12">
            <div class="max-w-md">
                <a href="{{ route('home') }}" aria-label="{{ $footerBrandName }}" class="inline-flex min-h-12 items-center gap-3">
                    @if($footerLogoUrl)
                        <img src="{{ $footerLogoUrl }}" alt="{{ $footerBrandName }}" class="max-h-12 max-w-44 object-contain">
                    @else
                        <span class="text-2xl font-semibold tracking-[0.08em] text-white">{{ $footerBrandName }}</span>
                    @endif
                </a>
                <p class="mt-5 max-w-sm text-sm leading-8 text-white/65">{{ $footerBrandDescription !== '' ? $footerBrandDescription : 'در ' . $footerBrandName . ' تلاش می‌کنیم مسیر آشنایی با محصولات و ثبت سفارش، روشن و ساده باشد. برای دیدن انتخاب‌ها و دریافت اطلاعات بیشتر همراه ما باشید.' }}</p>

                @if(collect($footerSocials)->contains(fn ($social) => filled($social['url'])))
                    <div class="mt-6">
                        <p class="mb-3 text-xs font-semibold text-white/70">ما را در شبکه‌های اجتماعی دنبال کنید</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($footerSocials as $social)
                                @if(filled($social['url']))
                                    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-full border border-white/15 px-3 py-2 text-xs text-white/70 transition hover:border-[var(--sg-accent)] hover:text-white">{{ $social['label'] }}</a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-2">
                    @if(filled($storeSettings?->enamad_url))
                        <a href="{{ $storeSettings->enamad_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/15 px-3 py-2 text-[11px] text-white/75 transition hover:border-white/40 hover:text-white">استعلام نماد اعتماد</a>
                    @endif
                    @if(filled($storeSettings?->samandehi_url))
                        <a href="{{ $storeSettings->samandehi_url }}" target="_blank" rel="noopener noreferrer" class="rounded-full border border-white/15 px-3 py-2 text-[11px] text-white/75 transition hover:border-white/40 hover:text-white">ساماندهی</a>
                    @endif
                </div>
            </div>

            @foreach([
                'shop' => 'در فروشگاه',
                'guide' => 'راهنمای شما',
                'contact' => 'ارتباط و پشتیبانی',
            ] as $menuKey => $menuTitle)
                @php
                    $configuredLinks = data_get($footerMenus, $menuKey, []);
                    $menuLinks = is_array($configuredLinks) && count($configuredLinks)
                        ? $configuredLinks
                        : $footerDefaultMenus[$menuKey];
                @endphp
                <div>
                    <h2 class="text-sm font-semibold text-white">{{ $menuTitle }}</h2>
                    <nav aria-label="{{ $menuTitle }}" class="mt-5 flex flex-col gap-3">
                        @foreach($menuLinks as $link)
                            @php
                                $linkUrl = (string) ($link['url'] ?? '');
                                $externalLink = str_starts_with($linkUrl, 'https://');
                                $href = $externalLink ? $linkUrl : url($linkUrl ?: '/');
                            @endphp
                            @if(filled($link['label'] ?? null) && filled($linkUrl))
                                <a href="{{ $href }}" @if($externalLink) target="_blank" rel="noopener noreferrer" @endif class="w-fit text-sm text-white/65 transition hover:translate-x-[-2px] hover:text-white">{{ $link['label'] }}</a>
                            @endif
                        @endforeach
                    </nav>
                </div>
            @endforeach
        </div>

        @if(filled($storeSettings?->phone) || filled($storeSettings?->email) || filled($storeSettings?->address) || filled($storeSettings?->hours))
            <div class="grid gap-5 rounded-2xl border border-white/10 bg-white/[0.035] px-5 py-5 text-sm text-white/70 sm:grid-cols-2 lg:grid-cols-4">
                @if(filled($storeSettings?->phone))
                    <div><p class="mb-2 text-[11px] text-white/40">تماس تلفنی</p><a class="transition hover:text-white" dir="ltr" href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $storeSettings->phone) }}">{{ $storeSettings->phone }}</a></div>
                @endif
                @if(filled($storeSettings?->email))
                    <div><p class="mb-2 text-[11px] text-white/40">پست الکترونیکی</p><a class="break-all transition hover:text-white" href="mailto:{{ $storeSettings->email }}">{{ $storeSettings->email }}</a></div>
                @endif
                @if(filled($storeSettings?->address))
                    <div><p class="mb-2 text-[11px] text-white/40">نشانی</p><p class="leading-7">{{ $storeSettings->address }}</p></div>
                @endif
                @if(filled($storeSettings?->hours))
                    <div><p class="mb-2 text-[11px] text-white/40">ساعات پاسخ‌گویی</p><p class="leading-7">{{ $storeSettings->hours }}</p></div>
                @endif
            </div>
        @endif

        @if(count($footerPayments))
            <div class="flex flex-wrap items-center gap-4 border-t border-white/10 py-6">
                <span class="text-[11px] text-white/45">روش‌های پرداخت ثبت‌شده</span>
                @foreach($footerPayments as $payment)
                    @if(filled($payment['label'] ?? null))
                        @if(filled($payment['url'] ?? null))
                            <a href="{{ $payment['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2">
                        @else
                            <span class="inline-flex items-center gap-2">
                        @endif
                            @if(filled($payment['image'] ?? null))
                                <img src="{{ $payment['image'] }}" alt="{{ $payment['label'] }}" loading="lazy" class="h-8 max-w-24 object-contain">
                            @else
                                <span class="rounded-lg border border-white/15 px-3 py-2 text-xs text-white/70">{{ $payment['label'] }}</span>
                            @endif
                        @if(filled($payment['url'] ?? null))</a>@else</span>@endif
                    @endif
                @endforeach
            </div>
        @endif

        <div class="flex flex-col gap-3 border-t border-white/10 py-6 text-[11px] text-white/45 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ \App\Helpers\JalaliHelper::formatYear(now()) }} {{ $storeSettings?->copyright_text ?: ('تمام حقوق برای '.$footerBrandName.' محفوظ است.') }}</p>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="{{ route('about') }}" class="transition hover:text-white">درباره ما</a>
                <a href="{{ route('terms') }}" class="transition hover:text-white">قوانین و مقررات</a>
                <a href="{{ route('contact') }}" class="transition hover:text-white">تماس</a>
            </div>
        </div>
    </x-layout.container>
</footer>
