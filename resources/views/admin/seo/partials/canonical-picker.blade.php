<div x-data="seoRoutePicker('{{ route('admin.seo.public-routes') }}')" class="space-y-2">
    <label class="admin-label" for="canonical_url">Canonical URL</label>
    <input id="canonical_url" name="canonical_url" type="url" dir="ltr" class="admin-input" value="{{ old('canonical_url', $seoCanonical ?? '') }}" placeholder="https://sillashop.com/...">
    <button type="button" class="admin-btn admin-btn-secondary" @click="show()">انتخاب از صفحات واقعی سایت</button>
    <p class="admin-help">در صورت نیاز به Canonical متفاوت، از فهرست انتخاب کنید یا آدرس معتبر را وارد کنید؛ در غیر این صورت خالی بگذارید.</p>
    @error('canonical_url')<p class="admin-error">{{ $message }}</p>@enderror
    <div x-show="open" x-cloak class="fixed inset-0 z-[310] flex items-center justify-center bg-black/60 p-3 sm:p-6" @keydown.escape.window="open = false">
        <section class="flex max-h-[85vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-[var(--admin-white)] shadow-2xl" role="dialog" aria-modal="true" aria-label="انتخاب صفحه عمومی">
            <header class="flex items-center justify-between border-b border-[var(--admin-border)] p-4"><div><h3 class="font-bold">صفحات عمومی فروشگاه</h3><p class="admin-help mt-1">فقط صفحات قابل مشاهده برای بازدیدکننده نمایش داده می‌شوند.</p></div><button type="button" class="admin-btn admin-btn-secondary" @click="open = false">بستن</button></header>
            <div class="p-4"><input type="search" class="admin-input" x-model="search" @input.debounce.300ms="load()" placeholder="جست‌وجوی محصول، دسته، مقاله یا صفحه"></div>
            <p x-show="loading" class="py-4 text-center text-sm text-[var(--admin-muted)]">در حال بارگذاری…</p>
            <div class="min-h-0 flex-1 space-y-2 overflow-y-auto px-4 pb-4">
                <template x-for="item in routes" :key="item.url"><button type="button" @click="choose(item.url)" class="block w-full rounded-xl border border-[var(--admin-border)] p-3 text-right hover:border-[var(--admin-accent)]"><span class="block text-sm font-semibold" x-text="item.label"></span><span class="mt-1 block break-all text-xs text-[var(--admin-muted)]" dir="ltr" x-text="item.url"></span></button></template>
                <p x-show="!loading && routes.length === 0" class="py-6 text-center text-sm text-[var(--admin-muted)]">موردی پیدا نشد.</p>
            </div>
        </section>
    </div>
</div>