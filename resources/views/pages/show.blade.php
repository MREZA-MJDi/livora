@extends('layouts.app')

@php
    $siteBrandName = $storeSettings?->brand_name ?: config('app.name');
    $isAboutPage = $page->slug === 'about';
    $aboutExcerpt = 'با سیلا گالری بیشتر آشنا شوید؛ نگاهی به شیوه انتخاب محصولات، تجربه خرید و راه‌های ارتباط با ما.';
@endphp

@section('title', $page->meta_title ?: ($page->title . ' | ' . $siteBrandName))
@section('description', $page->meta_description ?: ($page->excerpt ?: ($isAboutPage ? $aboutExcerpt : 'اطلاعات و مطالب ' . $siteBrandName)))
@section('robots', $robots)
@section('canonical', $page->canonical_url ?: route('pages.show', $page->slug))
@section('og_image', $page->og_image_url ?: $page->featured_image_url)
@section('og_type', 'article')

@section('content')
@php
    $body = (string) $page->body;
    $body = preg_replace_callback('/<(h1|h2)\\b[^>]*>(.*?)<\\/\\1>/is', function ($match) use ($page) {
        return trim(preg_replace('/\\s+/u', ' ', strip_tags($match[2]))) === trim($page->title)
            ? ''
            : $match[0];
    }, $body, 1);
@endphp
<section class="bg-[var(--sg-cream)] py-8 sm:py-12 lg:py-16">
    <x-layout.container>
        <nav aria-label="مسیر صفحه" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--sg-stone)]">
            <a href="{{ route('home') }}" class="transition hover:text-[var(--sg-ink)]">خانه</a>
            <span aria-hidden="true">/</span>
            <span class="text-[var(--sg-ink)]">{{ $page->title }}</span>
        </nav>
        <article class="mx-auto max-w-5xl overflow-hidden rounded-[1.75rem] border border-[var(--sg-border)] bg-[var(--sg-white)] shadow-[0_20px_70px_rgba(35,29,23,.06)]">
            @if($page->featured_image_url)
                <figure class="relative max-h-[560px] overflow-hidden bg-[var(--sg-surface)]">
                    <img src="{{ $page->featured_image_url }}" alt="{{ $page->featured_image_alt ?: $page->title }}" class="max-h-[560px] w-full object-cover" fetchpriority="high">
                </figure>
            @endif
            <header class="border-b border-[var(--sg-border)] px-6 py-8 sm:px-10 sm:py-12 lg:px-16">
                <p class="sg-eyebrow">{{ $isAboutPage ? 'SILLAGALLERY · OUR STORY' : $siteBrandName }}</p>
                <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight text-[var(--sg-ink)] sm:text-4xl lg:text-5xl">{{ $page->title }}</h1>
                @if($page->excerpt || $isAboutPage)
                    <p class="mt-5 max-w-3xl text-sm leading-8 text-[var(--sg-stone)] sm:text-base">{{ $page->excerpt ?: $aboutExcerpt }}</p>
                @endif
                <span class="mt-7 block h-px w-16 bg-[var(--sg-accent)]"></span>
            </header>

            @if(trim($body) !== '')
                <div class="cms-content px-6 py-8 sm:px-10 sm:py-12 lg:px-16 lg:py-14">
                    {!! $body !!}
                </div>
            @elseif($isAboutPage)
                <div class="space-y-10 px-6 py-8 sm:px-10 sm:py-12 lg:px-16 lg:py-14">
                    <section class="max-w-3xl">
                        <p class="sg-eyebrow">OUR APPROACH</p>
                        <h2 class="mt-3 text-2xl font-semibold leading-9 text-[var(--sg-ink)]">خرید خوب، از یک انتخاب روشن شروع می‌شود</h2>
                        <p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">سیلا گالری با این نگاه شکل گرفته است که خرید اینترنتی باید ساده، قابل‌فهم و دلنشین باشد. می‌خواهیم پیش از ثبت سفارش، فرصت کافی برای دیدن محصولات، مقایسه انتخاب‌ها و بررسی اطلاعات هر کالا در اختیار شما باشد.</p>
                    </section>
                    <div class="grid gap-4 md:grid-cols-3">
                        <article class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)] p-5">
                            <span class="text-xs font-semibold tracking-wider text-[var(--sg-accent)]">01 · انتخاب</span>
                            <h3 class="mt-3 font-semibold text-[var(--sg-ink)]">با حوصله بررسی کنید</h3>
                            <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">تصاویر، مشخصات و اطلاعات درج‌شده برای هر محصول را ببینید و گزینه‌های مناسب خودتان را پیدا کنید.</p>
                        </article>
                        <article class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)] p-5">
                            <span class="text-xs font-semibold tracking-wider text-[var(--sg-accent)]">02 · سفارش</span>
                            <h3 class="mt-3 font-semibold text-[var(--sg-ink)]">مراحل خرید را دنبال کنید</h3>
                            <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">محصول موردنظر را به سبد خرید اضافه کنید و پیش از نهایی‌کردن سفارش، جزئیات و مبلغ آن را بررسی کنید.</p>
                        </article>
                        <article class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)] p-5">
                            <span class="text-xs font-semibold tracking-wider text-[var(--sg-accent)]">03 · ارتباط</span>
                            <h3 class="mt-3 font-semibold text-[var(--sg-ink)]">اگر سوالی دارید، با ما در میان بگذارید</h3>
                            <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">برای پرسش درباره محصولات یا روند خرید، از راه‌های ارتباطی ثبت‌شده در صفحه تماس استفاده کنید.</p>
                        </article>
                    </div>
                    <section class="max-w-3xl">
                        <h2 class="text-xl font-semibold text-[var(--sg-ink)]">تعهد ما به تجربه‌ای شفاف‌تر</h2>
                        <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">تلاش می‌کنیم اطلاعات فروشگاه و محصولات تا جای ممکن روشن و به‌روز باشد؛ از معرفی کالا و وضعیت موجودی تا نمایش جزئیات سفارش. بازخوردها و سوال‌های شما کمک می‌کند مسیر خرید را بهتر و ساده‌تر کنیم.</p>
                    </section>
                    <section class="flex flex-col gap-5 rounded-3xl bg-[var(--sg-ink)] p-6 text-[var(--sg-cream)] sm:flex-row sm:items-center sm:justify-between sm:p-8">
                        <div>
                            <p class="text-xs font-semibold tracking-wider text-white/55">READY TO EXPLORE?</p>
                            <h2 class="mt-2 text-xl font-semibold">حالا نگاهی به فروشگاه بیندازید</h2>
                            <p class="mt-2 text-sm leading-7 text-white/65">محصولات را ببینید و انتخاب بعدی‌تان را پیدا کنید.</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center rounded-full bg-[var(--sg-accent)] px-6 py-3 text-sm font-semibold text-[var(--sg-ink)] transition hover:brightness-110">مشاهده فروشگاه</a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">ارتباط با ما</a>
                        </div>
                    </section>
                </div>
            @endif
        </article>
    </x-layout.container>
</section>
@endsection
