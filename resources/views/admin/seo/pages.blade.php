@extends('admin.layouts.app')

@section('title', 'متادیتای صفحات ثابت')
@section('page_title', 'متادیتای صفحات ثابت')

@section('content')
<div class="space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">SEARCH / PAGE SEO</p>
        <h1 class="admin-title">مدیریت متادیتای صفحات ثابت</h1>
        <p class="admin-subtitle mt-2">برای هر صفحه عنوان، توضیحات، Canonical و تصویر اشتراک‌گذاری را جداگانه تنظیم کنید.</p>
    </header>

    <form method="POST" action="{{ route('admin.seo.pages.update') }}" class="space-y-5">
        @csrf
        @method('PUT')
        @foreach($pages as $page)
            <section class="admin-card space-y-4 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h2 class="text-base font-bold">{{ $page->label }}</h2><p class="mt-1 text-xs text-[var(--admin-muted)]" dir="ltr">{{ $page->path }}</p></div>
                </div>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div><label class="admin-label">Meta Title</label><input class="admin-input" name="pages[{{ $page->id }}][meta_title]" maxlength="255" value="{{ old("pages.{$page->id}.meta_title", $page->meta_title) }}"></div>
                    <div><label class="admin-label">Canonical URL</label><input class="admin-input" dir="ltr" type="url" name="pages[{{ $page->id }}][canonical_url]" value="{{ old("pages.{$page->id}.canonical_url", $page->canonical_url) }}"></div>
                    <div><label class="admin-label">Open Graph Image URL</label><input class="admin-input" dir="ltr" type="url" name="pages[{{ $page->id }}][og_image]" value="{{ old("pages.{$page->id}.og_image", $page->og_image) }}"></div>
                    <div class="lg:col-span-2"><label class="admin-label">Meta Description</label><textarea class="admin-textarea" rows="2" maxlength="1000" name="pages[{{ $page->id }}][meta_description]">{{ old("pages.{$page->id}.meta_description", $page->meta_description) }}</textarea></div>
                </div>
            </section>
        @endforeach
        <div class="flex justify-end"><button class="admin-btn admin-btn-primary" type="submit">ذخیره تنظیمات صفحات</button></div>
    </form>
</div>
@endsection