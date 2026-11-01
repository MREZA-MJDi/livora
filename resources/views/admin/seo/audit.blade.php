@extends('admin.layouts.app')

@section('title', 'ممیزی فنی SEO')
@section('page_title', 'ممیزی فنی SEO')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">GROWTH / TECHNICAL SEO</p>
            <h1 class="admin-title">ممیزی فنی SEO</h1>
            <p class="admin-subtitle mt-2">گزارش از رکوردهای واقعی فروشگاه و تنظیمات ذخیره‌شده؛ بدون امتیاز یا ترافیک ساختگی.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.seo.edit') }}" class="admin-btn admin-btn-secondary">تنظیمات سراسری SEO</a>
            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">مشاهده Sitemap</a>
        </div>
    </header>

    <section class="admin-card flex flex-wrap items-center justify-between gap-4 p-5">
        <div>
            <h2 class="text-base font-bold">قفل انتشار موتورهای جست‌وجو</h2>
            <p class="admin-help mt-2">برای index/follow شدن محتوا، هر دو سوییچ سراسری و مجوز هر رکورد باید روشن باشند.</p>
            <p class="mt-3 font-mono text-xs" dir="ltr">Robots: {{ $settings->robots() }}</p>
        </div>
        @if($globalGateOpen)
            <span class="admin-badge admin-badge-success">قفل سراسری باز است</span>
        @else
            <span class="admin-badge admin-badge-warning">قفل سراسری بسته است</span>
        @endif
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="admin-stat admin-card p-5">
            <p class="admin-stat-label">کل رکوردهای بررسی‌شده</p>
            <p class="mt-2 text-3xl font-bold">{{ number_format($totals['records']) }}</p>
            <p class="admin-help mt-2">محصول، دسته، مقاله و صفحه ثابت/محتوایی</p>
        </article>
        <article class="admin-stat admin-card p-5">
            <p class="admin-stat-label">دارای مجوز محتوایی Index + Follow</p>
            <p class="mt-2 text-3xl font-bold">{{ number_format($totals['eligible']) }}</p>
            <p class="admin-help mt-2">قبل از اعمال قفل سراسری</p>
        </article>
        <article class="admin-stat admin-card p-5">
            <p class="admin-stat-label">قابل ایندکس در وضعیت فعلی</p>
            <p class="mt-2 text-3xl font-bold">{{ number_format($globalGateOpen ? $totals['eligible'] : 0) }}</p>
            <p class="admin-help mt-2">خروجی واقعی ترکیب قفل سراسری و مجوز رکورد</p>
        </article>
        <article class="admin-stat admin-card p-5">
            <p class="admin-stat-label">تصویر بدون Alt</p>
            <p class="mt-2 text-3xl font-bold">{{ number_format($missingImageAlt) }}</p>
            <p class="admin-help mt-2">تصاویر شاخص محصول، مقاله و صفحه منتشرشده</p>
        </article>
    </section>

    <section class="admin-card overflow-hidden">
        <div class="border-b border-[var(--admin-border)] p-5">
            <h2 class="text-base font-bold">پوشش متادیتا بر اساس نوع محتوا</h2>
            <p class="admin-help mt-1">اعداد از Query دیتابیس استخراج می‌شوند و با انتشار/ویرایش رکوردها تغییر می‌کنند.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>نوع محتوا</th>
                        <th>تعداد</th>
                        <th>دارای مجوز Index + Follow</th>
                        <th>بدون Meta Title</th>
                        <th>بدون Meta Description</th>
                        <th>مدیریت</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sections as $section)
                        <tr>
                            <td class="font-semibold">{{ $section['label'] }}</td>
                            <td>{{ number_format($section['metrics']['total']) }}</td>
                            <td>{{ number_format($section['metrics']['eligible']) }}</td>
                            <td>{{ number_format($section['metrics']['missing_titles']) }}</td>
                            <td>{{ number_format($section['metrics']['missing_descriptions']) }}</td>
                            <td><a class="text-xs font-semibold text-[var(--admin-accent)]" href="{{ $section['url'] }}">باز کردن</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <article class="admin-card space-y-4 p-5">
            <h2 class="text-base font-bold">اتصال ابزارهای جست‌وجو</h2>
            <div class="flex items-center justify-between gap-3 text-sm">
                <span>Google verification token</span>
                <span class="admin-badge {{ $googleVerificationConfigured ? 'admin-badge-success' : 'admin-badge-warning' }}">{{ $googleVerificationConfigured ? 'ثبت شده' : 'ثبت نشده' }}</span>
            </div>
            <div class="flex items-center justify-between gap-3 text-sm">
                <span>Bing verification token</span>
                <span class="admin-badge {{ $bingVerificationConfigured ? 'admin-badge-success' : 'admin-badge-warning' }}">{{ $bingVerificationConfigured ? 'ثبت شده' : 'ثبت نشده' }}</span>
            </div>
            <p class="admin-help leading-7">ثبت کد تأیید فقط مالکیت سایت را برای meta verification آماده می‌کند. این پروژه هنوز به APIهای Google Search Console یا Bing Webmaster متصل نیست؛ بنابراین Clicks، Impressions، CTR و Average Position در این گزارش وجود ندارند.</p>
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-7 text-amber-800">
                داده میدانی Core Web Vitals و امتیاز PageSpeed/Lighthouse نیز هنوز به این داشبورد متصل نشده‌اند. برای نمایش آن‌ها باید منبع اندازه‌گیری و مجوز API/CrUX به‌صورت واقعی پیکربندی شود.
            </div>
            <a href="{{ route('admin.seo.edit') }}" class="text-xs font-semibold text-[var(--admin-accent)]">تنظیم کدهای تأیید ←</a>
        </article>
        <article class="admin-card space-y-3 p-5">
            <h2 class="text-base font-bold">اقدام‌های اولویت‌دار</h2>
            <p class="text-sm leading-7">۱. برای همه محصولات، مقاله‌ها و صفحات مهم Meta Title و Description یکتا و توصیفی ثبت کن.</p>
            <p class="text-sm leading-7">۲. برای هر تصویر محتوایی Alt دقیق و غیرتکراری وارد کن.</p>
            <p class="text-sm leading-7">۳. Canonical، لینک‌های داخلی، محتوای تکراری و وضعیت HTTP را در بازبینی جداگانه کنترل کن.</p>
            <p class="text-sm leading-7">۴. قفل سراسری SEO را فقط پس از بررسی انسانی و اتصال ابزارهای Search Console باز کن.</p>
        </article>
    </section>
</div>
@endsection
