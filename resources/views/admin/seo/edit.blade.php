@extends('admin.layouts.app')

@section('title', 'مدیریت SEO')
@section('page_title', 'مدیریت SEO')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">GROWTH / SEO</p>
        <h1 class="admin-title">مرکز مدیریت SEO | SillaGallery</h1>
        <p class="admin-subtitle mt-2">هویت برند، اطلاعات متادیتا، شبکه‌های اجتماعی و داده‌های ساختاریافته سیلا گالری.</p>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.home-sections.index') }}" class="admin-card group p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-xs font-semibold text-[var(--admin-accent)]">STOREFRONT</span>
            <h2 class="mt-3 font-bold">مدیریت صفحه اصلی</h2>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">هیرو، بنر، سکشن‌های محصول، دسته‌ها، محتوای تصویری و مجله را مدیریت کنید.</p>
            <span class="mt-4 inline-flex text-xs font-semibold text-[var(--admin-accent)]">ویرایش سکشن‌ها ←</span>
            <span class="mt-2 block text-xs font-semibold text-[var(--admin-accent)]">تنظیم مستقل سکشن مجله ←</span>
            <a href="{{ route('admin.home-sections.create', ['preset' => 'homepage-journal']) }}" class="absolute inset-0" aria-label="تنظیم مستقل سکشن مجله"></a>
        </a>
        <a href="{{ route('admin.blog.index') }}" class="admin-card group p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-xs font-semibold text-[var(--admin-accent)]">EDITORIAL</span>
            <h2 class="mt-3 font-bold">مقالات و مجله</h2>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">محتوا، تصویر شاخص، متن جایگزین، زمان انتشار و متادیتای هر مقاله.</p>
            <span class="mt-4 inline-flex text-xs font-semibold text-[var(--admin-accent)]">مدیریت مقالات ←</span>
        </a>
        <a href="{{ route('admin.shop-banner.edit') }}" class="admin-card group p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-xs font-semibold text-[var(--admin-accent)]">CAMPAIGNS</span>
            <h2 class="mt-3 font-bold">بنر فروشگاه</h2>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تصویر کمپین، پیام و لینک بنرهای فروشگاه را از پنل تنظیم کنید.</p>
            <span class="mt-4 inline-flex text-xs font-semibold text-[var(--admin-accent)]">ویرایش بنر ←</span>
        </a>
        <a href="{{ route('admin.media.index') }}" class="admin-card group p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
            <span class="text-xs font-semibold text-[var(--admin-accent)]">ASSET LIBRARY</span>
            <h2 class="mt-3 font-bold">کتابخانه رسانه</h2>
            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">مدیریت تصاویر، انتخاب فایل موجود و بررسی محل استفاده رسانه‌ها.</p>
            <span class="mt-4 inline-flex text-xs font-semibold text-[var(--admin-accent)]">باز کردن رسانه‌ها ←</span>
        </a>
    </section>

    <form method="POST" action="{{ route('admin.seo.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">هویت سایت و متادیتای پیش‌فرض</h2>
            <div><label class="admin-label" for="site_name">نام برند</label><input id="site_name" name="site_name" class="admin-input" maxlength="255" required readonly value="{{ $brandSettings?->brand_name ?: config('app.name') }}"><p class="admin-help">نام فارسی و لاتین برند در عنوان‌ها و داده‌های ساختاریافته استفاده می‌شود.</p></div>
            <div><label class="admin-label" for="default_title">عنوان پیش‌فرض</label><input id="default_title" name="default_title" class="admin-input" maxlength="255" value="{{ old('default_title', $settings->default_title) }}"></div>
            <div><label class="admin-label" for="default_description">توضیحات پیش‌فرض</label><textarea id="default_description" name="default_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('default_description', $settings->default_description) }}</textarea></div>
            <div><label class="admin-label" for="default_og_image">تصویر پیش‌فرض اشتراک‌گذاری (Open Graph)</label><input id="default_og_image" name="default_og_image" type="file" accept="image/jpeg,image/png,image/webp" data-crop-ratio="1.9047619" class="admin-input p-2"><p class="admin-help">تصویر را از Media Library انتخاب کنید یا از رایانه آپلود کنید. نسبت پیشنهادی ۱۲۰۰×۶۳۰ است.</p><div class="mt-3"><img id="default-og-image-preview" src="{{ $settings->default_og_image_url ?? '' }}" alt="تصویر اشتراک‌گذاری پیش‌فرض" class="{{ $settings->default_og_image_url ? '' : 'hidden' }} aspect-[1200/630] max-h-48 rounded-xl object-cover"></div>@include('admin.media.partials.picker', ['mediaPickerField' => 'default_og_image', 'mediaPickerId' => old('default_og_media_id', $settings->default_og_media_id), 'mediaPickerUrl' => $settings->defaultOgMedia?->url, 'mediaPickerName' => $settings->defaultOgMedia?->original_name ?? 'Open Graph image', 'mediaPickerPreviewId' => 'default-og-image-preview', 'mediaPickerInputName' => 'default_og_media_id', 'mediaPickerRemoveName' => 'remove_default_og_image'])<label class="mt-3 flex items-center gap-2 text-xs text-rose-700"><input type="checkbox" name="remove_default_og_image" value="1" class="admin-checkbox"> حذف تصویر Open Graph فعلی از تنظیمات سایت</label></div>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">اطلاعات سازمان و شبکه‌های اجتماعی</h2>
            <div><label class="admin-label" for="organization_name">نام سازمان</label><input id="organization_name" class="admin-input" readonly value="{{ $brandSettings?->brand_name ?: config('app.name') }}"><p class="admin-help">برای تغییر نام برند از تنظیمات برند استفاده کنید.</p></div>
            <div><label class="admin-label" for="organization_description">معرفی کوتاه سازمان</label><textarea id="organization_description" rows="3" class="admin-textarea" readonly>{{ $brandSettings?->brand_description }}</textarea><p class="admin-help">توضیح برند از تنظیمات برند خوانده می‌شود.</p></div>
            <div><label class="admin-label">لوگوی برند</label><p class="admin-help">لوگو از تنظیمات برند خوانده می‌شود و در این بخش جداگانه تغییر نمی‌کند.</p>@if($brandSettings?->logo_path)<img src="{{ str_starts_with($brandSettings->logo_path, 'http') ? $brandSettings->logo_path : \Illuminate\Support\Facades\Storage::disk('public')->url($brandSettings->logo_path) }}" alt="{{ $brandSettings->brand_name }}" class="mt-3 h-20 w-40 rounded-xl object-contain">@endif</div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="admin-label">ایمیل</label><input type="email" class="admin-input" readonly value="{{ $brandSettings?->email }}"></div><div><label class="admin-label">تلفن</label><input class="admin-input" readonly value="{{ $brandSettings?->phone }}"></div></div>
            <div><label class="admin-label">شناسه X / Twitter</label><input name="twitter_handle" class="admin-input" dir="ltr" value="{{ old('twitter_handle', $settings->twitter_handle) }}" placeholder="@SillaGallery"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['instagram'=>'اینستاگرام','telegram'=>'تلگرام','whatsapp'=>'واتساپ','eitaa'=>'ایتا','bale'=>'بله'] as $key=>$label)
                    <div><label class="admin-label">{{ $label }}</label><input type="url" dir="ltr" class="admin-input" readonly value="{{ $brandSettings?->{$key.'_url'} }}"></div>
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
            <a href="{{ route('admin.seo.audit') }}" class="admin-btn admin-btn-secondary">ممیزی فنی و پوشش SEO</a>
            <a href="{{ route('admin.seo.pages') }}" class="admin-btn admin-btn-secondary">تنظیم SEO صفحات ثابت</a>
            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">مشاهده Sitemap</a>
            <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنظیمات SEO</button>
        </div>
    </form>
</div>
@endsection