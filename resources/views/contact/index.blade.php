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
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq['answer'],
        ],
    ])->values()->all(),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endif
@endpush



@section('content')

    <div class="overflow-hidden bg-[var(--sg-cream)]">

        {{-- =========================================================
             HERO
        ========================================================== --}}

        <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-16 lg:py-20">

                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--sg-stone)]"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--sg-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--sg-ink)]">
                            {{ $pageContent['breadcrumb_label'] ?? 'تماس با ما' }}
                        </span>

                    </nav>


                    <div class="mt-10 grid items-end gap-10 lg:grid-cols-[1.1fr_0.9fr]">

                        <div class="max-w-4xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--sg-accent)]">
                                {{ $pageContent['hero_kicker'] ?? 'ارتباط با ما' }}
                            </p>

                            <h1 class="mt-5 text-5xl font-semibold leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">
                                {{ $pageContent['hero_title'] ?? 'برای انتخاب بهتر،' }}
                                <span class="block text-[var(--sg-accent)]">
                                    {{ $pageContent['hero_highlight'] ?? 'کنار شما هستیم.' }}
                                </span>
                            </h1>

                        </div>


                        <div>

                            <p class="text-sm leading-8 text-[var(--sg-stone)] sm:text-base">
                                {{ $pageContent['hero_description'] ?? 'برای مشاوره خرید، پیگیری سفارش یا هر موضوع دیگری می‌توانید با ما در ارتباط باشید.' }}
                            </p>

                            <a
                                href="#contact-form"
                                class="mt-7 inline-flex items-center rounded-2xl bg-[var(--sg-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]"
                            >
                                {{ $pageContent['hero_cta'] ?? 'ارسال پیام' }}
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             CONTACT METHODS
        ========================================================== --}}

        <section class="border-b border-[var(--sg-border)]">

            <x-layout.container>

                <div class="grid gap-4 py-10 sm:grid-cols-2 lg:grid-cols-4">

                    {{-- PHONE --}}

                    @if($contactSetting?->phone)

                        <a
                            href="tel:{{ $contactSetting->phone }}"
                            class="group rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--sg-ink)]"
                        >

                            @else

                                <div
                                    class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6"
                                >

                                    @endif

                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-xs font-semibold">
                                        01
                                    </div>

                                    <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                        تلفن
                                    </p>

                                    <h2 class="mt-2 text-base font-semibold">
                                        {{ $contactSetting?->phone_label ?? 'تماس تلفنی' }}
                                    </h2>

                                    <p class="mt-3 break-all text-xs leading-6 text-[var(--sg-stone)]">
                                        {{ $contactSetting?->phone }}
                                    </p>

                                @if($contactSetting?->phone)

                        </a>

                    @else

                </div>

                @endif


                {{-- EMAIL --}}

                @if($contactSetting?->email)

                    <a
                        href="mailto:{{ $contactSetting->email }}"
                        class="group rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--sg-ink)]"
                    >

                        @else

                            <div
                                class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6"
                            >

                                @endif

                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-xs font-semibold">
                                    02
                                </div>

                                <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                    ایمیل
                                </p>

                                <h2 class="mt-2 text-base font-semibold">
                                    {{ $contactSetting?->email_label ?? 'ایمیل' }}
                                </h2>

                                <p class="mt-3 break-all text-xs leading-6 text-[var(--sg-stone)]">
                                {{ $contactSetting?->email }}
                                </p>

                            @if($contactSetting?->email)

                    </a>

        @else

    </div>

    @endif


    {{-- SUPPORT --}}

    @if(filled($contactSetting?->support_description))<div class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6">

        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-xs font-semibold">
            03
        </div>

        <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
            پشتیبانی
        </p>

        <h2 class="mt-2 text-base font-semibold">
            {{ $contactSetting?->support_label ?? 'پشتیبانی' }}
        </h2>

        <p class="mt-3 text-xs leading-6 text-[var(--sg-stone)]">
            {{ $contactSetting->support_description }}
        </p>

    </div>@endif


    {{-- ONLINE --}}

    @if(filled($contactSetting?->online_description))<div class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6">

        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-xs font-semibold">
            04
        </div>

        <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
            ارتباط آنلاین
        </p>

        <h2 class="mt-2 text-base font-semibold">
            {{ $contactSetting?->online_label ?? 'ارتباط آنلاین' }}
        </h2>

        <p class="mt-3 text-xs leading-6 text-[var(--sg-stone)]">
            {{ $contactSetting->online_description }}
        </p>

    </div>@endif

    </div>

    </x-layout.container>

    </section>


    {{-- =========================================================
         FORM + INFO
    ========================================================== --}}

    <section id="contact-form">

        <x-layout.container>

            <div class="grid gap-8 py-14 sm:py-16 lg:grid-cols-[0.8fr_1.2fr] lg:py-24">

                {{-- INFO --}}

                <div>

                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                        {{ $pageContent['info_kicker'] ?? 'پشتیبانی' }}
                    </p>

                    <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                        {{ $pageContent['info_title'] ?? 'چه کمکی از دست ما برمی‌آید؟' }}
                    </h2>

                    <p class="mt-5 max-w-xl text-sm leading-8 text-[var(--sg-stone)]">
                        {{ $pageContent['info_description'] ?? 'اگر درباره محصول، موجودی، قیمت، شرایط خرید اقساطی، ارسال یا سفارش خود سؤالی دارید، پیام بگذارید.' }}
                    </p>


                    {{-- ADDRESS --}}

                    <div class="mt-10 space-y-3">

                        @if(filled($contactSetting?->address))<div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                {{ $pageContent['address_label'] ?? 'آدرس' }}
                            </p>

                            <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">
                                {{ $contactSetting->address }}
                            </p>

                        </div>@endif


                        {{-- HOURS --}}

                        @if(filled($contactSetting?->hours))<div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                {{ $pageContent['hours_label'] ?? 'ساعات کاری' }}
                            </p>

                            <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">
                                {{ $contactSetting->hours }}
                            </p>

                        </div>@endif


                        {{-- INSTALLMENT --}}

                        @if(filled($pageContent['installment_note'] ?? null))<div class="rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-5">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                {{ $pageContent['installment_label'] ?? 'اقساط' }}
                            </p>

                            <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">
                                {{ $pageContent['installment_note'] }}
                            </p>

                        </div>@endif

                    </div>

                </div>


                {{-- FORM --}}

                <div class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-6 sm:p-8 lg:p-10">

                    <div class="mb-8">

                        <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--sg-accent)]">
                            {{ $pageContent['form_kicker'] ?? 'فرم تماس' }}
                        </p>

                        <h2 class="mt-3 text-2xl font-semibold">
                            {{ $pageContent['form_title'] ?? 'پیام خود را ارسال کنید' }}
                        </h2>

                        <p class="mt-2 text-xs leading-7 text-[var(--sg-stone)]">
                            {{ $pageContent['form_description'] ?? 'اطلاعات تماس خود را وارد کنید تا بتوانیم پاسخ دقیق‌تری ارائه دهیم.' }}
                        </p>

                    </div>


                    {{-- SUCCESS MESSAGE --}}

                    @if(session('success'))

                        <div class="mb-6 rounded-2xl border border-[var(--sg-success)]/20 bg-[var(--sg-success-soft)] p-4">

                            <p class="text-xs leading-7 text-[var(--sg-success)]">
                                {{ session('success') }}
                            </p>

                        </div>

                    @endif


                    {{-- VALIDATION ERRORS --}}

                    @if($errors->any())

                        <div class="mb-6 rounded-2xl border border-[var(--sg-danger)]/20 bg-[var(--sg-danger-soft)] p-4">

                            <ul class="space-y-1 text-xs leading-6 text-[var(--sg-danger)]">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- CONTACT FORM --}}

                    <form
                        action="{{ route('contact.store') }}"
                        method="POST"
                        class="space-y-5"
                    >

                        @csrf


                        {{-- NAME + PHONE --}}

                        <div class="grid gap-5 sm:grid-cols-2">

                            {{-- NAME --}}

                            <div>

                                <label
                                    for="name"
                                    class="admin-label"
                                >
                                    نام و نام خانوادگی
                                </label>

                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    value="{{ old('name', auth()->user()?->name) }}"
                                    class="admin-input mt-2 w-full @error('name') border-[var(--sg-danger)] @enderror"
                                    autocomplete="name"
                                    placeholder="نام شما"
                                >

                                @error('name')

                                <p class="mt-1 text-[11px] text-[var(--sg-danger)]">
                                    {{ $message }}
                                </p>

                                @enderror

                            </div>


                            {{-- PHONE --}}

                            <div>

                                <label
                                    for="phone"
                                    class="admin-label"
                                >
                                    شماره تماس
                                </label>

                                <input
                                    id="phone"
                                    name="phone"
                                    type="tel"
                                    value="{{ old('phone', auth()->user()?->phone) }}"
                                    class="admin-input mt-2 w-full @error('phone') border-[var(--sg-danger)] @enderror"
                                    autocomplete="tel"
                                    dir="ltr"
                                    placeholder="09xxxxxxxxx"
                                >

                                @error('phone')

                                <p class="mt-1 text-[11px] text-[var(--sg-danger)]">
                                    {{ $message }}
                                </p>

                                @enderror

                            </div>

                        </div>


                        {{-- EMAIL --}}

                        <div>

                            <label
                                for="email"
                                class="admin-label"
                            >
                                ایمیل
                            </label>

                            <input
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email', auth()->user()?->email) }}"
                                class="admin-input mt-2 w-full @error('email') border-[var(--sg-danger)] @enderror"
                                autocomplete="email"
                                dir="ltr"
                                placeholder="you@example.com"
                            >

                            @error('email')

                            <p class="mt-1 text-[11px] text-[var(--sg-danger)]">
                                {{ $message }}
                            </p>

                            @enderror

                        </div>


                        {{-- SUBJECT --}}

                        <div>

                            <label
                                for="subject"
                                class="admin-label"
                            >
                                موضوع
                            </label>

                            <select
                                id="subject"
                                name="subject"
                                class="admin-select mt-2 w-full @error('subject') border-[var(--sg-danger)] @enderror"
                            >

                                <option value="">
                                    انتخاب موضوع
                                </option>

                                <option
                                    value="product"
                                    @selected(old('subject') === 'product')
                                >
                                مشاوره درباره محصول
                                </option>

                                <option
                                    value="installment"
                                    @selected(old('subject') === 'installment')
                                >
                                شرایط خرید اقساطی
                                </option>

                                <option
                                    value="order"
                                    @selected(old('subject') === 'order')
                                >
                                پیگیری سفارش
                                </option>

                                <option
                                    value="shipping"
                                    @selected(old('subject') === 'shipping')
                                >
                                ارسال و تحویل
                                </option>

                                <option
                                    value="other"
                                    @selected(old('subject') === 'other')
                                >
                                سایر
                                </option>

                            </select>

                            @error('subject')

                            <p class="mt-1 text-[11px] text-[var(--sg-danger)]">
                                {{ $message }}
                            </p>

                            @enderror

                        </div>


                        {{-- MESSAGE --}}

                        <div>

                            <label
                                for="message"
                                class="admin-label"
                            >
                                پیام
                            </label>

                            <textarea
                                id="message"
                                name="message"
                                rows="7"
                                class="admin-textarea mt-2 w-full @error('message') border-[var(--sg-danger)] @enderror"
                                placeholder="پیام خود را بنویسید..."
                            >{{ old('message') }}</textarea>

                            @error('message')

                            <p class="mt-1 text-[11px] text-[var(--sg-danger)]">
                                {{ $message }}
                            </p>

                            @enderror

                        </div>


                        {{-- FORM NOTE --}}

                        <div class="rounded-2xl bg-[var(--sg-surface)] p-4">

                            <p class="text-[11px] leading-7 text-[var(--sg-stone)]">
                                پیام شما پس از ارسال بررسی می‌شود و در صورت نیاز
                                از طریق اطلاعات تماس واردشده با شما ارتباط خواهیم گرفت.
                            </p>

                        </div>


                        {{-- SUBMIT --}}

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-[var(--sg-ink)] px-6 py-4 text-sm font-medium text-white transition hover:-translate-y-0.5 hover:bg-[var(--sg-accent)]"
                        >
                            ارسال پیام
                        </button>

                    </form>

                </div>

            </div>

        </x-layout.container>

    </section>


    {{-- =========================================================
         FAQ
         FAQ is intentionally static for the first version.
    ========================================================== --}}

    <section class="border-t border-[var(--sg-border)] bg-[var(--sg-white)]">

        <x-layout.container>

            <div
                x-data="{ active: null }"
                class="grid gap-10 py-14 sm:py-16 lg:grid-cols-[0.7fr_1.3fr] lg:py-24"
            >

                <div>

                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                        {{ $pageContent['faq_kicker'] ?? 'پرسش‌های متداول' }}
                    </p>

                    <h2 class="mt-4 text-3xl font-semibold tracking-tight">
                        {{ $pageContent['faq_title'] ?? 'پرسش‌های متداول' }}
                    </h2>

                    <p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">
                        {{ $pageContent['faq_description'] ?? 'پاسخ چند سؤال رایج درباره ارتباط با فروشگاه و خرید.' }}
                    </p>

                </div>


                <div class="space-y-3">

                    @forelse($faqs as $index => $faq)

                        <article class="overflow-hidden rounded-3xl border border-[var(--sg-border)] bg-[var(--sg-cream)]">

                            <button
                                type="button"
                                @click="active === {{ $index }} ? active = null : active = {{ $index }}"
                                :aria-expanded="active === {{ $index }} ? 'true' : 'false'"
                                class="flex w-full items-center justify-between gap-5 px-5 py-5 text-right sm:px-6"
                            >

                                    <span class="text-sm font-semibold">
                                        {{ $faq['question'] }}
                                    </span>

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-4 w-4 shrink-0 transition-transform duration-300"
                                    :class="active === {{ $index }} ? 'rotate-180' : ''"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m19.5 8.25-7.5 7.5-7.5-7.5"
                                    />
                                </svg>

                            </button>


                            <div
                                x-show="active === {{ $index }}"
                                x-collapse
                                x-cloak
                            >

                                <div class="border-t border-[var(--sg-border)] px-5 py-5 text-xs leading-7 text-[var(--sg-stone)] sm:px-6">
                                    {{ $faq['answer'] }}
                                </div>

                            </div>

                        </article>

                    @empty @endforelse

                </div>

            </div>

        </x-layout.container>

    </section>


    {{-- =========================================================
         FINAL CTA
    ========================================================== --}}

    <section class="border-t border-[var(--sg-border)] bg-[var(--sg-surface)]">

        <x-layout.container>

            <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                <div>

                    <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--sg-accent)]">
                        {{ $pageContent['final_kicker'] ?? ($storeSettings?->brand_name ?: config('app.name')) }}
                    </p>

                    <h2 class="mt-2 text-2xl font-semibold">
                        {{ $pageContent['final_title'] ?? 'آماده‌ای انتخابت را پیدا کنی؟' }}
                    </h2>

                    <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">
                        {{ $pageContent['final_description'] ?? 'مجموعه محصولات را ببین و انتخاب بعدی‌ات را پیدا کن.' }}
                    </p>

                </div>


                <a
                    href="{{ route('shop.index') }}"
                    class="inline-flex w-fit rounded-2xl bg-[var(--sg-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]"
                >
                    {{ $pageContent['final_cta'] ?? 'ورود به فروشگاه' }}
                </a>

            </div>

        </x-layout.container>

    </section>

    </div>

@endsection
