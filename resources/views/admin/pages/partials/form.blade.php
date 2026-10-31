@php($editing = isset($page))
@csrf

<div class="grid gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">محتوا</h2>
            <div><label class="admin-label" for="title">عنوان صفحه</label><input id="title" name="title" class="admin-input" required maxlength="255" value="{{ old('title', $page->title ?? '') }}">@error('title')<p class="admin-error">{{ $message }}</p>@enderror</div>
            <div><label class="admin-label" for="slug">نامک URL</label><input id="slug" name="slug" class="admin-input" dir="ltr" placeholder="about-us" value="{{ old('slug', $page->slug ?? '') }}">@error('slug')<p class="admin-error">{{ $message }}</p>@enderror</div>
            <div><label class="admin-label" for="excerpt">خلاصه</label><textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="admin-textarea">{{ old('excerpt', $page->excerpt ?? '') }}</textarea>@error('excerpt')<p class="admin-error">{{ $message }}</p>@enderror</div>

            <div>
                <label class="admin-label">متن صفحه</label>
                <div x-data="richTextEditor(@js(route('admin.editor-images.store')))" class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-white">
                    <div class="flex flex-wrap gap-1 border-b border-[var(--admin-border)] bg-[var(--admin-surface)] p-2">
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('bold')" title="Bold"><strong>B</strong></button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('italic')" title="Italic"><em>I</em></button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('underline')" title="Underline"><u>U</u></button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('formatBlock','<h2>')">تیتر ۲</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('formatBlock','<h3>')">تیتر ۳</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('insertUnorderedList')">فهرست</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('insertOrderedList')">شماره‌ای</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('formatBlock','<blockquote>')">نقل‌قول</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="addLink()">لینک</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="$refs.imageInput.click()">تصویر</button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('removeFormat')">پاک‌کردن قالب</button>
                        <input x-ref="imageInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadImage($event)">
                    </div>
                    <div x-ref="editor" contenteditable="true" dir="rtl" role="textbox" aria-multiline="true" class="cms-editor min-h-[380px] p-5 text-sm leading-9 outline-none" @input="sync()" @keydown="onKeydown($event)" @paste="pastePlain($event)">{!! $bodyHtml !!}</div>
                    <textarea name="body" x-ref="output" class="hidden" required></textarea>
                    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-3 text-xs text-[var(--admin-muted)]">
                        <span x-text="message || 'متن غنی؛ محتوای ناامن هنگام ذخیره پاک‌سازی می‌شود.'"></span>
                        <span x-text="wordCount + ' واژه'"></span>
                    </div>
                </div>
                @error('body')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">تنظیمات SEO صفحه</h2>
            <div><label class="admin-label" for="meta_title">Meta Title</label><input id="meta_title" name="meta_title" maxlength="255" class="admin-input" value="{{ old('meta_title', $page->meta_title ?? '') }}"><p class="admin-help">عنوان نمایشی در نتایج جستجو؛ در صورت خالی بودن از عنوان صفحه استفاده می‌شود.</p></div>
            <div><label class="admin-label" for="meta_description">Meta Description</label><textarea id="meta_description" name="meta_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('meta_description', $page->meta_description ?? '') }}</textarea></div>
            <div><label class="admin-label" for="canonical_url">Canonical URL</label><input id="canonical_url" name="canonical_url" type="url" dir="ltr" class="admin-input" value="{{ old('canonical_url', $page->canonical_url ?? '') }}" placeholder="https://sillashop.com/about"></div>
            <div><label class="admin-label" for="og_image">Open Graph Image URL (اختیاری)</label><input id="og_image" name="og_image" type="url" dir="ltr" class="admin-input" value="{{ old('og_image', $page->og_image ?? '') }}"></div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_index" value="1" class="admin-checkbox" @checked(old('seo_index', $page->seo_index ?? false))><span class="text-sm">اجازه ایندکس</span></label>
                <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_follow" value="1" class="admin-checkbox" @checked(old('seo_follow', $page->seo_follow ?? false))><span class="text-sm">اجازه Follow</span></label>
            </div>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="admin-card space-y-4 p-6">
            <h2 class="text-base font-bold">انتشار</h2>
            <div><label class="admin-label" for="status">وضعیت</label><select id="status" name="status" class="admin-select"><option value="draft" @selected(old('status', $page->status ?? 'draft') === 'draft')>پیش‌نویس</option><option value="published" @selected(old('status', $page->status ?? 'draft') === 'published')>منتشرشده</option></select></div>
            <div><label class="admin-label" for="published_at">زمان انتشار</label><input id="published_at" name="published_at" type="datetime-local" class="admin-input" value="{{ old('published_at', isset($page) && $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '') }}"></div>
            <button type="submit" class="admin-btn admin-btn-primary w-full">{{ $editing ? 'ذخیره تغییرات' : 'ایجاد صفحه' }}</button>
            <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary w-full">بازگشت</a>
        </section>
        <section class="admin-card space-y-4 p-6">
            <h2 class="text-base font-bold">تصویر شاخص</h2>
            @if($editing && $page->featured_image_url)<img src="{{ $page->featured_image_url }}" alt="{{ $page->featured_image_alt ?: $page->title }}" class="aspect-video w-full rounded-2xl object-cover">@endif
            <input name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input p-2">
            <p class="admin-help">JPG، PNG یا WEBP؛ حداکثر ۱۰ مگابایت.</p>
            <label class="admin-label" for="featured_image_alt">متن جایگزین تصویر</label>
            <input id="featured_image_alt" name="featured_image_alt" maxlength="255" class="admin-input" value="{{ old('featured_image_alt', $page->featured_image_alt ?? '') }}">
            @error('featured_image')<p class="admin-error">{{ $message }}</p>@enderror
        </section>
    </aside>
</div>