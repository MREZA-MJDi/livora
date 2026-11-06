@extends('admin.layouts.app')

@section('title', $section->exists ? 'ویرایش سکشن' : 'افزودن سکشن')
@section('page_title', 'مدیریت سکشن صفحه اصلی')

@section('content')
<div class="mx-auto max-w-4xl space-y-6" x-data="{ sectionType: @js(old('type', $section->type)), trustItems: @js(old('payload.items', data_get($section->payload, 'items', []))) }">
    <header><a href="{{ route('admin.home-sections.index') }}" class="text-xs text-[var(--admin-accent)]">← بازگشت به سکشن‌ها</a><h1 class="admin-title mt-3">{{ $section->exists ? 'ویرایش سکشن' : 'افزودن سکشن' }}</h1></header>
    <form method="POST" enctype="multipart/form-data" action="{{ $section->exists ? route('admin.home-sections.update', $section) : route('admin.home-sections.store') }}" class="admin-card space-y-5 p-6">
        @csrf @if($section->exists) @method('PUT') @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <label><span class="admin-label">شناسه یکتا</span><input name="key" class="admin-input" required maxlength="100" value="{{ old('key', $section->key) }}" placeholder="مثلاً featured-products">@error('key')<small class="admin-error">{{ $message }}</small>@enderror</label>
            <label><span class="admin-label">نوع سکشن</span><select name="type" x-model="sectionType" class="admin-input" required>@foreach(['hero'=>'Hero','products'=>'محصولات','categories'=>'دسته‌بندی‌ها','editorial'=>'محتوای تصویری','trust'=>'ویژگی‌ها','cta'=>'دعوت به اقدام'] as $value=>$label)<option value="{{ $value }}" @selected(old('type', $section->type) === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="admin-label">Eyebrow</span><input name="eyebrow" class="admin-input" maxlength="160" value="{{ old('eyebrow', $section->eyebrow) }}"></label>
            <label><span class="admin-label">عنوان</span><input name="title" class="admin-input" maxlength="255" value="{{ old('title', $section->title) }}"></label>
            <label class="sm:col-span-2"><span class="admin-label">توضیحات</span><textarea name="subtitle" rows="3" class="admin-textarea">{{ old('subtitle', $section->subtitle) }}</textarea></label>
            <label><span class="admin-label">متن دکمه</span><input name="cta_label" class="admin-input" value="{{ old('cta_label', $section->cta_label) }}"></label>
            <label><span class="admin-label">لینک دکمه</span><input name="cta_url" class="admin-input" dir="ltr" value="{{ old('cta_url', $section->cta_url) }}" placeholder="/shop"></label>
            <label class="sm:col-span-2"><span class="admin-label">تصویر سکشن</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp" class="admin-input"><input type="hidden" name="image_path" value="{{ old('image_path', $section->image_path) }}">@if($section->image_url)<img src="{{ $section->image_url }}" alt="" class="mt-3 max-h-48 rounded-xl object-cover">@endif</label>
        </div>
        <section x-show="['hero', 'products', 'categories'].includes(sectionType)" class="rounded-2xl bg-[var(--admin-surface)] p-4 space-y-4">
            <h2 class="font-bold">منبع محتوا</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label><span class="admin-label">منبع محصولات</span><select name="payload[product_source]" class="admin-input">@foreach(['latest'=>'جدیدترین محصولات','new'=>'محصولات تازه','installments'=>'دارای اقساط','selected'=>'انتخاب دستی','category'=>'دسته‌بندی مشخص'] as $v=>$label)<option value="{{ $v }}" @selected(old('payload.product_source', data_get($section->payload, 'product_source', 'latest')) === $v)>{{ $label }}</option>@endforeach</select></label>
                <label><span class="admin-label">تعداد آیتم</span><input type="number" name="payload[limit]" min="1" max="24" class="admin-input" value="{{ old('payload.limit', data_get($section->payload, 'limit', 8)) }}"></label>
                <label><span class="admin-label">دسته‌بندی</span><select name="payload[category_id]" class="admin-input"><option value="">انتخاب نشده</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('payload.category_id', data_get($section->payload, 'category_id')) === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                <label class="sm:col-span-2"><span class="admin-label">شناسه محصولات انتخابی (در حالت انتخاب دستی)</span><select multiple name="payload[product_ids][]" class="admin-input min-h-40">@foreach($products as $product)<option value="{{ $product->id }}" @selected(in_array($product->id, old('payload.product_ids', data_get($section->payload, 'product_ids', [])) ?? []))>{{ $product->name }} (#{{ $product->id }})</option>@endforeach</select><small class="admin-help">برای جلوگیری از تکرار، هر محصول فقط در یک سکشن انتخاب دستی مجاز است.</small></label>
            </div>
        </section>
        <section x-show="sectionType === 'trust'" class="rounded-2xl border border-[var(--admin-border)] p-4 space-y-4">
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
@endsection
