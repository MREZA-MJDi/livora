@extends('admin.layouts.app')

@section('title', 'بنر صفحه فروشگاه')
@section('page_title', 'مدیریت بنر فروشگاه')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">STOREFRONT / SHOP HERO</p>
        <h1 class="admin-title">بنر صفحه فروشگاه</h1>
        <p class="admin-subtitle mt-2">تصویر، تیتر و توضیحات بخش بالای فروشگاه را از اینجا مدیریت کنید.</p>
    </header>

    @if(session('success'))
        <div class="admin-card border border-emerald-200 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.shop-banner.update') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="admin-card space-y-5 p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">تصویر اصلی بنر</h2>
                    <p class="admin-help mt-1">نسبت برش ۱۲:۵ است. تصویر در دسکتاپ و موبایل با همین کادر اصلی نمایش داده می‌شود.</p>
                </div>
                <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] px-4 py-3 text-xs">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="admin-checkbox" @checked(old('is_active', $settings->is_active))>
                    نمایش بنر در فروشگاه
                </label>
            </div>

            @if($settings->banner_image_url)
                <div class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]">
                    <img src="{{ $settings->banner_image_url }}" alt="{{ $settings->banner_image_alt ?: 'پیش‌نمایش بنر فعلی فروشگاه' }}" class="aspect-[12/5] w-full object-cover">
                </div>
            @endif

            <div>
                <label for="banner_image" class="admin-label">آپلود تصویر و تنظیم کادر</label>
                <input
                    id="banner_image"
                    name="banner_image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    data-crop-ratio="2.4"
                    class="admin-input"
                >
                @error('banner_image')<p class="admin-error">{{ $message }}</p>@enderror
                <p class="admin-help mt-2">پس از انتخاب تصویر، ابزار برش باز می‌شود. قاب را با کشیدن تصویر و کنترل بزرگ‌نمایی تنظیم کنید.</p>
            </div>

            <div>
                <label for="banner_image_alt" class="admin-label">توضیح تصویر (Alt)</label>
                <input id="banner_image_alt" name="banner_image_alt" maxlength="255" class="admin-input" value="{{ old('banner_image_alt', $settings->banner_image_alt ?? '') }}" placeholder="شرح واقعی تصویر بنر">
                @error('banner_image_alt')<p class="admin-error">{{ $message }}</p>@enderror
            </div>

            @if($settings->banner_image)
                <label class="flex items-center gap-2 text-sm text-rose-700">
                    <input type="checkbox" name="remove_banner" value="1" class="admin-checkbox">
                    حذف تصویر فعلی و نمایش بنر متنی
                </label>
            @endif
        </section>

        <section class="admin-card space-y-5 p-6">
            <div>
                <h2 class="text-base font-bold">متن‌های بنر</h2>
                <p class="admin-help mt-1">متن‌ها روی لایه تیره تصویر قرار می‌گیرند و برای خوانایی در موبایل نیز کنترل شده‌اند.</p>
            </div>
            <div>
                <label for="eyebrow" class="admin-label">برچسب کوچک بالای تیتر</label>
                <input id="eyebrow" name="eyebrow" maxlength="120" class="admin-input" value="{{ old('eyebrow', $settings->eyebrow) }}">
                @error('eyebrow')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="title" class="admin-label">تیتر اصلی</label>
                <input id="title" name="title" maxlength="255" required class="admin-input" value="{{ old('title', $settings->title) }}">
                @error('title')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="admin-label">توضیح بنر</label>
                <textarea id="description" name="description" rows="4" maxlength="1200" class="admin-textarea">{{ old('description', $settings->description) }}</textarea>
                @error('description')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('shop.index') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">پیش‌نمایش فروشگاه</a>
            <button type="submit" class="admin-btn admin-btn-primary">ذخیره بنر فروشگاه</button>
        </div>
    </form>
</div>
@endsection
