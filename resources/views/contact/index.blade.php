@extends('layouts.app')

@section('title', 'تماس با LIVORA | ارتباط با ما')

@section(
    'description',
    'برای مشاوره خرید، پیگیری سفارش و دریافت اطلاعات بیشتر درباره محصولات و خدمات LIVORA با ما در ارتباط باشید.'
)

@section('canonical', route('contact'))

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="تماس با LIVORA | ارتباط با ما"
    >

    <meta
        property="og:description"
        content="برای مشاوره خرید، پیگیری سفارش و دریافت اطلاعات بیشتر با LIVORA در ارتباط باشید."
    >

    <meta
        property="og:url"
        content="{{ route('contact') }}"
    >

    <meta
        name="twitter:card"
        content="summary"
    >

    <meta
        name="twitter:title"
        content="تماس با LIVORA"
    >

    <meta
        name="twitter:description"
        content="راه‌های ارتباطی و پشتیبانی LIVORA."
    >

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "LIVORA",
    "url": @json(url('/')),
    "potentialAction": {
        "@@type": "SearchAction",
        "target": @json(url('/shop') . '?search={search_term_string}'),
        "query-input": "required name=search_term_string"
    }
}
</script>

{{--    <script type="application/ld+json">--}}
{{--{--}}
{{--    "@@context": "https://schema.org",--}}
{{--    "@@type": "Organization",--}}
{{--    "name": "LIVORA",--}}
{{--    "url": @json(url('/'))--}}
{{--        }--}}
{{--</script>--}}
@endpush

@section('content')

    <div class="overflow-hidden bg-[var(--livora-cream)]">

        {{-- =========================================================
             HERO
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-16 lg:py-20">

                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        تماس با ما
                    </span>

                    </nav>

                    <div class="mt-10 grid items-end gap-10 lg:grid-cols-[1.1fr_0.9fr]">

                        <div class="max-w-4xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                                GET IN TOUCH
                            </p>

                            <h1 class="mt-5 text-5xl font-semibold leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">
                                برای انتخاب بهتر،
                                <span class="block text-[var(--livora-accent)]">
                                کنار شما هستیم.
                            </span>
                            </h1>

                        </div>

                        <div>

                            <p class="text-sm leading-8 text-[var(--livora-stone)] sm:text-base">
                                برای مشاوره خرید، پیگیری سفارش، پرسش درباره شرایط اقساط
                                یا هر موضوع دیگری می‌توانید با LIVORA در ارتباط باشید.
                            </p>

                            <a
                                href="#contact-form"
                                class="mt-7 inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                ارسال پیام
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             CONTACT METHODS
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)]">

            <x-layout.container>

                <div class="grid gap-4 py-10 sm:grid-cols-2 lg:grid-cols-4">

                    <a
                        href="tel:{{ config('app.contact.phone') }}"
                        class="group rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-ink)]"
                    >

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                            01
                        </div>

                        <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            PHONE
                        </p>

                        <h2 class="mt-2 text-base font-semibold">
                            تماس تلفنی
                        </h2>

                        <p class="mt-3 break-all text-xs leading-6 text-[var(--livora-stone)]">
                            {{ config('app.contact.phone', 'شماره تماس شما') }}
                        </p>

                    </a>


                    <a
                        href="mailto:{{ config('app.contact.email') }}"
                        class="group rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-ink)]"
                    >

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                            02
                        </div>

                        <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            EMAIL
                        </p>

                        <h2 class="mt-2 text-base font-semibold">
                            ایمیل
                        </h2>

                        <p class="mt-3 break-all text-xs leading-6 text-[var(--livora-stone)]">
                            {{ config('app.contact.email', 'ایمیل شما') }}
                        </p>

                    </a>


                    <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                            03
                        </div>

                        <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            SUPPORT
                        </p>

                        <h2 class="mt-2 text-base font-semibold">
                            پشتیبانی
                        </h2>

                        <p class="mt-3 text-xs leading-6 text-[var(--livora-stone)]">
                            پاسخ‌گویی به پرسش‌های خرید و سفارش.
                        </p>

                    </div>


                    <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-xs font-semibold">
                            04
                        </div>

                        <p class="mt-7 text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            ONLINE
                        </p>

                        <h2 class="mt-2 text-base font-semibold">
                            ارتباط آنلاین
                        </h2>

                        <p class="mt-3 text-xs leading-6 text-[var(--livora-stone)]">
                            پیام خود را ارسال کنید تا با شما تماس بگیریم.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FORM + INFO
        ========================================================== --}}
        <section id="contact-form">

            <x-layout.container>

                <div class="grid gap-8 py-14 sm:py-18 lg:grid-cols-[0.8fr_1.2fr] lg:py-24">

                    {{-- INFO --}}
                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            LET'S TALK
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            چه کمکی از دست ما برمی‌آید؟
                        </h2>

                        <p class="mt-5 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                            اگر درباره محصول، موجودی، قیمت، شرایط خرید اقساطی،
                            ارسال یا سفارش خود سؤالی دارید، پیام بگذارید.
                        </p>


                        {{-- Contact Details --}}
                        <div class="mt-10 space-y-3">

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    ADDRESS
                                </p>

                                <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                    {{ config('app.contact.address', 'آدرس فروشگاه در این قسمت قرار می‌گیرد.') }}
                                </p>

                            </div>


                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    HOURS
                                </p>

                                <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                    {{ config('app.contact.hours', 'ساعات کاری فروشگاه') }}
                                </p>

                            </div>


                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    INSTALLMENT
                                </p>

                                <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                    برای اطلاع از شرایط اقساط هر محصول،
                                    صفحه همان محصول را بررسی کنید یا از طریق فرم با ما تماس بگیرید.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- FORM --}}
                    <div class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8 lg:p-10">

                        <div class="mb-8">

                            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                CONTACT FORM
                            </p>

                            <h2 class="mt-3 text-2xl font-semibold">
                                پیام خود را ارسال کنید
                            </h2>

                            <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                اطلاعات تماس خود را وارد کنید تا بتوانیم پاسخ دقیق‌تری ارائه دهیم.
                            </p>

                        </div>


                        {{--
                            این فرم را فعلاً به route جدید وصل نمی‌کنیم.
                            چون در پروژه فعلی route پردازش فرم تماس نداریم.
                        --}}
                        <form
                            action="#"
                            method="POST"
                            class="space-y-5"
                            onsubmit="return false;"
                        >

                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">

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
                                        value="{{ old('name', auth()->user()->name ?? '') }}"
                                        class="admin-input mt-2 w-full"
                                        autocomplete="name"
                                        placeholder="نام شما"
                                    >

                                </div>


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
                                        value="{{ old('phone', auth()->user()->phone ?? '') }}"
                                        class="admin-input mt-2 w-full"
                                        autocomplete="tel"
                                        dir="ltr"
                                        placeholder="09xxxxxxxxx"
                                    >

                                </div>

                            </div>


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
                                    value="{{ old('email', auth()->user()->email ?? '') }}"
                                    class="admin-input mt-2 w-full"
                                    autocomplete="email"
                                    dir="ltr"
                                    placeholder="you@example.com"
                                >

                            </div>


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
                                    class="admin-select mt-2 w-full"
                                >

                                    <option value="">
                                        انتخاب موضوع
                                    </option>

                                    <option value="product">
                                        مشاوره درباره محصول
                                    </option>

                                    <option value="installment">
                                        شرایط خرید اقساطی
                                    </option>

                                    <option value="order">
                                        پیگیری سفارش
                                    </option>

                                    <option value="shipping">
                                        ارسال و تحویل
                                    </option>

                                    <option value="other">
                                        سایر
                                    </option>

                                </select>

                            </div>


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
                                    class="admin-textarea mt-2 w-full"
                                    placeholder="پیام خود را بنویسید..."
                                ></textarea>

                            </div>


                            <div class="rounded-2xl bg-[var(--livora-surface)] p-4">

                                <p class="text-[11px] leading-7 text-[var(--livora-stone)]">
                                    فرم تماس در محیط فعلی به‌صورت نمایشی آماده شده است.
                                    پس از ساخت Controller و endpoint تماس، ارسال واقعی این فرم فعال می‌شود.
                                </p>

                            </div>


                            <button
                                type="submit"
                                disabled
                                class="w-full cursor-not-allowed rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white opacity-50"
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
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div
                    x-data="{
                    active: null
                }"
                    class="grid gap-10 py-14 sm:py-18 lg:grid-cols-[0.7fr_1.3fr] lg:py-24"
                >

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            FAQ
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight">
                            پرسش‌های متداول
                        </h2>

                        <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                            پاسخ چند سؤال رایج درباره ارتباط با LIVORA و خرید از فروشگاه.
                        </p>

                    </div>


                    <div class="space-y-3">

                        @php
                            $faqs = [
                                [
                                    'question' => 'چطور شرایط اقساط یک محصول را ببینم؟',
                                    'answer' => 'اگر محصول قابلیت خرید اقساطی داشته باشد، شرایط پیش‌پرداخت، تعداد چک و فاصله سررسید در صفحه همان محصول نمایش داده می‌شود.',
                                ],
                                [
                                    'question' => 'چطور وضعیت سفارش خود را پیگیری کنم؟',
                                    'answer' => 'پس از ورود به حساب کاربری، از بخش سفارش‌ها می‌توانید وضعیت سفارش، پرداخت و در صورت وجود برنامه اقساط را مشاهده کنید.',
                                ],
                                [
                                    'question' => 'برای مشاوره خرید چگونه با شما تماس بگیرم؟',
                                    'answer' => 'می‌توانید از اطلاعات تماس بالای همین صفحه استفاده کنید یا پیام خود را از فرم تماس برای ما ارسال کنید.',
                                ],
                                [
                                    'question' => 'آیا شرایط همه محصولات اقساطی یکسان است؟',
                                    'answer' => 'خیر. شرایط اقساط توسط فروشگاه برای هر محصول تعریف می‌شود و ممکن است درصد پیش‌پرداخت، تعداد چک و فاصله سررسید متفاوت باشد.',
                                ],
                            ];
                        @endphp

                        @foreach($faqs as $index => $faq)

                            <article class="overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-cream)]">

                                <button
                                    type="button"
                                    @click="active === {{ $index }} ? active = null : active = {{ $index }}"
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
                                        class="h-4 w-4 shrink-0 transition-transform"
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
                                    <div class="border-t border-[var(--livora-border)] px-5 py-5 text-xs leading-7 text-[var(--livora-stone)] sm:px-6">
                                        {{ $faq['answer'] }}
                                    </div>
                                </div>

                            </article>

                        @endforeach

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            LIVORA
                        </p>

                        <h2 class="mt-2 text-2xl font-semibold">
                            آماده‌ای انتخابت را پیدا کنی؟
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                            مجموعه محصولات را ببین و انتخاب بعدی‌ات را پیدا کن.
                        </p>

                    </div>

                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex w-fit rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                    >
                        ورود به فروشگاه
                    </a>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
