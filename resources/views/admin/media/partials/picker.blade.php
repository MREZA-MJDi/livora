@php
    $mediaPickerField = $mediaPickerField ?? 'image';
    $mediaPickerId = $mediaPickerId ?? null;
    $mediaPickerUrl = $mediaPickerUrl ?? null;
    $mediaPickerName = $mediaPickerName ?? '';
    $mediaPickerPreviewId = $mediaPickerPreviewId ?? 'media-image-preview';
    $mediaPickerInputName = $mediaPickerInputName ?? 'media_id';
    $mediaPickerRemoveName = $mediaPickerRemoveName ?? 'remove_image';
@endphp
<div x-data="mediaPicker(@js(route('admin.media.picker')), @js($mediaPickerId), @js($mediaPickerUrl), @js($mediaPickerName), @js($mediaPickerPreviewId), @js($mediaPickerField), @js($mediaPickerRemoveName))" class="space-y-2">
    <input type="hidden" name="{{ $mediaPickerInputName }}" :value="selectedMedia?.id || ''">
    <input type="hidden" name="{{ $mediaPickerRemoveName }}" value="0">
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" class="admin-btn admin-btn-secondary" @click="openPicker()">انتخاب از کتابخانه رسانه</button>
        <button type="button" x-show="selectedMedia" x-cloak class="admin-btn admin-btn-secondary" @click="cropSelected()">برش مستقل برای این محل استفاده</button>
        <span class="text-xs text-[var(--admin-muted)]" x-text="selectedMedia ? 'تصویر کتابخانه انتخاب شده' : 'تصویر جدید آپلود کنید یا از کتابخانه انتخاب کنید'"></span>
        <button type="button" x-show="selectedMedia" @click="clearSelection()" class="text-xs text-rose-600">حذف تصویر فعلی از این بخش</button>
    </div>
    <p x-show="errorMessage" x-text="errorMessage" class="text-xs text-[var(--admin-danger)]" role="alert"></p>
    <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-[320] flex items-center justify-center bg-black/60 p-3 sm:p-6" @keydown.escape.window="pickerOpen = false">
        <section class="flex max-h-[88vh] w-full max-w-5xl flex-col overflow-hidden rounded-2xl bg-[var(--admin-white)] shadow-2xl" role="dialog" aria-modal="true" aria-label="کتابخانه رسانه">
            <header class="flex items-center justify-between border-b border-[var(--admin-border)] p-4">
                <div><h3 class="font-bold">کتابخانه رسانه</h3><p class="admin-help mt-1">یک تصویر را انتخاب کنید. برای جایگزینی، از رایانه آپلود کنید؛ برای حذف تصویر فعلی، گزینه حذف را بزنید.</p></div>
                <button type="button" class="admin-btn admin-btn-secondary" @click="pickerOpen = false">بستن</button>
            </header>
            <div class="p-4"><input type="search" class="admin-input" x-model="search" @input.debounce.300ms="loadMedia(1)" placeholder="جست‌وجوی نام فایل یا تصویر"></div>
            <p x-show="errorMessage" x-text="errorMessage" class="px-4 text-sm text-[var(--admin-danger)]"></p>
            <div class="min-h-0 flex-1 overflow-y-auto px-4 pb-4">
                <p x-show="loading" class="py-6 text-center text-sm text-[var(--admin-muted)]">در حال بارگذاری…</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                    <template x-for="item in items" :key="item.id">
                        <button type="button" @click="selectMedia(item)" class="overflow-hidden rounded-xl border p-2 text-right transition hover:border-[var(--admin-accent)]" :class="selectedMedia?.id === item.id ? 'border-[var(--admin-accent)] bg-[var(--admin-accent-soft)]' : 'border-[var(--admin-border)]'">
                            <span class="flex aspect-square items-center justify-center overflow-hidden rounded-lg bg-[var(--admin-surface)]"><template x-if="item.url"><img :src="item.url" :alt="item.alt || item.name" class="h-full w-full object-contain"></template><template x-if="!item.url"><span class="px-2 text-center text-xs text-amber-700">فایل ناموجود</span></template></span>
                            <span class="mt-2 block truncate text-xs font-medium" x-text="item.name"></span>
                            <span class="block text-[10px] text-[var(--admin-muted)]" x-text="item.size"></span>
                        </button>
                    </template>
                </div>
                <p x-show="!loading && items.length === 0" class="py-8 text-center text-sm text-[var(--admin-muted)]">تصویری پیدا نشد.</p>
            </div>
            <footer class="flex items-center justify-between border-t border-[var(--admin-border)] p-4 text-xs">
                <span x-text="total + ' تصویر'"></span>
                <div class="flex gap-2"><button type="button" class="admin-btn admin-btn-secondary" :disabled="page <= 1 || loading" @click="loadMedia(page - 1)">قبلی</button><span class="self-center" x-text="page + ' / ' + lastPage"></span><button type="button" class="admin-btn admin-btn-secondary" :disabled="page >= lastPage || loading" @click="loadMedia(page + 1)">بعدی</button></div>
            </footer>
        </section>
    </div>
</div>
