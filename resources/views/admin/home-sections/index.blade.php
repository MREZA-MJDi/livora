@extends('admin.layouts.app')

@section('title', 'سکشن‌های صفحه اصلی')
@section('page_title', 'مدیریت صفحه اصلی')

@section('content')
<div class="mx-auto max-w-6xl space-y-6" x-data="homeSectionOrder()">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-semibold text-[var(--admin-accent)]">STOREFRONT / HOMEPAGE</p><h1 class="admin-title mt-2">سکشن‌های صفحه اصلی</h1><p class="admin-subtitle mt-2">فعال‌سازی، ویرایش و مرتب‌سازی سکشن‌ها بدون تغییر کد.</p></div>
        <a href="{{ route('admin.home-sections.create') }}" class="admin-btn admin-btn-primary">افزودن سکشن</a>
    </header>
    @if(session('success'))<div class="admin-card p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('admin.home-sections.reorder') }}" class="admin-card space-y-4 p-5">
        @csrf
        <input type="hidden" name="order" :value="JSON.stringify(order)">
        <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-bold">ترتیب نمایش</h2><p class="admin-help">با کشیدن کارت‌ها ترتیب را تغییر دهید و ذخیره کنید.</p></div><button class="admin-btn admin-btn-primary" type="submit">ذخیره ترتیب</button></div>
        <div class="space-y-2">
            @forelse($sections as $section)
                <article draggable="true" @dragstart="start({{ $section->id }})" @dragover.prevent="over({{ $section->id }})" @drop.prevent="drop({{ $section->id }})" @dragend="dragging = null" data-section-id="{{ $section->id }}" class="flex flex-wrap items-center gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-4" :class="dragging === {{ $section->id }} ? 'opacity-50' : ''">
                    <span class="cursor-grab select-none text-xl text-[var(--admin-muted)]" aria-label="جابجایی">⠿</span>
                    <span class="min-w-0 flex-1"><span class="block text-sm font-bold">{{ $section->title ?: $section->key }}</span><span class="mt-1 block text-xs text-[var(--admin-muted)]">{{ $section->key }} · {{ $section->type }} · {{ $section->enabled ? 'فعال' : 'غیرفعال' }}</span></span>
                    <a class="admin-btn admin-btn-secondary" href="{{ route('admin.home-sections.edit', $section) }}">ویرایش</a>
                    <form method="POST" action="{{ route('admin.home-sections.destroy', $section) }}" onsubmit="return confirm('این سکشن حذف شود؟')">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger" type="submit">حذف</button></form>
                </article>
            @empty
                <p class="rounded-xl bg-[var(--admin-surface)] p-6 text-sm text-[var(--admin-muted)]">هنوز سکشنی تعریف نشده است.</p>
            @endforelse
        </div>
    </form>
</div>
<script>
function homeSectionOrder() {
    return {
        order: @js($sections->pluck('id')->values()),
        dragging: null,
        start(id) { this.dragging = id; },
        over(id) { if (!this.dragging || this.dragging === id) return; },
        drop(id) {
            if (!this.dragging || this.dragging === id) return;
            const from = this.order.indexOf(this.dragging);
            const to = this.order.indexOf(id);
            if (from < 0 || to < 0) return;
            this.order.splice(from, 1);
            this.order.splice(to, 0, this.dragging);
            this.dragging = null;
        }
    };
}
</script>
@endsection
