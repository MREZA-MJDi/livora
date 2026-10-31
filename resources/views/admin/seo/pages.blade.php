@extends('admin.layouts.app')

@section('title', 'SEO صفحات ثابت')
@section('page_title', 'SEO صفحات ثابت')

@section('content')
<div class="space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">SEARCH / PAGE SEO</p>
        <h1 class="admin-title">کنترل ایندکس صفحات ثابت</h1>
        <p class="admin-subtitle mt-2">برای هر صفحه عنوان، توضیحات، Canonical و اجازه ایندکس را جداگانه تنظیم کنید.</p>
    </header>

    <div class="admin-card p-5">
        <strong class="text-sm">قفل ایمنی سراسری</strong>
        <p class="mt-2 text-xs leading-7 text-[var(--admin-muted)]">حتی با روشن‌کردن ایندکس یک صفحه، تا وقتی اجازه ایندکس سراسری در تنظیمات اصلی SEO فعال نشده باشد، تمام صفحات عمومی noindex, nofollow باقی می‌مانند.</p>
        <a class="mt-3 inline-flex text-xs font-semibold text-[var(--admin-accent)]" href="{{ route('admin.seo.edit') }}">رفتن به تنظیمات اصلی SEO ←</a>
    </div>

    <form method="POST" action="{{ route('admin.seo.pages.update') }}" class="space-y-5">
        @csrf
        @method('PUT')
        @foreach($pages as $page)
            <section class="admin-card space-y-4 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h2 class="text-base font-bold">{{ $page->label }}</h2><p class="mt-1 text-xs text-[var(--admin-muted)]" dir="ltr">{{ $page->path }}</p></div>
                    <span class="admin-badge {{ $page->seo_index ? 'admin-badge-warning' : 'admin-badge-default' }}">{{ $page->seo_index ? 'در انتظار قفل سراسری' : 'Noindex پیش‌فرض' }}</span>
                </div>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div><label class="admin-label">Meta Title</label><input class="admin-input" name="pages[{{ $page->id }}][meta_title]" maxlength="255" value="{{ old("pages.{$page->id}.meta_title", $page->meta_title) }}"></div>
                    <div><label class="admin-label">Canonical URL</label><input class="admin-input" dir="ltr" type="url" name="pages[{{ $page->id }}][canonical_url]" value="{{ old("pages.{$page->id}.canonical_url", $page->canonical_url) }}"></div>
                    <div class="lg:col-span-2"><label class="admin-label">Meta Description</label><textarea class="admin-textarea" rows="2" maxlength="1000" name="pages[{{ $page->id }}][meta_description]">{{ old("pages.{$page->id}.meta_description", $page->meta_description) }}</textarea></div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] px-4 py-3 text-xs"><input class="admin-checkbox" type="checkbox" name="pages[{{ $page->id }}][seo_index]" value="1" @checked(old("pages.{$page->id}.seo_index", $page->seo_index))> اجازه Index این صفحه</label>
                    <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] px-4 py-3 text-xs"><input class="admin-checkbox" type="checkbox" name="pages[{{ $page->id }}][seo_follow]" value="1" @checked(old("pages.{$page->id}.seo_follow", $page->seo_follow))> اجازه Follow لینک‌ها</label>
                </div>
            </section>
        @endforeach
        <div class="flex justify-end"><button class="admin-btn admin-btn-primary" type="submit">ذخیره تنظیمات صفحات</button></div>
    </form>
</div>
@endsection