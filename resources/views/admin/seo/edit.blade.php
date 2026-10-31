@extends('admin.layouts.app')

@section('title', 'مدیریت SEO')
@section('page_title', 'مدیریت SEO')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">GROWTH / SEO</p>
        <h1 class="admin-title">مدیریت SEO فروشگاه</h1>
        <p class="admin-subtitle mt-2">کنترل ایندکس، دنبال‌کردن لینک‌ها، عنوان و توضیحات پیش‌فرض سایت.</p>
    </header>

    <div class="admin-card border border-amber-300/60 p-5">
        <p class="text-sm font-semibold text-[var(--admin-text)]">حالت محافظتی فعال</p>
        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تا زمانی که کارشناس SEO آماده‌بودن سایت را تأیید نکند، تنظیم پیش‌فرض Noindex و Nofollow باقی می‌ماند. robots.txt عمداً Crawl را مسدود نمی‌کند تا موتور جست‌وجو بتواند این دستور را بخواند.</p>
        <p class="mt-3 font-mono text-xs" dir="ltr">Current: {{ $settings->robots() }}</p>
    </div>

    <form method="POST" action="{{ route('admin.seo.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold text-[var(--admin-text)]">کنترل موتورهای جست‌وجو</h2>
            <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4">
                <input type="checkbox" name="indexing_enabled" value="1" class="admin-checkbox mt-1" @checked(old('indexing_enabled', $settings->indexing_enabled))>
                <span><strong class="block text-sm">اجازه ایندکس صفحات عمومی</strong><span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">در صورت غیرفعال بودن، سایت noindex می‌ماند و URLهای محصولات و دسته‌بندی‌ها وارد Sitemap نمی‌شوند.</span></span>
            </label>
            <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4">
                <input type="checkbox" name="following_enabled" value="1" class="admin-checkbox mt-1" @checked(old('following_enabled', $settings->following_enabled))>
                <span><strong class="block text-sm">اجازه دنبال‌کردن لینک‌ها</strong><span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">در صورت غیرفعال بودن، دستور nofollow روی صفحات عمومی اعمال می‌شود.</span></span>
            </label>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold text-[var(--admin-text)]">اطلاعات پیش‌فرض برند</h2>
            <div>
                <label class="admin-label" for="site_name">نام سایت</label>
                <input id="site_name" name="site_name" class="admin-input" maxlength="255" required value="{{ old('site_name', $settings->site_name) }}">
                @error('site_name')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-label" for="default_title">عنوان پیش‌فرض</label>
                <input id="default_title" name="default_title" class="admin-input" maxlength="255" value="{{ old('default_title', $settings->default_title) }}">
                @error('default_title')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="admin-label" for="default_description">توضیحات پیش‌فرض</label>
                <textarea id="default_description" name="default_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('default_description', $settings->default_description) }}</textarea>
                @error('default_description')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('sitemap') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">مشاهده Sitemap</a>
            <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنظیمات SEO</button>
        </div>
    </form>
</div>
@endsection
