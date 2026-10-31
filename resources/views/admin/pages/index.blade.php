@extends('admin.layouts.app')

@section('title', 'صفحات سایت')
@section('page_title', 'صفحات سایت')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / PAGES</p>
            <h1 class="admin-title">مدیریت صفحات</h1>
            <p class="admin-subtitle mt-2">صفحات درباره ما، قوانین و صفحات محتوایی را بدون تغییر کد مدیریت کنید.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="admin-btn admin-btn-primary">افزودن صفحه</a>
    </header>

    <form method="GET" class="admin-card flex flex-wrap gap-3 p-4">
        <input name="q" value="{{ request('q') }}" class="admin-input min-w-[220px] flex-1" placeholder="جستجو بر اساس عنوان یا نامک">
        <button class="admin-btn admin-btn-secondary">جستجو</button>
        @if(request('q'))<a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-ghost">پاک کردن</a>@endif
    </form>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead><tr><th>عنوان</th><th>نامک</th><th>وضعیت</th><th>وضعیت SEO</th><th>آخرین ویرایش</th><th>عملیات</th></tr></thead>
                <tbody>
                @forelse($pages as $page)
                    <tr>
                        <td class="font-semibold">{{ $page->title }}</td>
                        <td dir="ltr">/{{ $page->slug }}</td>
                        <td><span class="admin-badge {{ $page->status === 'published' ? 'admin-badge-success' : 'admin-badge-warning' }}">{{ $page->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}</span></td>
                        <td dir="ltr">{{ $page->seo_index ? 'index' : 'noindex' }} / {{ $page->seo_follow ? 'follow' : 'nofollow' }}</td>
                        <td>{{ $page->updated_at?->format('Y/m/d H:i') }}</td>
                        <td><div class="flex gap-3"><a class="text-xs font-semibold text-[var(--admin-accent)]" href="{{ route('admin.pages.edit', $page) }}">ویرایش</a>
                        @if($page->status === 'published')<a target="_blank" rel="noopener" class="text-xs" href="{{ route('pages.show', $page->slug) }}">مشاهده</a>@endif
                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('صفحه حذف شود؟')">@csrf @method('DELETE')<button class="text-xs text-red-500">حذف</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-10 text-center">صفحه‌ای ثبت نشده است.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[var(--admin-border)] p-4">{{ $pages->links() }}</div>
    </div>
</div>
@endsection