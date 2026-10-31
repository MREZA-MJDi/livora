@extends('layouts.app')

@section('title', 'قوانین و شرایط استفاده | SillaGallery')
@section('description', 'شرایط عمومی استفاده از فروشگاه SillaGallery.')

@push('seo')
    <meta name="robots" content="noindex,follow">
@endpush

@section('content')
    <section class="min-h-[70vh] bg-[var(--sg-cream)] py-10 sm:py-16">
        <x-layout.container>
            <nav aria-label="مسیر صفحه" class="mb-8 flex flex-wrap items-center gap-2 text-xs text-[var(--sg-stone)]">
                <a href="{{ route('home') }}" class="hover:text-[var(--sg-ink)]">خانه</a>
                <span aria-hidden="true">/</span>
                <span class="text-[var(--sg-ink)]">قوانین و شرایط</span>
            </nav>

            <article class="mx-auto max-w-4xl overflow-hidden rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)]">
                <header class="border-b border-[var(--sg-border)] p-6 sm:p-10">
                    <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">TERMS OF USE</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">قوانین و شرایط استفاده</h1>
                    <p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">
                        این صفحه چارچوب عمومی استفاده از فروشگاه را توضیح می‌دهد. شرایط نهایی فروش، ارسال، بازگشت کالا، ضمانت و حریم خصوصی باید پیش از راه‌اندازی توسط مالک فروشگاه بازبینی و تکمیل شود.
                    </p>
                </header>

                <div class="space-y-8 p-6 text-sm leading-8 text-[var(--sg-stone)] sm:p-10">
                    <section>
                        <h2 class="text-base font-semibold text-[var(--sg-ink)]">۱. اطلاعات حساب کاربری</h2>
                        <p class="mt-2">کاربر مسئول صحت اطلاعاتی است که هنگام ثبت‌نام، ثبت سفارش یا مدیریت نشانی ارسال وارد می‌کند و باید دسترسی به حساب خود را ایمن نگه دارد.</p>
                    </section>
                    <section>
                        <h2 class="text-base font-semibold text-[var(--sg-ink)]">۲. کالا، قیمت و موجودی</h2>
                        <p class="mt-2">اطلاعات کالا، قیمت و موجودی در صفحه محصول نمایش داده می‌شود. پیش از ثبت سفارش، اطلاعات نهایی سفارش باید در مرحله تأیید بررسی شود. ثبت سفارش به‌تنهایی به معنی تأیید پرداخت یا تضمین موجودی تا زمان تأیید نهایی نیست.</p>
                    </section>
                    <section>
                        <h2 class="text-base font-semibold text-[var(--sg-ink)]">۳. پرداخت و سفارش</h2>
                        <p class="mt-2">وضعیت سفارش و پرداخت در حساب کاربری نمایش داده می‌شود. پرداخت آنلاین فقط پس از تأیید معتبر سرویس پرداخت ثبت می‌شود. کاربر نباید صرفاً بر اساس پیام مرورگر یا بازگشت از درگاه، پرداخت را قطعی فرض کند.</p>
                    </section>
                    <section>
                        <h2 class="text-base font-semibold text-[var(--sg-ink)]">۴. ارسال، مرجوعی و ضمانت</h2>
                        <p class="mt-2">زمان و هزینه ارسال، محدوده خدمت‌رسانی، ضوابط لغو یا مرجوعی و شرایط ضمانت باید در سیاست‌های نهایی فروشگاه مشخص شوند. پیش از خرید، جزئیات مربوط به کالای انتخابی را از فروشگاه استعلام کنید.</p>
                    </section>
                    <section>
                        <h2 class="text-base font-semibold text-[var(--sg-ink)]">۵. حریم خصوصی</h2>
                        <p class="mt-2">اطلاعات حساب، سفارش و نشانی برای ارائه خدمات فروشگاه استفاده می‌شود. شرح دقیق نگهداری داده‌ها، ابزارهای تحلیلی، ارائه‌دهندگان پرداخت و شیوه درخواست حذف یا اصلاح اطلاعات باید در سیاست حریم خصوصی مستقل توضیح داده شود.</p>
                    </section>
                    <section>
                        <h2 class="text-base font-semibold text-[var(--sg-ink)]">۶. پشتیبانی و ارتباط</h2>
                        <p class="mt-2">برای سؤال درباره کالا یا سفارش از صفحه تماس با ما استفاده کنید. اطلاعات تماس معتبر فروشگاه پس از تأیید مالک در تنظیمات محیطی نمایش داده می‌شوند.</p>
                        <a href="{{ route('contact') }}" class="mt-3 inline-flex font-medium text-[var(--sg-accent)] hover:underline">ارتباط با فروشگاه ←</a>
                    </section>
                </div>
            </article>
        </x-layout.container>
    </section>
@endsection
