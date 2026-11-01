@php
    $editing = isset($post);
    $publishedAtGregorian = old(
        'published_at',
        $editing && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : ''
    );
@endphp
@csrf
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">محتوای مقاله</h2>
            <div><label class="admin-label" for="title">عنوان مقاله</label><input id="title" name="title" class="admin-input" maxlength="255" required value="{{ old('title', $post->title ?? '') }}">@error('title')<p class="admin-error">{{ $message }}</p>@enderror</div>
            <div><label class="admin-label" for="slug">نامک (Slug)</label><input id="slug" name="slug" class="admin-input" dir="ltr" value="{{ old('slug', $post->slug ?? '') }}" placeholder="article-slug">@error('slug')<p class="admin-error">{{ $message }}</p>@enderror</div>
            <div class="grid gap-4 sm:grid-cols-2"><div><label class="admin-label" for="category">دسته مقاله</label><input id="category" name="category" class="admin-input" maxlength="120" value="{{ old('category', $post->category ?? '') }}"></div><div><label class="admin-label" for="tags">برچسب‌ها</label><input id="tags" name="tags" class="admin-input" maxlength="1000" value="{{ $tagNames ?? old('tags', '') }}" placeholder="دکوراسیون، مبلمان، راهنمای خرید"><p class="admin-help">نام برچسب‌ها را با ویرگول فارسی یا انگلیسی جدا کنید؛ حداکثر ۲۰ برچسب.</p>@error('tags')<p class="admin-error">{{ $message }}</p>@enderror</div></div>
            <div><label class="admin-label" for="excerpt">خلاصه مقاله</label><textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="admin-textarea">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>@error('excerpt')<p class="admin-error">{{ $message }}</p>@enderror</div>
            <div>
                <label class="admin-label">متن مقاله</label>
                <div x-data="richTextEditor(@js(route('admin.editor-images.store')))" class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-white">
                    <div class="flex flex-wrap gap-1 border-b border-[var(--admin-border)] bg-[var(--admin-surface)] p-2">
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('bold')"><strong>B</strong></button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('italic')"><em>I</em></button>
                        <button type="button" class="admin-btn admin-btn-ghost" @click="command('underline')"><u>U</u></button>
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
                    <div x-ref="editor" contenteditable="true" dir="rtl" role="textbox" aria-multiline="true" class="cms-editor min-h-[420px] p-5 text-sm leading-9 outline-none" @input="sync()" @keydown="onKeydown($event)" @paste="pastePlain($event)">{!! $bodyHtml !!}</div>
                    <textarea name="body" x-ref="output" class="hidden" required></textarea>
                    <div class="flex justify-between gap-2 border-t border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-3 text-xs text-[var(--admin-muted)]"><span x-text="message || 'ویرایشگر محتوای غنی؛ HTML ناامن هنگام ذخیره پاک‌سازی می‌شود.'"></span><span x-text="wordCount + ' واژه'"></span></div>
                </div>
                @error('body')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
        </section>
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">SEO مقاله</h2>
            <div><label class="admin-label" for="meta_title">Meta Title</label><input id="meta_title" name="meta_title" class="admin-input" maxlength="255" value="{{ old('meta_title', $post->meta_title ?? '') }}"></div>
            <div><label class="admin-label" for="meta_description">Meta Description</label><textarea id="meta_description" name="meta_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('meta_description', $post->meta_description ?? '') }}</textarea></div>
            <div><label class="admin-label" for="canonical_url">Canonical URL</label><input id="canonical_url" name="canonical_url" type="url" dir="ltr" class="admin-input" value="{{ old('canonical_url', $post->canonical_url ?? '') }}"></div>
            <div><label class="admin-label" for="og_image">Open Graph Image URL</label><input id="og_image" name="og_image" type="url" dir="ltr" class="admin-input" value="{{ old('og_image', $post->og_image ?? '') }}"></div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_index" value="1" class="admin-checkbox" @checked(old('seo_index', $post->seo_index ?? false))><span class="text-sm">اجازه ایندکس مقاله</span></label>
                <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_follow" value="1" class="admin-checkbox" @checked(old('seo_follow', $post->seo_follow ?? false))><span class="text-sm">اجازه Follow لینک‌ها</span></label>
            </div>
            <p class="admin-help">کنترل سراسری noindex/nofollow تا زمان تأیید سئو اولویت دارد.</p>
        </section>
    </div>
    <aside class="space-y-6">
        <section class="admin-card space-y-4 p-6">
            <h2 class="text-base font-bold">انتشار</h2>
            <div><label class="admin-label" for="status">وضعیت</label><select id="status" name="status" class="admin-select"><option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>پیش‌نویس</option><option value="published" @selected(old('status', $post->status ?? 'draft') === 'published')>منتشرشده</option></select></div>
<div
                x-data="jalaliDatePicker(@js($publishedAtGregorian))"
                x-init="init()"
                @click.outside="open = false"
                class="relative"
            >
                <label class="admin-label" for="published_at_display">زمان انتشار (تقویم شمسی)</label>
                <input type="hidden" name="published_at" :value="gregorianValue">

                <div class="grid grid-cols-[minmax(0,1fr)_minmax(110px,140px)_48px] gap-2">
                    <input id="published_at_display" type="text" readonly :value="displayValue" placeholder="انتخاب تاریخ شمسی" class="admin-input" aria-label="تاریخ انتشار شمسی">
                    <input type="time" x-model="time" @change="updateValue()" class="admin-input" aria-label="ساعت انتشار">
                    <button type="button" @click="open = !open" class="admin-btn admin-btn-secondary !min-h-0 !px-0" :aria-expanded="open.toString()" aria-label="بازکردن تقویم شمسی">▦</button>
                </div>

                <div x-show="open" x-cloak x-transition.origin.top class="absolute left-0 right-0 z-50 mt-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-4 shadow-xl sm:right-auto sm:w-[340px]">
                    <div class="flex items-center justify-between gap-3">
                        <button type="button" @click="changeMonth(-1)" class="admin-btn admin-btn-secondary !min-h-9 !px-3" aria-label="ماه قبل">‹</button>
                        <p class="text-sm font-bold" x-text="monthTitle"></p>
                        <button type="button" @click="changeMonth(1)" class="admin-btn admin-btn-secondary !min-h-9 !px-3" aria-label="ماه بعد">›</button>
                    </div>
                    <div class="mt-4 grid grid-cols-7 gap-1 text-center">
                        <template x-for="(weekday, index) in weekdays" :key="index">
                            <span class="py-2 text-[10px] font-semibold text-[var(--admin-muted)]" x-text="weekday"></span>
                        </template>
                        <template x-for="(day, index) in calendarDays" :key="index">
                            <button type="button" :disabled="!day" @click="selectDay(day)"
                                :class="{
                                    'invisible': !day,
                                    'bg-[var(--admin-text)] text-white': day && isSelected(day),
                                    'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]': day && isToday(day) && !isSelected(day),
                                    'hover:bg-[var(--admin-surface)]': day && !isSelected(day)
                                }"
                                class="flex aspect-square items-center justify-center rounded-xl text-xs transition"
                                x-text="day ? persianDigits(day) : ''"></button>
                        </template>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-[var(--admin-border)] pt-3">
                        <button type="button" @click="selectToday()" class="text-xs font-semibold text-[var(--admin-accent)]">امروز</button>
                        <button type="button" @click="clearDate()" class="text-xs text-[var(--admin-muted)]">پاک‌کردن تاریخ</button>
                    </div>
                </div>
                <p class="admin-help">تقویم و تاریخ نمایشی شمسی است؛ مقدار استاندارد میلادی فقط در فرم داخلی و دیتابیس ذخیره می‌شود.</p>
                @error('published_at')<p class="admin-error">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="admin-btn admin-btn-primary w-full">{{ $editing ? 'ذخیره مقاله' : 'ایجاد مقاله' }}</button><a href="{{ route('admin.blog.index') }}" class="admin-btn admin-btn-secondary w-full">بازگشت</a>
        </section>
        <section class="admin-card space-y-4 p-6">
            <h2 class="text-base font-bold">تصویر شاخص</h2>
            @if($editing && $post->featured_image_url)<img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-video w-full rounded-2xl object-cover">@endif
            <input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input p-2"><p class="admin-help">JPG، PNG یا WEBP — حداکثر ۱۰ مگابایت</p>
            <div><label class="admin-label" for="featured_image_alt">Alt تصویر</label><input id="featured_image_alt" name="featured_image_alt" class="admin-input" maxlength="255" value="{{ old('featured_image_alt', $post->featured_image_alt ?? '') }}"></div>
            @error('featured_image')<p class="admin-error">{{ $message }}</p>@enderror
        </section>
    </aside>
</div>