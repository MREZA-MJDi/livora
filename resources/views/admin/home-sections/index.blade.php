@extends('admin.layouts.app')

@section('title', 'سکشن‌های صفحه اصلی')
@section('page_title', 'مدیریت صفحه اصلی')

@section('content')
<div class="mx-auto max-w-6xl space-y-6" x-data="homeSectionOrder()">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-xs font-semibold text-[var(--admin-accent)]">STOREFRONT / HOMEPAGE</p><h1 class="admin-title mt-2">سکشن‌های صفحه اصلی</h1><p class="admin-subtitle mt-2">اسلایدر و بخش‌های صفحه اصلی را ویرایش کنید. تغییرات پس از ذخیره در سایت نمایش داده می‌شوند.</p></div>
        <a href="{{ route('admin.home-sections.create') }}" class="admin-btn admin-btn-primary">افزودن سکشن</a>
    </header>
    @if(session('success'))<div class="admin-card p-4 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('admin.home-sections.reorder') }}" class="admin-card flex flex-wrap items-center justify-between gap-3 p-5">
        @csrf
        <template x-for="id in order" :key="id"><input type="hidden" name="order[]" :value="id"></template>
        <div><h2 class="font-bold">ترتیب نمایش</h2><p class="admin-help">ترتیب نمایش بخش‌ها را تنظیم کنید و ذخیره را بزنید.</p></div>
        <button class="admin-btn admin-btn-primary" type="submit">ذخیره ترتیب</button>
    </form>
    <div class="space-y-2">
        @forelse($sections as $section)
            <article draggable="true" @dragstart="start({{ $section->id }})" @dragover.prevent @drop.prevent="drop({{ $section->id }})" @dragend="dragging = null" class="admin-card flex flex-wrap items-center gap-3 p-4" :class="dragging === {{ $section->id }} ? 'opacity-50' : ''">
                <span class="cursor-grab select-none text-xl text-[var(--admin-muted)]" aria-label="جابجایی">⠿</span>
                <span class="min-w-0 flex-1"><span class="block text-sm font-bold">{{ $section->title ?: $section->key }}</span><span class="mt-1 block text-xs text-[var(--admin-muted)]">{{ match($section->type) { 'hero' => 'اسلایدر صفحه اصلی', 'products' => 'محصولات', 'categories' => 'دسته‌بندی‌ها', 'editorial' => 'محتوای تصویری', 'trust' => 'مزیت‌های فروشگاه', 'cta' => 'دعوت به اقدام', default => 'بخش دیگر' } }} · {{ $section->enabled ? 'نمایش داده می‌شود' : 'فعلاً پنهان است' }}</span></span>
                <a class="admin-btn admin-btn-secondary" href="{{ route('admin.home-sections.edit', $section) }}">ویرایش</a>
                <form method="POST" action="{{ route('admin.home-sections.destroy', $section) }}" onsubmit="return confirm('این سکشن حذف شود؟')">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger" type="submit">حذف</button></form>
            </article>
        @empty
            <p class="rounded-xl bg-[var(--admin-surface)] p-6 text-sm text-[var(--admin-muted)]">هنوز سکشنی تعریف نشده است.</p>
        @endforelse
    </div>
</div>
<script>
function homeSectionOrder() {
    return {
        order: @js($sections->pluck('id')->values()),
        dragging: null,
        start(id) { this.dragging = id; },
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
