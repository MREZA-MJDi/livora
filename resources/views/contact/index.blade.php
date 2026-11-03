@extends('layouts.app')

@section('title', 'تماس با '.($storeSettings?->brand_name ?: config('app.name')).' | ارتباط با ما')
@section('description', $pageContent['hero_description'] ?? 'برای مشاوره خرید، پیگیری سفارش و دریافت اطلاعات بیشتر با ما در ارتباط باشید.')
@section('canonical', \App\Models\SeoPageSetting::forRoute('contact')?->canonical_url ?: route('contact'))
@section('robots', \App\Models\SeoPageSetting::forRoute('contact')?->robots() ?? \App\Models\SeoSetting::current()->robots())

@push('seo')
@if($faqs->isNotEmpty())
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faqs->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endif
@endpush

@section('content')
<div class="bg-[var(--sg-cream)]">
    <section class="relative overflow-hidden border-b border-[var(--sg-border)] bg-[var(--sg-white)]">
        <x-layout.container>
            <div class="py-10 sm:py-16 lg:py-20">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-[var(--sg-stone)]">
                    <a href="{{ route('home') }}" class="hover:text-[var(--sg-ink)]">خانه</a><span>/</span><span class="text-[var(--sg-ink)]">{{ $pageContent['breadcrumb_label'] ?? 'تماس با ما' }}</span>
                </nav>
                <div class="mt-10 grid items-end gap-8 lg:grid-cols-[1.15fr_.85fr] lg:gap-16">
                    <div>
                        <p class="sg-eyebrow">{{ $pageContent['hero_kicker'] ?? 'ارتباط با ما' }}</p>
                        <h1 class="mt-5 max-w-3xl text-4xl font-semibold leading-[1.3] tracking-tight text-[var(--sg-ink)] sm:text-5xl lg:text-6xl">
                            {{ $pageContent['hero_title'] ?? 'برای انتخاب بهتر،' }}
                            <span class="block text-[var(--sg-accent)]">{{ $pageContent['hero_highlight'] ?? 'کنار شما هستیم.' }}</span>
                        </h1>
                    </div>
                    <div class="max-w-xl lg:pb-2">
                        <p class="text-sm leading-8 text-[var(--sg-stone)] sm:text-base">{{ $pageContent['hero_description'] ?? 'برای مشاوره خرید، پیگیری سفارش یا هر موضوع دیگری می‌توانید با ما در ارتباط باشید.' }}</p>
                        <a href="#contact-form" class="mt-7 inline-flex min-h-12 items-center justify-center rounded-xl bg-[var(--sg-ink)] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[var(--sg-accent)]">{{ $pageContent['hero_cta'] ?? 'ارسال پیام' }}</a>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    @php
        $contactMethods = collect([
            ['number' => '01', 'label' => $contactSetting?->phone_label ?? 'تماس تلفنی', 'value' => $contactSetting?->phone, 'href' => $contactSetting?->phone ? 'tel:'.$contactSetting->phone : null, 'eyebrow' => 'PHONE'],
            ['number' => '02', 'label' => $contactSetting?->email_label ?? 'ایمیل', 'value' => $contactSetting?->email, 'href' => $contactSetting?->email ? 'mailto:'.$contactSetting->email : null, 'eyebrow' => 'EMAIL'],
            ['number' => '03', 'label' => $contactSetting?->support_label ?? 'پشتیبانی', 'value' => $contactSetting?->support_description, 'href' => null, 'eyebrow' => 'SUPPORT'],
            ['number' => '04', 'label' => $contactSetting?->online_label ?? 'ارتباط آنلاین', 'value' => $contactSetting?->online_description, 'href' => null, 'eyebrow' => 'ONLINE'],
        ])->filter(fn ($item) => filled($item['value']))->values();
    @endphp
    @if($contactMethods->isNotEmpty())
        <section class="border-b border-[var(--sg-border)]">
            <x-layout.container>
                <div class="grid gap-3 py-8 sm:grid-cols-2 lg:grid-cols-4 lg:py-10">
                    @foreach($contactMethods as $method)
                        <article class="group min-w-0 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5 transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-6">
                            <div class="flex items-center justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--sg-surface)] text-xs font-semibold text-[var(--sg-ink)]">{{ $method['number'] }}</span>
                                <span class="text-[9px] tracking-[.18em] text-[var(--sg-accent)]">{{ $method['eyebrow'] }}</span>
                            </div>
                            <h2 class="mt-6 text-base font-semibold text-[var(--sg-ink)]">{{ $method['label'] }}</h2>
                            @if($method['href'])
                                <a href="{{ $method['href'] }}" class="mt-2 block break-all text-sm text-[var(--sg-stone)] hover:text-[var(--sg-accent)]" dir="auto">{{ $method['value'] }}</a>
                            @else
                                <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">{{ $method['value'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </x-layout.container>
        </section>
    @endif

    <section id="contact-form" class="scroll-mt-24 py-12 sm:py-16 lg:py-20">
        <x-layout.container>
            <div class="grid items-start gap-8 lg:grid-cols-[.82fr_1.18fr] lg:gap-12">
                <aside class="space-y-5">
                    <div class="max-w-xl">
                        <p class="sg-eyebrow">{{ $pageContent['info_kicker'] ?? 'پشتیبانی SillaGallery' }}</p>
                        <h2 class="mt-4 text-3xl font-semibold leading-snug text-[var(--sg-ink)] sm:text-4xl">{{ $pageContent['info_title'] ?? 'چه کمکی از دست ما برمی‌آید؟' }}</h2>
                        <p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">{{ $pageContent['info_description'] ?? 'اگر درباره محصول، موجودی، قیمت، شرایط خرید اقساطی، ارسال یا سفارش خود سؤالی دارید، پیام بگذارید.' }}</p>
                    </div>
                    @if(filled($contactSetting?->address))
                        <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">
                            <p class="text-xs font-semibold text-[var(--sg-accent)]">{{ $pageContent['address_label'] ?? 'آدرس' }}</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">{{ $contactSetting->address }}</p>
                        </div>
                    @endif
                    @if(filled($contactSetting?->hours))
                        <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">
                            <p class="text-xs font-semibold text-[var(--sg-accent)]">{{ $pageContent['hours_label'] ?? 'ساعات کاری' }}</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">{{ $contactSetting->hours }}</p>
                        </div>
                    @endif
                    @if(filled($pageContent['installment_note'] ?? null))
                        <div class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">
                            <p class="text-xs font-semibold text-[var(--sg-accent)]">{{ $pageContent['installment_label'] ?? 'خرید اقساطی' }}</p>
                            <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">{{ $pageContent['installment_note'] }}</p>
                        </div>
                    @endif
                    @if($contactSetting?->googleMapsEmbedUrl())
                        <div class="overflow-hidden rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)]">
                            <div class="border-b border-[var(--sg-border)] px-5 py-4"><p class="sg-eyebrow">FIND US</p><h3 class="mt-1 font-semibold">موقعیت فروشگاه</h3></div>
                            <div class="aspect-[4/3] bg-[var(--sg-surface)]"><iframe src="{{ $contactSetting->googleMapsEmbedUrl() }}" title="نقشه موقعیت فروشگاه SillaGallery" class="h-full w-full border-0" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
                            @if($contactSetting->googleMapsDirectionsUrl())
                                <div class="p-4"><a href="{{ $contactSetting->googleMapsDirectionsUrl() }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-[var(--sg-accent)]">مسیریابی در Google Maps ↗</a></div>
                            @endif
                        </div>
                    @elseif($contactSetting?->googleMapsSearchUrl())
                        <a href="{{ $contactSetting->googleMapsSearchUrl() }}" target="_blank" rel="noopener noreferrer" class="block rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5 text-sm font-semibold text-[var(--sg-accent)]">مشاهده آدرس در Google Maps ↗</a>
                    @endif
                </aside>

                <div class="rounded-[1.5rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-5 shadow-[0_18px_60px_rgba(35,29,23,.06)] sm:p-8 lg:p-10">
                    <div class="mb-7">
                        <p class="sg-eyebrow">{{ $pageContent['form_kicker'] ?? 'فرم تماس' }}</p>
                        <h2 class="mt-3 text-2xl font-semibold text-[var(--sg-ink)]">{{ $pageContent['form_title'] ?? 'پیام خود را ارسال کنید' }}</h2>
                        <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">{{ $pageContent['form_description'] ?? 'اطلاعات تماس خود را وارد کنید تا بتوانیم پاسخ دقیق‌تری ارائه دهیم.' }}</p>
                    </div>
                    @if(session('success'))
                        <div role="status" class="mb-6 rounded-xl border border-[var(--sg-success)]/20 bg-[var(--sg-success-soft)] p-4 text-sm leading-7 text-[var(--sg-success)]">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div role="alert" class="mb-6 rounded-xl border border-[var(--sg-danger)]/20 bg-[var(--sg-danger-soft)] p-4">
                            <ul class="list-inside list-disc space-y-1 text-sm leading-7 text-[var(--sg-danger)]">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="contact-name" class="admin-label">نام و نام خانوادگی <span class="text-[var(--sg-danger)]">*</span></label>
                                <input id="contact-name" name="name" type="text" required maxlength="120" autocomplete="name" value="{{ old('name', auth()->user()?->name) }}" class="admin-input mt-2 w-full @error('name') border-[var(--sg-danger)] @enderror" placeholder="نام شما">
                                @error('name')<p class="mt-2 text-xs text-[var(--sg-danger)]">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="contact-phone" class="admin-label">شماره تماس <span class="text-[var(--sg-danger)]">*</span></label>
                                <input id="contact-phone" name="phone" type="tel" required maxlength="30" autocomplete="tel" inputmode="tel" dir="ltr" value="{{ old('phone') }}" class="admin-input mt-2 w-full text-left @error('phone') border-[var(--sg-danger)] @enderror" placeholder="09xxxxxxxxx">
                                @error('phone')<p class="mt-2 text-xs text-[var(--sg-danger)]">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label for="contact-email" class="admin-label">ایمیل <span class="text-xs font-normal text-[var(--sg-stone)]">(اختیاری)</span></label>
                            <input id="contact-email" name="email" type="email" maxlength="255" autocomplete="email" dir="ltr" value="{{ old('email', auth()->user()?->email) }}" class="admin-input mt-2 w-full text-left @error('email') border-[var(--sg-danger)] @enderror" placeholder="you@example.com">
                            @error('email')<p class="mt-2 text-xs text-[var(--sg-danger)]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="contact-subject" class="admin-label">موضوع پیام <span class="text-[var(--sg-danger)]">*</span></label>
                            <select id="contact-subject" name="subject" required class="admin-select mt-2 w-full @error('subject') border-[var(--sg-danger)] @enderror">
                                <option value="">انتخاب موضوع</option>
                                <option value="product" @selected(old('subject') === 'product')>مشاوره درباره محصول</option>
                                <option value="installment" @selected(old('subject') === 'installment')>شرایط خرید اقساطی</option>
                                <option value="order" @selected(old('subject') === 'order')>پیگیری سفارش</option>
                                <option value="shipping" @selected(old('subject') === 'shipping')>ارسال و تحویل</option>
                                <option value="other" @selected(old('subject') === 'other')>سایر</option>
                            </select>
                            @error('subject')<p class="mt-2 text-xs text-[var(--sg-danger)]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="contact-message" class="admin-label">پیام شما <span class="text-[var(--sg-danger)]">*</span></label>
                            <textarea id="contact-message" name="message" rows="6" required minlength="10" maxlength="5000" class="admin-textarea mt-2 w-full @error('message') border-[var(--sg-danger)] @enderror" placeholder="چطور می‌توانیم راهنمایی‌تان کنیم؟">{{ old('message') }}</textarea>
                            <p class="mt-2 text-xs text-[var(--sg-stone)]">حداقل ۱۰ و حداکثر ۵۰۰۰ نویسه.</p>
                            @error('message')<p class="mt-2 text-xs text-[var(--sg-danger)]">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-xl bg-[var(--sg-ink)] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[var(--sg-accent)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--sg-accent)]">ارسال پیام</button>
                        <p class="text-center text-xs leading-6 text-[var(--sg-stone)]">اطلاعات شما فقط برای پیگیری همین درخواست استفاده می‌شود.</p>
                    </form>
                </div>
            </div>
        </x-layout.container>
    </section>

    @if($faqs->isNotEmpty())
        <section class="border-t border-[var(--sg-border)] bg-[var(--sg-white)]">
            <x-layout.container>
                <div class="grid gap-8 py-12 sm:py-16 lg:grid-cols-[.7fr_1.3fr] lg:py-20">
                    <div><p class="sg-eyebrow">{{ $pageContent['faq_kicker'] ?? 'پرسش‌های متداول' }}</p><h2 class="mt-4 text-3xl font-semibold">{{ $pageContent['faq_title'] ?? 'پاسخ پرسش‌های شما' }}</h2><p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">{{ $pageContent['faq_description'] ?? 'پاسخ چند سؤال رایج درباره ارتباط با فروشگاه و خرید.' }}</p></div>
                    <div x-data="{ active: null }" class="space-y-3">
                        @foreach($faqs as $index => $faq)
                            <article class="overflow-hidden rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)]">
                                <button type="button" @click="active === {{ $index }} ? active = null : active = {{ $index }}" :aria-expanded="active === {{ $index }} ? 'true' : 'false'" class="flex w-full items-center justify-between gap-4 px-5 py-5 text-right">
                                    <span class="text-sm font-semibold">{{ $faq['question'] }}</span><span aria-hidden="true" class="text-lg text-[var(--sg-accent)]" x-text="active === {{ $index }} ? '−' : '+'"></span>
                                </button>
                                <div x-show="active === {{ $index }}" x-collapse x-cloak class="border-t border-[var(--sg-border)] px-5 py-4 text-sm leading-8 text-[var(--sg-stone)]">{{ $faq['answer'] }}</div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </x-layout.container>
        </section>
    @endif
</div>
@endsection
