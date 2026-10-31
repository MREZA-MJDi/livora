@php($editing = isset($post))
@csrf

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">محتوای مقاله</h2>
            <div><label class="admin-label" for="title">عنوان</label><input id="title" name="title" class="admin-input" maxlength="255" required value="{{ old('title', $post->title ?? '') }}">@error('title')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror</div>
            <div><label class="admin-label" for="slug">نشانی (Slug)</label><input id="slug" name="slug" class="admin-input" dir="ltr" value="{{ old('slug', $post->slug ?? '') }}" placeholder="article-slug">@error('slug')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror</div>
            <div><label class="admin-label" for="category">دسته مقاله</label><input id="category" name="category" class="admin-input" maxlength="120" value="{{ old('category', $post->category ?? '') }}"></div>
            <div><label class="admin-label" for="excerpt">خلاصه مقاله</label><textarea id="excerpt" name="excerpt" rows="3" maxlength="500" class="admin-textarea">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>@error('excerpt')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror</div>
            <div><label class="admin-label" for="body">متن مقاله</label><textarea id="body" name="body" rows="18" class="admin-textarea" required>{{ old('body', $post->body ?? '') }}</textarea><p class="mt-2 text-xs text-[var(--admin-muted)]">متن به‌صورت امن و بدون اجرای HTML ذخیره می‌شود.</p>@error('body')<p class="mt-2 text-xs text-red-500">{{ $message }}</p>@enderror</div>
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">SEO مقاله</h2>
            <div><label class="admin-label" for="meta_title">Meta Title</label><input id="meta_title" name="meta_title" class="admin-input" maxlength="255" value="{{ old('meta_title', $post->meta_title ?? '') }}"></div>
            <div><label class="admin-label" for="meta_description">Meta Description</label><textarea id="meta_description" name="meta_description" rows="3" maxlength="1000" class="admin-textarea">{{ old('meta_description', $post->meta_description ?? '') }}</textarea></div>
            <div class="grid gap-3 sm:grid-cols-2">
                <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_index" value="1" class="admin-checkbox" @checked(old('seo_index', $post->seo_index ?? false))><span class="text-sm">اجازه ایندکس مقاله</span></label>
                <label class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] p-4"><input type="checkbox" name="seo_follow" value="1" class="admin-checkbox" @checked(old('seo_follow', $post->seo_follow ?? false))><span class="text-sm">اجازه Follow لینک‌ها</span></label>
            </div>
            <p class="text-xs leading-6 text-[var(--admin-muted)]">تنظیم سراسری Noindex/Nofollow تا زمان تأیید SEO اولویت دارد.</p>
        </section>
    </div>

    <aside class="space-y-6">
        <section class="admin-card space-y-4 p-6">
            <h2 class="text-base font-bold">انتشار</h2>
            <div><label class="admin-label" for="status">وضعیت</label><select id="status" name="status" class="admin-select"><option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>پیش‌نویس</option><option value="published" @selected(old('status', $post->status ?? 'draft') === 'published')>منتشرشده</option></select></div>
            <div><label class="admin-label" for="published_at">زمان انتشار</label><input id="published_at" name="published_at" type="datetime-local" class="admin-input" value="{{ old('published_at', isset($post) && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"></div>
            <div class="flex flex-col gap-3"><button type="submit" class="admin-btn admin-btn-primary w-full">{{ $editing ? 'ذخیره مقاله' : 'ایجاد مقاله' }}</button><a href="{{ route('admin.blog.index') }}" class="admin-btn admin-btn-secondary w-full">بازگشت</a></div>
        </section>

        <section class="admin-card space-y-4 p-6">
            <h2 class="text-base font-bold">تصویر شاخص</h2>
            @if($editing && $post->featured_image_url)<img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-video w-full rounded-2xl object-cover">@endif
            <input id="featured_image" name="featured_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input p-2">
            <p class="text-xs text-[var(--admin-muted)]">JPG، PNG، WEBP — حداکثر ۱۰ مگابایت</p>
            <div><label class="admin-label" for="featured_image_alt">Alt تصویر</label><input id="featured_image_alt" name="featured_image_alt" class="admin-input" maxlength="255" value="{{ old('featured_image_alt', $post->featured_image_alt ?? '') }}"></div>
            @error('featured_image')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
        </section>
    </aside>
</div>
