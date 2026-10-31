@extends('admin.layouts.app')

@section('title', 'مدیریت وبلاگ')
@section('page_title', 'وبلاگ')

@section('content')
<div class="space-y-6">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / BLOG</p>
            <h1 class="admin-title">مدیریت وبلاگ</h1>
            <p class="admin-subtitle mt-2">مقاله‌ها، وضعیت انتشار و متادیتای هر مطلب را مدیریت کنید.</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" class="admin-btn admin-btn-primary">افزودن مقاله</a>
    </header>

    <div class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-right">
                <thead class="bg-[var(--admin-surface-soft)] text-xs text-[var(--admin-muted)]">
                    <tr><th class="p-4">عنوان</th><th class="p-4">دسته</th><th class="p-4">وضعیت</th><th class="p-4">انتشار</th><th class="p-4">SEO</th><th class="p-4">عملیات</th></tr>
                </thead>
                <tbody class="divide-y divide-[var(--admin-border)]">
                    @forelse($posts as $post)
                        <tr>
                            <td class="p-4"><p class="text-sm font-semibold">{{ $post->title }}</p><p class="mt-1 text-xs text-[var(--admin-muted)]" dir="ltr">/blog/{{ $post->slug }}</p></td>
                            <td class="p-4 text-xs">{{ $post->category ?: '—' }}</td>
                            <td class="p-4"><span class="admin-badge {{ $post->status === 'published' ? 'admin-badge-success' : 'admin-badge-warning' }}">{{ $post->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}</span></td>
                            <td class="p-4 text-xs">{{ $post->published_at?->format('Y/m/d H:i') ?: '—' }}</td>
                            <td class="p-4 text-xs" dir="ltr">{{ $post->seo_index ? 'index' : 'noindex' }} / {{ $post->seo_follow ? 'follow' : 'nofollow' }}</td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <a class="text-xs font-semibold text-[var(--admin-accent)]" href="{{ route('admin.blog.edit', $post) }}">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('این مقاله حذف شود؟')">@csrf @method('DELETE')<button class="text-xs text-red-500">حذف</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-10 text-center text-sm text-[var(--admin-muted)]">هنوز مقاله‌ای ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-[var(--admin-border)] p-4">{{ $posts->links() }}</div>
    </div>
</div>
@endsection
