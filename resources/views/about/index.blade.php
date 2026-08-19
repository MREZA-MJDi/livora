@extends('layouts.app')

@section('title', 'درباره LIVORA | مبلمان و سبک زندگی')

@section(
    'description',
    'با LIVORA آشنا شوید؛ مجموعه‌ای برای انتخاب مبلمان و عناصر خانه با تمرکز بر طراحی، کیفیت، تجربه خرید و پرداخت منعطف.'
)

@section('canonical', route('about'))

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="درباره LIVORA | مبلمان و سبک زندگی"
    >

    <meta
        property="og:description"
        content="LIVORA برای انتخاب دقیق‌تر، خرید ساده‌تر و ساختن خانه‌ای ماندگار شکل گرفته است."
    >

    <meta
        property="og:url"
        content="{{ route('about') }}"
    >

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="درباره LIVORA"
    >

    <meta
        name="twitter:description"
        content="داستان، فلسفه و تجربه برند LIVORA."
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

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "LIVORA",
    "url": @json(url('/'))
        }
</script>

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
                        درباره ما
                    </span>

                    </nav>

                    <div class="mt-10 grid items-end gap-10 lg:grid-cols-[1fr_0.8fr]">

                        <div class="max-w-4xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                                ABOUT LIVORA
                            </p>

                            <h1 class="mt-5 text-5xl font-semibold leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">
                                خانه فقط یک فضا نیست.
                                <span class="block text-[var(--livora-accent)]">
                                بخشی از زندگی ماست.
                            </span>
                            </h1>

                        </div>

                        <div>

                            <p class="text-sm leading-8 text-[var(--livora-stone)] sm:text-base">
                                LIVORA با یک ایده ساده شکل گرفته است:
                                انتخاب مبلمان نباید فقط خرید یک محصول باشد؛
                                باید بخشی از ساختن فضایی باشد که هر روز در آن زندگی می‌کنیم.
                            </p>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-7 inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                کشف مجموعه LIVORA
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             BRAND STATEMENT
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)]">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.7fr_1.3fr] lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            OUR APPROACH
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            کمتر، اما بهتر انتخاب کن.
                        </h2>

                    </div>

                    <div class="max-w-3xl">

                        <p class="text-lg leading-9 text-[var(--livora-ink)] sm:text-xl">
                            ما باور داریم محصول خوب محصولی نیست که فقط در عکس زیبا باشد؛
                            باید در استفاده روزمره، در کیفیت متریال، در تناسب با فضا
                            و در سال‌هایی که کنار شما می‌ماند هم ارزش خودش را نشان دهد.
                        </p>

                        <p class="mt-6 text-sm leading-8 text-[var(--livora-stone)]">
                            به همین دلیل در LIVORA تلاش می‌کنیم تجربه کشف و خرید را
                            ساده، شفاف و بدون شلوغی نگه داریم؛ از اطلاعات محصول و قیمت
                            گرفته تا شرایط پرداخت و مسیر سفارش.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             VALUES
        ========================================================== --}}
        <section class="bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-16 sm:py-20 lg:py-24">

                    <div class="max-w-2xl">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            WHAT MATTERS
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            چیزهایی که برای ما مهم‌اند.
                        </h2>

                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            01
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                طراحی
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                فرم و ظاهر باید در خدمت فضای شما باشد، نه اینکه فضا را تحت‌الشعاع قرار دهد.
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            02
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                کیفیت
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                انتخاب متریال و ساخت خوب، چیزی است که ارزش یک محصول را در طول زمان حفظ می‌کند.
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            03
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                شفافیت
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                قیمت، مشخصات، موجودی و شرایط پرداخت باید قبل از تصمیم‌گیری روشن باشند.
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            04
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                تجربه
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                خرید باید ساده و آرام باشد؛ از اولین مشاهده محصول تا سفارش نهایی.
                            </p>

                        </article>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             PROCESS
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.65fr_1.35fr] lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            THE EXPERIENCE
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            از کشف تا انتخاب.
                        </h2>

                        <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                            تجربه LIVORA فقط به صفحه محصول محدود نمی‌شود.
                            تمام مسیر برای انتخاب راحت‌تر طراحی شده است.
                        </p>

                    </div>

                    <div class="space-y-3">

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                01
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        کشف
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        دسته‌بندی‌ها و مجموعه‌های مختلف را بررسی می‌کنید و محصول مناسب فضای خود را پیدا می‌کنید.
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                02
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        بررسی
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        قیمت، مشخصات، موجودی و در صورت وجود، شرایط خرید اقساطی محصول را می‌بینید.
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                03
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        انتخاب
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        محصول و ویژگی‌های موردنظر را انتخاب و وارد سبد خرید می‌کنید.
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                04
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        پرداخت
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        مسیر پرداخت متناسب با سفارش انتخاب می‌شود و شرایط اقساطی در صورت فعال بودن، شفاف نمایش داده خواهد شد.
                                    </p>

                                </div>

                            </div>

                        </article>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSTALLMENT
        ========================================================== --}}
        <section class="border-t border-white/10 bg-[var(--livora-ink)] text-white">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-white/45">
                            FLEXIBLE PAYMENT
                        </p>

                        <h2 class="mt-4 max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">
                            انتخاب خوب نباید فقط به بودجه امروز محدود شود.
                        </h2>

                        <p class="mt-5 max-w-2xl text-sm leading-8 text-white/55">
                            برخی محصولات LIVORA می‌توانند با شرایط اقساطی تعریف‌شده توسط فروشگاه خریداری شوند.
                            درصد پیش‌پرداخت، تعداد چک و فاصله سررسید برای هر محصول مشخص است.
                        </p>

                        <a
                            href="{{ route('shop.index') }}"
                            class="mt-8 inline-flex items-center rounded-2xl bg-white px-6 py-4 text-sm font-medium text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]"
                        >
                            مشاهده محصولات
                        </a>

                    </div>

                    <div class="grid grid-cols-2 gap-3">

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                                TODAY
                            </p>

                            <p class="mt-4 text-3xl font-semibold">
                                50٪
                            </p>

                            <p class="mt-2 text-xs leading-6 text-white/40">
                                نمونه پیش‌پرداخت
                            </p>

                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                                SCHEDULE
                            </p>

                            <p class="mt-4 text-3xl font-semibold">
                                2+
                            </p>

                            <p class="mt-2 text-xs leading-6 text-white/40">
                                فقره چک قابل تنظیم
                            </p>

                        </div>

                        <div class="col-span-2 rounded-3xl border border-white/10 bg-white/[0.07] p-5 sm:p-6">

                            <p class="text-sm font-semibold">
                                شرایط هر محصول در صفحه خودش نمایش داده می‌شود.
                            </p>

                            <p class="mt-2 text-xs leading-7 text-white/45">
                                قبل از افزودن محصول به سبد، مبلغ پیش‌پرداخت و برنامه تسویه را بررسی کنید.
                            </p>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL STATEMENT
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-20 sm:py-28">

                    <div class="mx-auto max-w-3xl text-center">

                        <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                            LIVORA
                        </p>

                        <h2 class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
                            برای فضاهایی که قرار است ماندگار باشند.
                        </h2>

                        <p class="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                            مجموعه LIVORA را ببین و محصولی را پیدا کن که فقط خانه را پر نکند؛
                            بلکه بخشی از شخصیت آن شود.
                        </p>

                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                ورود به فروشگاه
                            </a>

                            <a
                                href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-7 py-4 text-sm font-medium text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)]"
                            >
                                تماس با ما
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
