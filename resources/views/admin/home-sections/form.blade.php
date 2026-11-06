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
    $initialSlides = collect($savedSlides)->take(4)->map(function ($slide) {
        $path = $slide['image_path'] ?? null;
        $slide['image_url'] = $path
            ? (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') ? $path : \Illuminate\Support\Facades\Storage::disk('public')->url($path))
            : null;
        $slide['object_position'] = $slide['object_position'] ?? '50% 50%';
        return $slide;
    })->values()->all();
@endphp

@section('content')
<div class="mx-auto max-w-5xl space-y-6" x-data="homeSectionEditor(@js($initialSlides))">
    <header><a href="{{ route('admin.home-sections.index') }}" class="text-xs text-[var(--admin-accent)]">← بازگشت به سکشن‌ها</a><h1 class="admin-title mt-3">{{ $section->exists ? 'ویرایش سکشن' : 'افزودن سکشن' }}</h1></header>
    <form method="POST" enctype="multipart/form-data" action="{{ $section->exists ? route('admin.home-sections.update', $section) : route('admin.home-sections.store') }}" class="admin-card space-y-5 p-6">
        @csrf @if($section->exists) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="admin-label">شناسه یکتا</span><input name="key" class="admin-input" required maxlength="100" value="{{ old('key', $section->key) }}" placeholder="مثلاً featured-products">@error('key')<small class="admin-error">{{ $message }}</small>@enderror</label>
            <label><span class="admin-label">نوع سکشن</span><select name="type" x-model="sectionType" class="admin-input" required>@foreach(['hero'=>'Hero','products'=>'محصولات','categories'=>'دسته‌بندی‌ها','editorial'=>'محتوای تصویری','trust'=>'ویژگی‌ها','cta'=>'دعوت به اقدام'] as $value=>$label)<option value="{{ $value }}" @selected(old('type', $section->type) === $value)>{{ $label }}</option>@endforeach</select></label>
        </div>

        <section x-show="sectionType === 'hero'" x-cloak class="space-y-4 rounded-2xl border border-[var(--admin-border)] p-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="text-lg font-bold">اسلایدهای هیرو</h2><p class="admin-help">برای هر اسلاید تصویر، متن و لینک جداگانه تعریف کنید. حداکثر ۴ اسلاید.</p></div>
                <button type="button" class="admin-btn admin-btn-secondary" @click="addSlide()" :disabled="slides.length >= 4">افزودن اسلاید</button>
            </div>
            <fieldset :disabled="sectionType !== 'hero'" class="space-y-4">
            <template x-for="(slide, index) in slides" :key="slide.uid">
                <article class="space-y-4 rounded-2xl bg-[var(--admin-surface)] p-4">
                    <div class="flex items-center justify-between gap-3"><strong x-text="'اسلاید ' + (index + 1)"></strong><button type="button" class="admin-btn admin-btn-danger" @click="removeSlide(index)" :disabled="slides.length === 1">حذف اسلاید</button></div>
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="space-y-3">
                            <label class="block"><span class="admin-label">تصویر اسلاید</span><input type="file" accept="image/jpeg,image/png,image/webp" :name="'slide_images[' + index + ']'" data-crop-ratio="1.5" class="admin-input" @change="previewFile($event, slide)"><small class="admin-help">تصویر در قاب ۳:۲ برش می‌خورد؛ می‌توانید در پنجره برش، کادر را جابه‌جا و بزرگ‌نمایی کنید.</small></label>
                            <input type="hidden" :name="'payload[slides][' + index + '][image_path]'" x-model="slide.image_path">
                            <label class="block"><span class="admin-label">Eyebrow</span><input class="admin-input" :name="'payload[slides][' + index + '][eyebrow]'" x-model="slide.eyebrow" maxlength="160"></label>
                            <label class="block"><span class="admin-label">عنوان</span><input class="admin-input" :name="'payload[slides][' + index + '][title]'" x-model="slide.title" maxlength="255" required></label>
                            <label class="block"><span class="admin-label">توضیحات</span><textarea class="admin-textarea" rows="3" :name="'payload[slides][' + index + '][subtitle]'" x-model="slide.subtitle" maxlength="1500"></textarea></label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label><span class="admin-label">متن دکمه</span><input class="admin-input" :name="'payload[slides][' + index + '][cta_label]'" x-model="slide.cta_label" maxlength="120"></label>
                                <label><span class="admin-label">لینک دکمه</span><input class="admin-input" dir="ltr" :name="'payload[slides][' + index + '][cta_url]'" x-model="slide.cta_url" maxlength="2048" placeholder="/shop"></label>
                            </div>
                            <input type="hidden" :name="'payload[slides][' + index + '][object_position]'" x-model="slide.object_position">
                        </div>
                        <div class="space-y-2">
                            <span class="admin-label">پیش‌نمایش واقعی هیرو</span>
                            <div class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-white">
                                <div class="grid items-center gap-3 p-3 sm:grid-cols-2">
                                    <div class="space-y-2 text-right">
                                        <p class="text-[10px] font-semibold text-amber-700" x-text="slide.eyebrow || 'متن بالای عنوان'"></p>
                                        <h3 class="text-lg font-bold leading-tight text-gray-900" x-text="slide.title || 'عنوان هیرو در این قسمت نمایش داده می‌شود'"></h3>
                                        <p class="text-xs leading-6 text-gray-600" x-text="slide.subtitle || 'توضیحات اسلاید در این قسمت نمایش داده می‌شود.'"></p>
                                        <span class="inline-flex rounded-lg bg-gray-900 px-3 py-2 text-[10px] text-white" x-text="slide.cta_label || 'متن دکمه'"></span>
                                    </div>
                                    <div class="aspect-[3/2] overflow-hidden rounded-xl bg-gray-100">
                                        <img :src="slide.preview || slide.image_url || ''" :style="'object-position:' + (slide.object_position || '50% 50%')" class="h-full w-full object-cover" alt="پیش‌نمایش تصویر اسلاید" x-show="slide.preview || slide.image_url">
                                        <div class="flex h-full items-center justify-center text-xs text-gray-400" x-show="!(slide.preview || slide.image_url)">تصویر اسلاید پس از انتخاب اینجا دیده می‌شود</div>
                                    </div>
                                </div>
                                <div class="flex justify-center gap-2 border-t border-gray-100 p-3" dir="ltr">
                                    <template x-for="(dot, dotIndex) in slides" :key="dot.uid"><span class="h-2 w-2 rounded-full" :class="dotIndex === index ? 'bg-blue-600' : 'bg-gray-300'"></span></template>
                                </div>
                            </div>
                            <p class="admin-help">این پیش‌نمایش، نسبت تصویر و چیدمان دو ستونه نسخه دسکتاپ را نشان می‌دهد؛ نمایش موبایل نیز جداگانه ریسپانسیو است.</p>
                        </div>
                    </div>
                </article>
            </template>
            </fieldset>
        </section>

        <section x-show="sectionType !== 'hero'" x-cloak class="grid gap-4 sm:grid-cols-2">
            <label><span class="admin-label">Eyebrow</span><input name="eyebrow" class="admin-input" maxlength="160" value="{{ old('eyebrow', $section->eyebrow) }}"></label>
            <label><span class="admin-label">عنوان</span><input name="title" class="admin-input" maxlength="255" value="{{ old('title', $section->title) }}"></label>
            <label class="sm:col-span-2"><span class="admin-label">توضیحات</span><textarea name="subtitle" rows="3" class="admin-textarea">{{ old('subtitle', $section->subtitle) }}</textarea></label>
            <label><span class="admin-label">متن دکمه</span><input name="cta_label" class="admin-input" value="{{ old('cta_label', $section->cta_label) }}"></label>
            <label><span class="admin-label">لینک دکمه</span><input name="cta_url" class="admin-input" dir="ltr" value="{{ old('cta_url', $section->cta_url) }}" placeholder="/shop"></label>
            <label class="sm:col-span-2"><span class="admin-label">تصویر سکشن</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-crop-ratio="1.25" class="admin-input"><input type="hidden" name="image_path" value="{{ old('image_path', $section->image_path) }}">@if($section->image_url)<img src="{{ $section->image_url }}" alt="" class="mt-3 max-h-48 rounded-xl object-cover">@endif</label>
        </section>

        <section x-show="['hero', 'products', 'categories'].includes(sectionType)" x-cloak class="rounded-2xl bg-[var(--admin-surface)] p-4 space-y-4">
            <h2 class="font-bold">منبع محتوا</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label><span class="admin-label">منبع محصولات</span><select name="payload[product_source]" class="admin-input">@foreach(['latest'=>'جدیدترین محصولات','new'=>'محصولات تازه','installments'=>'دارای اقساط','selected'=>'انتخاب دستی','category'=>'دسته‌بندی مشخص'] as $v=>$label)<option value="{{ $v }}" @selected(old('payload.product_source', data_get($section->payload, 'product_source', 'latest')) === $v)>{{ $label }}</option>@endforeach</select></label>
                <label><span class="admin-label">تعداد آیتم</span><input type="number" name="payload[limit]" min="1" max="24" class="admin-input" value="{{ old('payload.limit', data_get($section->payload, 'limit', 8)) }}"></label>
                <label><span class="admin-label">دسته‌بندی</span><select name="payload[category_id]" class="admin-input"><option value="">انتخاب نشده</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('payload.category_id', data_get($section->payload, 'category_id')) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label class="sm:col-span-2"><span class="admin-label">شناسه محصولات انتخابی (در حالت انتخاب دستی)</span><select multiple name="payload[product_ids][]" class="admin-input min-h-40">@foreach($products as $product)<option value="{{ $product->id }}" @selected(in_array($product->id, old('payload.product_ids', data_get($section->payload, 'product_ids', [])) ?? []))>{{ $product->name }} (#{{ $product->id }})</option>@endforeach</select><small class="admin-help">برای جلوگیری از تکرار، هر محصول فقط در یک سکشن انتخاب دستی مجاز است.</small></label>
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
</div>
<script>
function homeSectionEditor(initialSlides) {
    return {
        sectionType: @js(old('type', $section->type)),
        trustItems: @js(old('payload.items', data_get($section->payload, 'items', []))),
        slides: (Array.isArray(initialSlides) && initialSlides.length ? initialSlides : [{}]).map((slide, index) => ({ uid: 'saved-' + index, eyebrow: '', title: '', subtitle: '', cta_label: '', cta_url: '/shop', image_path: '', image_url: null, object_position: '50% 50%', preview: null, ...slide })),
        addSlide() {
            if (this.slides.length >= 4) return;
            this.slides.push({ uid: 'new-' + Date.now(), eyebrow: '', title: '', subtitle: '', cta_label: '', cta_url: '/shop', image_path: '', image_url: null, object_position: '50% 50%', preview: null });
            this.$nextTick(() => window.initSillaCropInputs?.());
        },
        removeSlide(index) {
            if (this.slides.length <= 1) return;
            this.slides.splice(index, 1);
        },
        previewFile(event, slide) {
            const file = event.target.files?.[0];
            if (!file) return;
            if (slide.preview) URL.revokeObjectURL(slide.preview);
            slide.preview = URL.createObjectURL(file);
        },
    };
}
</script>
@endsection
