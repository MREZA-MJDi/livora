@extends('admin.layouts.app')

@section('title', 'مدیریت SEO')
@section('page_title', 'مدیریت SEO')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">GROWTH / SEO</p>
        <h1 class="admin-title">مرکز مدیریت SEO | SillaGallery</h1>
        <p class="admin-subtitle mt-2">هویت برند، تنظیمات موتورهای جست‌وجو، شبکه‌های اجتماعی و قفل انتشار سراسری.</p>
    </header>

    <div class="admin-card border border-amber-300/60 p-5">
        <p class="text-sm font-semibold">حالت محافظتی فعال</p>
        <p class="mt-2 text-xs leading-7 text-[var(--admin-muted)]">تا زمان تأیید همکار SEO، هر دو گزینه زیر خاموش بمانند. خاموش بودن هر گزینه به‌ترتیب تمام سایت را noindex یا nofollow نگه می‌دارد؛ تنظیمات اختصاصی هر صفحه به‌تنهایی این قفل را باز نمی‌کند.</p>
        <p class="mt-3 font-mono text-xs" dir="ltr">Current: {{ $settings->robots() }}</p>
    </div>

    <form method="POST" action="{{ route('admin.seo.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">قفل سراسری موتورهای جست‌وجو</h2>
            <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="indexing_enabled" value="1" class="admin-checkbox mt-1" @checked(old('indexing_enabled', $settings->indexing_enabled))><span><strong class="block text-sm">اجازه ایندکس صفحات عمومی</strong><span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">فقط پس از تأیید نهایی محتوا روشن شود. در حالت خاموش تمام URLها noindex خواهند بود.</span></span></label>
            <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="following_enabled" value="1" class="admin-checkbox mt-1" @checked(old('following_enabled', $settings->following_enabled))><span><strong class="block text-sm">اجازه دنبال‌کردن لینک‌ها</strong><span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">در حالت خاموش، دستور nofollow به تمام صفحات عمومی اعمال می‌شود.</span></span></label>
            <p class="admin-help">پیش‌فرض Production باید هر دو خاموش باشد. خاموش‌شدن یک گزینه فوراً در HTML و HTTP Header اعمال می‌شود.</p>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">هویت سایت و متادیتای پیش‌فرض</h2>
            <div><label class="admin-label" for="site_name">نام برند</label><input id="site_name" name="site_name" class="admin-input" maxlength="255" required readonly value="SillaGallery"><p class="admin-help">نام برند عمومی قفل شده است؛ SillaGallery فقط نام فنی مخزن است.</p></div>
            <div><label class="admin-label" for="default_title">عنوان پیش‌فرض</label><input id="default_title" name="default_title" class="admin-input" maxlength="255" value="{{ old('default_title', $settings->default_title) }}"></div>
            <div><label class="admin-label" for="default_description">توضیحات پیش‌فرض</label><textarea id="default_description" name="default_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('default_description', $settings->default_description) }}</textarea></div>
            <div><label class="admin-label" for="default_og_image">تصویر پیش‌فرض اشتراک‌گذاری</label><input id="default_og_image" name="default_og_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input p-2"><p class="admin-help">تصویر مربعی یا افقی با کیفیت مناسب؛ JPG، PNG یا WEBP تا ۱۰ مگابایت.</p>@if($settings->default_og_image_url)<img src="{{ $settings->default_og_image_url }}" alt="تصویر اشتراک‌گذاری پیش‌فرض" class="mt-3 aspect-[1200/630] max-h-48 rounded-xl object-cover">@endif</div>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">اطلاعات سازمان و شبکه‌های اجتماعی</h2>
            <div><label class="admin-label" for="organization_name">نام سازمان</label><input id="organization_name" name="organization_name" class="admin-input" value="{{ old('organization_name', $settings->organization_name ?: 'SillaGallery') }}"></div>
            <div><label class="admin-label" for="organization_description">معرفی کوتاه سازمان</label><textarea id="organization_description" name="organization_description" rows="3" class="admin-textarea">{{ old('organization_description', $settings->organization_description) }}</textarea></div>
            <div><label class="admin-label" for="organization_logo">لوگوی سازمان</label><input id="organization_logo" name="organization_logo" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input p-2">@if($settings->organization_logo_url)<img src="{{ $settings->organization_logo_url }}" alt="لوگوی SillaGallery" class="mt-3 h-20 w-20 rounded-xl object-contain">@endif</div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="admin-label">ایمیل</label><input type="email" name="contact_email" class="admin-input" value="{{ old('contact_email', $settings->contact_email) }}"></div><div><label class="admin-label">تلفن</label><input name="contact_phone" class="admin-input" value="{{ old('contact_phone', $settings->contact_phone) }}"></div></div>
            <div><label class="admin-label">شناسه X / Twitter</label><input name="twitter_handle" class="admin-input" dir="ltr" value="{{ old('twitter_handle', $settings->twitter_handle) }}" placeholder="@SillaGallery"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['instagram'=>'اینستاگرام','telegram'=>'تلگرام','linkedin'=>'لینکدین','youtube'=>'یوتیوب'] as $key=>$label)
                    <div><label class="admin-label">{{ $label }}</label><input type="url" name="social_links[{{ $key }}]" dir="ltr" class="admin-input" value="{{ old("social_links.{$key}", $settings->social_links[$key] ?? '') }}"></div>
                @endforeach
            </div>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">اعتبارسنجی موتورهای جست‌وجو</h2>
            <div><label class="admin-label">Google Search Console verification</label><input name="google_site_verification" dir="ltr" class="admin-input" value="{{ old('google_site_verification', $settings->google_site_verification) }}"></div>
            <div><label class="admin-label">Bing Webmaster verification</label><input name="bing_site_verification" dir="ltr" class="admin-input" value="{{ old('bing_site_verification', $settings->bing_site_verification) }}"></div>
            <p class="admin-help">کد تأیید را از پنل رسمی همان سرویس کپی کنید؛ این فیلدها داده ساختگی تولید نمی‌کنند.</p>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('admin.seo.pages') }}" class="admin-btn admin-btn-secondary">تنظیم SEO صفحات ثابت</a>
            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">مشاهده Sitemap</a>
            <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنظیمات SEO</button>
        </div>
    </form>
</div>
@endsection