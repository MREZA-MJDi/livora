@extends('admin.layouts.app')

@section('title', $section->exists ? 'ویرایش سکشن' : 'افزودن سکشن')
@section('page_title', 'مدیریت سکشن صفحه اصلی')

@php
    $savedSlides = old('payload.slides', data_get($section->payload, 'slides', []));
    if (empty($savedSlides) && ($section->type === 'hero' || old('type') === 'hero')) {
        $savedSlides = [[
            'eyebrow' => old('eyebrow', $section->eyebrow),
            'title' => old('title', $section->title),
            'subtitle' => old('subtitle', $section->subtitle),
            'cta_label' => old('cta_label', $section->cta_label),
            'cta_url' => old('cta_url', $section->cta_url),
            'image_path' => $section->image_path,
            'object_position' => '50% 50%',
        ]];
    }
    $initialCategorySelection = $selectedCategory ? [['id' => $selectedCategory->id, 'label' => $selectedCategory->name, 'meta' => null]] : [];
    $initialProductSelections = $selectedProducts->map(fn ($product) => ['id' => $product->id, 'label' => $product->name, 'meta' => $product->sku])->values()->all();
    $initialSlides = collect($savedSlides)->take(4)->map(function ($slide, $index) use ($section) {
        $path = $slide['image_path'] ?? null;
        $slide['image_url'] = $path
            ? \App\Models\HomeSection::publicImageUrl($path)
            : ($index === 0 && filled($section->image_path) ? $section->image_url : null);
        $slide['object_position'] = $slide['object_position'] ?? '50% 50%';
        return $slide;
    })->values()->all();
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6" x-data="homeSectionEditor(@js($initialSlides), @js(route('shop.index')))">
    <header><a href="{{ route('admin.home-sections.index') }}" class="text-xs text-[var(--admin-accent)]">← بازگشت به سکشن‌ها</a><h1 class="admin-title mt-3">{{ $section->exists ? 'ویرایش سکشن' : 'افزودن سکشن' }}</h1></header>
    <form method="POST" enctype="multipart/form-data" action="{{ $section->exists ? route('admin.home-sections.update', $section) : route('admin.home-sections.store') }}" class="admin-card space-y-5 p-6">
        @csrf @if($section->exists) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="admin-label">شناسه داخلی (اختیاری)</span><input name="key" class="admin-input" maxlength="100" value="{{ old('key', $section->key) }}" placeholder="اگر نمی‌دانید، خالی بگذارید">@error('key')<small class="admin-error">{{ $message }}</small>@enderror<span class="admin-help">این شناسه فقط برای سیستم است و در سایت دیده نمی‌شود؛ اگر خالی بماند خودکار ساخته می‌شود.</span></label>
            <label><span class="admin-label">نوع سکشن</span><select name="type" x-model="sectionType" class="admin-input" required>@foreach(['hero'=>'اسلایدر صفحه اصلی','products'=>'محصولات','categories'=>'دسته‌بندی‌ها','editorial'=>'محتوای تصویری','trust'=>'ویژگی‌ها','cta'=>'دعوت به اقدام'] as $value=>$label)<option value="{{ $value }}" @selected(old('type', $section->type) === $value)>{{ $label }}</option>@endforeach</select></label>
        </div>

        <section x-show="sectionType === 'hero'" x-cloak class="space-y-4 rounded-2xl border border-[var(--admin-border)] p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="text-lg font-bold">اسلایدهای هیرو</h2><p class="admin-help">برای هر اسلاید تصویر، متن و لینک جداگانه تعریف کنید. حداکثر ۴ اسلاید.</p></div>
                <button type="button" class="admin-btn admin-btn-secondary" @click="addSlide()" :disabled="slides.length >= 4">افزودن اسلاید</button>
            </div>
            <fieldset :disabled="sectionType !== 'hero'" class="space-y-4">
            <template x-for="(slide, index) in slides" :key="slide.uid">
                <article class="space-y-4 rounded-2xl bg-[var(--admin-surface)] p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <strong x-text="'اسلاید ' + (index + 1)"></strong>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="admin-btn admin-btn-secondary" @click="previewIndex = index">نمایش در پیش‌نمایش</button>
                            <button type="button" class="admin-btn admin-btn-danger" @click="removeSlide(index)" :disabled="slides.length === 1">حذف اسلاید</button>
                        </div>
                    </div>
                    <div class="grid gap-4">
                        <div class="space-y-3">
                            <label class="block"><span class="admin-label">تصویر اسلاید</span><input type="file" accept="image/jpeg,image/png,image/webp" :name="'slide_images[' + index + ']'" data-crop-ratio="1.5" class="admin-input" @change="previewFile($event, slide)"><small class="admin-help">تصویر در قاب ۳:۲ برش می‌خورد؛ می‌توانید در پنجره برش، کادر را جابه‌جا و بزرگ‌نمایی کنید.</small></label>
                            <input type="hidden" :name="'payload[slides][' + index + '][image_path]'" x-model="slide.image_path">
                            <label class="block"><span class="admin-label">متن کوتاه بالای عنوان</span><input class="admin-input" :name="'payload[slides][' + index + '][eyebrow]'" x-model="slide.eyebrow" maxlength="160"></label>
                            <label class="block"><span class="admin-label">عنوان</span><input class="admin-input" :name="'payload[slides][' + index + '][title]'" x-model="slide.title" maxlength="255" required></label>
                            <label class="block"><span class="admin-label">توضیحات</span><textarea class="admin-textarea" rows="3" :name="'payload[slides][' + index + '][subtitle]'" x-model="slide.subtitle" maxlength="1500"></textarea></label>
                            <label class="block"><span class="admin-label">برچسب قیمت (اختیاری)</span><input class="admin-input" :name="'payload[slides][' + index + '][price_label]'" x-model="slide.price_label" maxlength="120" placeholder="مثلاً شروع از ..."></label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label><span class="admin-label">متن دکمه</span><input class="admin-input" :name="'payload[slides][' + index + '][cta_label]'" x-model="slide.cta_label" maxlength="120"></label>
                                <label><span class="admin-label">لینک دکمه</span><input class="admin-input" dir="ltr" :name="'payload[slides][' + index + '][cta_url]'" x-model="slide.cta_url" maxlength="2048" placeholder="/shop"></label>
                            </div>
                            <input type="hidden" :name="'payload[slides][' + index + '][object_position]'" x-model="slide.object_position">
                        </div>
                        
                    </div>
                </article>
            </template>
            </fieldset>
            <div class="space-y-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-3 sm:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold">پیش‌نمایش اسلاید جاری</h3>
                        <p class="admin-help">این پیش‌نمایش فقط اسلاید جاری را با ساختار و استایل عمومی نشان می‌دهد. فهرست محصولات از تنظیمات منبع محتوا ساخته می‌شود و در این قاب نمایش داده نمی‌شود.</p>
                    </div>
                    <span class="text-xs text-[var(--admin-muted)]" x-text="'اسلاید ' + (previewIndex + 1) + ' از ' + slides.length"></span>
                </div>
                <div class="sg-home-hero-preview overflow-hidden rounded-2xl border border-[var(--admin-border)]" style="--sg-border:#ddd8d0;--sg-white:#fbfaf8;--sg-accent:#9a7658;--sg-ink:#1c1b19;--sg-stone:#706a62;--sg-surface:#ece9e3;">
                    <section class="sg-home-hero bg-[var(--sg-white)]" aria-label="پیش‌نمایش هیرو">
                        <x-layout.container>
                        <div class="sg-home-hero__layout">
                            <div class="sg-home-hero__content" dir="ltr">
                                <div class="sg-home-hero__dots" role="tablist" aria-label="انتخاب اسلاید پیش‌نمایش">
                                    <template x-for="(item, dotIndex) in slides" :key="item.uid">
                                        <button type="button" class="sg-home-hero__dot" :class="dotIndex === previewIndex ? 'is-active' : ''" @click="previewIndex = dotIndex" :aria-label="'نمایش اسلاید ' + (dotIndex + 1)" :aria-pressed="dotIndex === previewIndex ? 'true' : 'false'"></button>
                                    </template>
                                </div>
                                <div class="sg-home-hero__copy" dir="rtl">
                                    <p class="sg-home-hero__eyebrow" x-show="slides[previewIndex]?.eyebrow" x-text="slides[previewIndex]?.eyebrow"></p>
                                    <h1 class="sg-home-hero__title" x-text="slides[previewIndex]?.title || ''"></h1>
                                    <p class="sg-home-hero__subtitle" x-show="slides[previewIndex]?.subtitle" x-text="slides[previewIndex]?.subtitle"></p>
                                    <p class="sg-home-hero__price" x-show="slides[previewIndex]?.price_label" x-text="slides[previewIndex]?.price_label"></p>
                                    <a class="sg-home-hero__cta" x-show="slides[previewIndex]?.cta_label"  :href="slides[previewIndex]?.cta_url || defaultCtaUrl" @click.prevent>
                                        <span x-text="slides[previewIndex]?.cta_label"></span><span aria-hidden="true">←</span>
                                    </a>
                                </div>
                            </div>
                            <div class="sg-home-hero__media">
                                <template x-if="(slides[previewIndex]?.preview || slides[previewIndex]?.image_url) && !slides[previewIndex]?.image_error">
                                    <img
                                        :src="slides[previewIndex].preview || slides[previewIndex].image_url"
                                        :style="'object-position:' + (slides[previewIndex]?.object_position || '50% 50%')"
                                        class="sg-home-hero__image is-active"
                                        :alt="slides[previewIndex]?.title || ''"
                                        x-on:error="slides[previewIndex].image_error = true"
                                        x-on:load="slides[previewIndex].image_error = false"
                                    >
                                </template>
                                <div class="sg-home-hero__placeholder" x-show="!(slides[previewIndex]?.preview || slides[previewIndex]?.image_url) || slides[previewIndex]?.image_error" x-text="slides[previewIndex]?.image_error ? 'بارگذاری تصویر ناموفق بود؛ تصویر را دوباره انتخاب کنید.' : 'تصویر هیرو را از داشبورد بارگذاری کنید'"></div>
                            </div>
                        </div>
                    </x-layout.container>
                    </section>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="admin-help">پیش‌نمایش ریسپانسیو اسلاید؛ فهرست محصولات منتخب در این پیش‌نمایش نیست.</span>
                    <div class="flex gap-2">
                        <button type="button" class="admin-btn admin-btn-secondary" @click="previewIndex = Math.max(0, previewIndex - 1)" :disabled="previewIndex === 0">اسلاید قبلی</button>
                        <button type="button" class="admin-btn admin-btn-secondary" @click="previewIndex = Math.min(slides.length - 1, previewIndex + 1)" :disabled="previewIndex >= slides.length - 1">اسلاید بعدی</button>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="sectionType !== 'hero'" x-cloak class="grid gap-4 sm:grid-cols-2">
            <label><span class="admin-label">Eyebrow</span><input name="eyebrow" class="admin-input" maxlength="160" value="{{ old('eyebrow', $section->eyebrow) }}"></label>
            <label><span class="admin-label">عنوان</span><input name="title" class="admin-input" maxlength="255" value="{{ old('title', $section->title) }}"></label>
            <label class="sm:col-span-2"><span class="admin-label">توضیحات</span><textarea name="subtitle" rows="3" class="admin-textarea">{{ old('subtitle', $section->subtitle) }}</textarea></label>
            <label><span class="admin-label">متن دکمه</span><input name="cta_label" class="admin-input" value="{{ old('cta_label', $section->cta_label) }}"></label>
            <label><span class="admin-label">لینک دکمه</span><input name="cta_url" class="admin-input" dir="ltr" value="{{ old('cta_url', $section->cta_url) }}" placeholder="/shop"></label>
            <div class="sm:col-span-2 space-y-2">
                <label class="admin-label" for="home-section-image">تصویر سکشن</label>
                <div class="overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)]">
                    <img id="home-section-image-preview" @if(filled($section->image_url)) src="{{ $section->image_url }}" @endif alt="{{ $section->title ?: 'تصویر سکشن' }}" class="{{ filled($section->image_url) ? '' : 'hidden' }} aspect-[5/4] w-full object-cover">
                    <div id="home-section-image-placeholder" class="{{ filled($section->image_url) ? 'hidden' : 'flex' }} aspect-[5/4] items-center justify-center text-xs text-[var(--admin-muted)]">تصویر این سکشن انتخاب نشده است</div>
                </div>
                <input id="home-section-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-crop-ratio="1.25" data-preview-target="home-section-image-preview" data-preview-placeholder="home-section-image-placeholder" class="admin-input">
                <input type="hidden" name="image_path" value="{{ old('image_path', $section->image_path) }}">
            </div>
        </section>

        <section x-show="['hero', 'products', 'categories'].includes(sectionType)" x-cloak class="rounded-2xl bg-[var(--admin-surface)] p-4 space-y-4">
            <h2 class="font-bold">منبع محتوا</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label><span class="admin-label">منبع محصولات</span><select name="payload[product_source]" class="admin-input">@foreach(['latest'=>'جدیدترین محصولات','new'=>'محصولات تازه','featured'=>'محصولات ویژه','popular'=>'پرفروش‌ترین محصولات','installments'=>'دارای اقساط','selected'=>'انتخاب دستی','category'=>'دسته‌بندی مشخص'] as $v=>$label)<option value="{{ $v }}" @selected(old('payload.product_source', data_get($section->payload, 'product_source', 'latest')) === $v)>{{ $label }}</option>@endforeach</select></label>
                <label><span class="admin-label">تعداد آیتم</span><input type="number" name="payload[limit]" min="1" max="24" class="admin-input" value="{{ old('payload.limit', data_get($section->payload, 'limit', 8)) }}"></label>
                <div x-data="adminCatalogueLookup(@js(route('admin.catalogue.lookups.categories')), @js($initialCategorySelection), false, 1, { active_only: true })" class="space-y-2">
                    <label class="admin-label" for="home-section-category-search">دسته‌بندی</label>
                    <input type="hidden" name="payload[category_id]" :value="selected[0]?.id || ''">
                    <input id="home-section-category-search" type="search" class="admin-input" x-model="term" x-on:input.debounce.300ms="search()" minlength="2" maxlength="80" autocomplete="off" placeholder="برای جستجو حداقل دو حرف وارد کنید">
                    <div x-show="results.length" x-cloak class="max-h-48 space-y-1 overflow-y-auto rounded-xl border border-[var(--admin-border)] bg-white p-2" role="listbox" aria-label="نتایج دسته‌بندی">
                        <template x-for="item in results" :key="item.id">
                            <button type="button" class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-right text-sm hover:bg-[var(--admin-surface)]" @click="choose(item)" role="option"><span x-text="item.label"></span></button>
                        </template>
                    </div>
                    <template x-for="item in selected" :key="item.id">
                        <div class="flex items-center justify-between rounded-lg border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3 py-2 text-sm"><span x-text="item.label"></span><button type="button" class="text-red-600" @click="remove(item.id)" aria-label="حذف دسته‌بندی انتخاب‌شده">×</button></div>
                    </template>
                    <p class="admin-help" role="status" aria-live="polite" x-text="status"></p>
                </div>
                <div x-data="adminCatalogueLookup(@js(route('admin.catalogue.lookups.products')), @js($initialProductSelections), true, 30, { active_only: true })" class="space-y-2 sm:col-span-2">
                    <label class="admin-label" for="home-section-product-search">محصولات انتخابی (در حالت انتخاب دستی)</label>
                    <template x-for="item in selected" :key="item.id">
                        <input type="hidden" name="payload[product_ids][]" :value="item.id">
                    </template>
                    <input id="home-section-product-search" type="search" class="admin-input" x-model="term" x-on:input.debounce.300ms="search()" minlength="2" maxlength="80" autocomplete="off" placeholder="نام محصول یا SKU را جستجو کنید">
                    <div x-show="results.length" x-cloak class="max-h-56 space-y-1 overflow-y-auto rounded-xl border border-[var(--admin-border)] bg-white p-2" role="listbox" aria-label="نتایج محصولات">
                        <template x-for="item in results" :key="item.id">
                            <button type="button" class="flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2 text-right text-sm hover:bg-[var(--admin-surface)]" @click="choose(item)" role="option"><span x-text="item.label"></span><small class="text-[var(--admin-muted)]" dir="ltr" x-text="item.meta || ''"></small></button>
                        </template>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="item in selected" :key="item.id">
                            <span class="inline-flex items-center gap-2 rounded-full border border-[var(--admin-border)] bg-[var(--admin-surface)] px-3 py-1.5 text-xs"><span x-text="item.label"></span><button type="button" class="text-red-600" @click="remove(item.id)" aria-label="حذف محصول انتخاب‌شده">×</button></span>
                        </template>
                    </div>
                    <p class="admin-help">حداکثر ۳۰ محصول؛ نتایج فقط پس از جستجوی شما و حداکثر ۲۰ مورد در هر درخواست دریافت می‌شوند. برای جلوگیری از تکرار، هر محصول فقط در یک سکشن انتخاب دستی مجاز است.</p>
                    <p class="admin-help" role="status" aria-live="polite" x-text="status"></p>
                </div>
            </div>
        </section>
        <section x-show="sectionType === 'trust'" x-cloak class="rounded-2xl border border-[var(--admin-border)] p-4 space-y-4">
            <div class="flex items-center justify-between gap-3"><h2 class="font-bold">آیتم‌های ویژگی و اعتماد</h2><button type="button" class="admin-btn admin-btn-secondary" @click="trustItems.push({title:'',body:''})">افزودن آیتم</button></div>
            <template x-for="(item, index) in trustItems" :key="'trust-' + index">
                <div class="grid gap-3 rounded-xl bg-[var(--admin-surface)] p-3 sm:grid-cols-[1fr_1.5fr_auto]">
                    <input class="admin-input" :name="'payload[items][' + index + '][title]'" x-model="item.title" maxlength="120" placeholder="عنوان">
                    <textarea class="admin-textarea" rows="2" :name="'payload[items][' + index + '][body]'" x-model="item.body" maxlength="500" placeholder="توضیح کوتاه"></textarea>
                    <button type="button" class="admin-btn admin-btn-danger" @click="trustItems.splice(index, 1)">حذف</button>
                </div>
            </template>
            <p class="admin-help">حداکثر ۸ مورد؛ عنوان و توضیح هر مورد الزامی است.</p>
        </section>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="enabled" value="1" @checked(old('enabled', $section->enabled))> نمایش این سکشن در صفحه اصلی</label>
        @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <div class="flex flex-wrap gap-3"><button class="admin-btn admin-btn-primary" type="submit">ذخیره سکشن</button><a href="{{ route('admin.home-sections.index') }}" class="admin-btn admin-btn-secondary">انصراف</a></div>
    </form>


<script>
function homeSectionEditor(initialSlides, defaultCtaUrl) {
    return {
        previewIndex: 0,
        sectionType: @js(old('type', $section->type)),
        defaultCtaUrl,
        trustItems: @js(old('payload.items', data_get($section->payload, 'items', []))),
        slides: (Array.isArray(initialSlides) && initialSlides.length ? initialSlides : [{}]).map((slide, index) => ({ uid: 'saved-' + index, eyebrow: '', title: '', subtitle: '', price_label: '', cta_label: '', cta_url: '/shop', image_path: '', image_url: null, object_position: '50% 50%', preview: null, image_error: false, ...slide })),
        addSlide() {
            if (this.slides.length >= 4) return;
            this.slides.push({ uid: 'new-' + Date.now(), eyebrow: '', title: '', subtitle: '', price_label: '', cta_label: '', cta_url: '/shop', image_path: '', image_url: null, object_position: '50% 50%', preview: null, image_error: false });
            this.$nextTick(() => window.initSillaCropInputs?.());
        },
        removeSlide(index) {
            if (this.slides.length <= 1) return;
            const removed = this.slides[index];
            if (removed?.preview) URL.revokeObjectURL(removed.preview);
            this.slides.splice(index, 1);
            this.previewIndex = Math.min(this.previewIndex, this.slides.length - 1);
        },
        previewFile(event, slide) {
            const file = event.target.files?.[0];
            if (!file) return;
            if (slide.preview) URL.revokeObjectURL(slide.preview);
            slide.preview = URL.createObjectURL(file);
            slide.image_error = false;
        },
    };
}
</script>
@endsection
