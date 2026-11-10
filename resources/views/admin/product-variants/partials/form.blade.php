@php
    $editing = isset($productVariant);
    $currentProductId = old('product_id', $productVariant->product_id ?? $selectedProductId ?? '');
    $currentProduct = $products->firstWhere('id', (int) $currentProductId);
    $basePrice = (float) ($currentProduct->price ?? 0);
    $initialAdjustment = (float) old('price_adjustment', $productVariant->price_adjustment ?? 0);
    $initialFinalPrice = max(0, $basePrice + $initialAdjustment);
    $selectedImageIds = collect(old('image_ids', ($productVariant ?? null)?->images?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (int) $id)->all();
@endphp

@csrf

<div class="mx-auto w-full max-w-5xl space-y-5"
     x-data="{
        basePrice: {{ $basePrice }},
        adjustment: {{ $initialAdjustment }},
        finalPrice: {{ $initialFinalPrice }},
        attributeType: @js(old('type', $productVariant->type ?? ($defaultType ?? 'اندازه'))),
        colorHex: @js(old('color_hex', $productVariant->color_hex ?? '')),
        pickerColor: @js(old('color_hex', $productVariant->color_hex ?? '#C8A27A')),
        priceTouched: false,
        formatMoney(value) {
            return new Intl.NumberFormat('fa-IR').format(Math.max(0, Number(value) || 0));
        },
        syncPrice() {
            this.finalPrice = Math.max(0, Number(this.basePrice || 0) + Number(this.adjustment || 0));
        },
        setProduct(select) {
            const option = select.options[select.selectedIndex];
            this.basePrice = Number(option?.dataset.price || 0);
            if (!this.priceTouched) this.adjustment = 0;
            this.syncPrice();
        }
     }">

    <section class="admin-card overflow-hidden">
        <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 sm:px-6">
            <p class="text-xs font-semibold text-[var(--admin-accent)]">مرحله ۱ از ۳</p>
            <h3 class="mt-1 text-base font-bold text-[var(--admin-text)]">این تنوع برای کدام محصول است؟</h3>
            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">مثلاً محصول «مبل آریا» را انتخاب کن؛ لازم نیست محصول را دوباره بسازی.</p>
        </div>
        <div class="p-5 sm:p-6">
            <label for="product_id" class="admin-label">محصول اصلی</label>
            <select id="product_id" name="product_id" class="admin-select" required
                    @change="setProduct($event.target)" @disabled($editing)>
                <option value="">انتخاب محصول</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-price="{{ (float) $product->price }}"
                        @selected((int) $currentProductId === (int) $product->id)>
                        {{ $product->name }} — قیمت پایه: {{ number_format((float) $product->price) }} تومان
                    </option>
                @endforeach
            </select>
            @if($editing)<input type="hidden" name="product_id" value="{{ $currentProductId }}">@endif
            @error('product_id')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="admin-card overflow-hidden">
        <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 sm:px-6">
            <p class="text-xs font-semibold text-[var(--admin-accent)]">مرحله ۲ از ۳</p>
            <h3 class="mt-1 text-base font-bold text-[var(--admin-text)]">مشخصات قابل انتخاب توسط مشتری</h3>
            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">برای مبل، نوع ویژگی «اندازه» و مقدار آن «سه نفره» یا «شش نفره» است.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
            <div>
                <label for="type" class="admin-label">نوع تنوع</label>
                <select id="type" name="type" class="admin-select" x-model="attributeType" required>
                    @foreach(['رنگ', 'جنس', 'اندازه', 'مدل', 'نوع پارچه', 'سایر'] as $typeOption)
                        <option value="{{ $typeOption }}">{{ $typeOption }}</option>
                    @endforeach
                    @php
                        $storedType = old('type', $productVariant->type ?? ($defaultType ?? 'اندازه'));
                        $standardTypes = ['رنگ', 'جنس', 'اندازه', 'مدل', 'نوع پارچه', 'سایر'];
                    @endphp
                    @if(!in_array($storedType, $standardTypes, true))
                        <option value="{{ $storedType }}" selected>{{ $storedType }} (موجود)</option>
                    @endif
                </select>
                <p class="mt-1.5 text-xs text-[var(--admin-muted)]">گزینه‌های هم‌نوع در صفحه محصول زیر یک عنوان گروه‌بندی می‌شوند؛ برای همه رنگ‌ها همین نوع را انتخاب کن.</p>
                @error('type')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="name" class="admin-label">عنوانی که به مشتری نمایش داده می‌شود</label>
                <input id="name" name="name" class="admin-input" maxlength="255"
                       value="{{ old('name', $productVariant->name ?? ($defaultName ?? 'اندازه مبل')) }}" placeholder="مثلاً رنگ بدنه" required>
                <p class="mt-1.5 text-xs text-[var(--admin-muted)]">برای تمام گزینه‌های یک گروه، عنوان یکسان وارد کن؛ مثلاً «رنگ بدنه».</p>
                @error('name')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="value" class="admin-label">مقدار این تنوع</label>
                <input id="value" name="value" class="admin-input" maxlength="255"
                       value="{{ old('value', $productVariant->value ?? '') }}" placeholder="مثلاً گردویی، مخمل، سه‌نفره" required>
                <p class="mt-1.5 text-xs text-[var(--admin-muted)]">مثلاً برای نوع «جنس»، مقدار «چوب راش»؛ برای نوع «رنگ»، مقدار «گردویی».</p>
                @error('value')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sku" class="admin-label">کد اختصاصی این گزینه (اختیاری)</label>
                <input id="sku" name="sku" class="admin-input text-left" dir="ltr"
                       value="{{ old('sku', $productVariant->sku ?? '') }}" placeholder="SOFA-WALNUT-3">
                @error('sku')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="stock" class="admin-label">موجودی این گزینه</label>
                <input id="stock" name="stock" type="number" min="0" step="1" class="admin-input"
                       value="{{ old('stock', $productVariant->stock ?? 0) }}" required>
                @error('stock')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            </div>
            <div x-show="attributeType === 'رنگ'" x-cloak>
                <label for="color_hex" class="admin-label">رنگ نمایشی</label>
                <div class="flex items-center gap-3">
                    <input type="color" x-model="pickerColor" @input="colorHex = pickerColor"
                           class="h-11 w-14 cursor-pointer rounded-lg border border-[var(--admin-border)] bg-white p-1">
                    <input id="color_hex" name="color_hex" x-model="colorHex" :disabled="attributeType !== 'رنگ'"
                           class="admin-input text-left" dir="ltr" placeholder="#C8A27A"
                           maxlength="20">
                </div>
                <p class="mt-1.5 text-xs text-[var(--admin-muted)]">رنگ را انتخاب کن یا کد HEX را وارد کن تا به شکل نمونه‌رنگ در سایت نمایش داده شود.</p>
                @error('color_hex')<p class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            </div>
            <input type="hidden" name="color_hex" value="" :disabled="attributeType === 'رنگ'">

            <div class="flex items-center gap-3 rounded-xl border border-[var(--admin-border)] px-4 py-3">
                <input id="is_active" name="is_active" type="checkbox" value="1" class="h-4 w-4"
                       @checked(old('is_active', $productVariant->is_active ?? true))>
                <label for="is_active" class="text-sm font-medium text-[var(--admin-text)]">این گزینه در فروشگاه فعال باشد</label>
            </div>
        </div>
    </section>

    <section class="admin-card overflow-hidden">
        <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 sm:px-6">
            <p class="text-xs font-semibold text-[var(--admin-accent)]">قیمت این گزینه</p>
            <h3 class="mt-1 text-base font-bold text-[var(--admin-text)]">میزان تغییر قیمت را وارد کن</h3>
            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">این مبلغ، تغییر قیمت این گزینه نسبت به قیمت پایه محصول است؛ در انتخاب چند ویژگی، تغییر قیمت گزینه‌ها با هم جمع می‌شود.</p>
        </div>
        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
            <div>
                <label for="final_price" class="admin-label">مبلغ افزوده / کاهشی نسبت به قیمت پایه (تومان)</label>
                <input id="final_price" type="number" step="1" class="admin-input"
                       x-model.number="adjustment" @input="priceTouched = true; syncPrice()" required>
                <p class="mt-1.5 text-xs text-[var(--admin-muted)]">قیمت پایه محصول: <span x-text="formatMoney(basePrice)"></span> تومان</p>
            </div>
            <div class="flex flex-col justify-center rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                <span class="text-xs text-[var(--admin-muted)]">قیمت تخمینی این گزینه</span>
                <strong class="mt-2 text-xl text-[var(--admin-text)]" x-text="formatMoney(finalPrice) + ' تومان'"></strong>
                <span class="mt-1 text-xs text-[var(--admin-muted)]">مبلغ نهایی پس از جمع‌شدن تغییر قیمت تمام ویژگی‌های انتخابی در سبد خرید محاسبه می‌شود.</span>
            </div>
            <input type="hidden" name="price_adjustment" :value="adjustment">
            @error('price_adjustment')<p class="text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="admin-card overflow-hidden">
        <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 sm:px-6">
            <p class="text-xs font-semibold text-[var(--admin-accent)]">مرحله ۳ از ۳</p>
            <h3 class="mt-1 text-base font-bold text-[var(--admin-text)]">تصاویر مخصوص این گزینه</h3>
            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">تصاویر را از عکس‌های همین محصول انتخاب کن. با انتخاب این گزینه توسط مشتری، این تصاویر نمایش داده می‌شوند.</p>
        </div>
        <div class="p-5 sm:p-6">
            @if(($selectedProductImages ?? collect())->isNotEmpty())
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($selectedProductImages as $image)
                        <label class="group relative cursor-pointer overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] has-[:checked]:border-[var(--admin-accent)] has-[:checked]:ring-2 has-[:checked]:ring-[var(--admin-accent)]">
                            <input type="checkbox" name="image_ids[]" value="{{ $image->id }}" class="absolute right-3 top-3 z-10 h-4 w-4"
                                   @checked(in_array((int) $image->id, $selectedImageIds, true))>
                            <img src="{{ $image->url }}" alt="{{ $image->alt ?: 'تصویر محصول' }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                            <span class="block truncate px-3 py-2 text-xs text-[var(--admin-muted)]">تصویر {{ $loop->iteration }}</span>
                        </label>
                    @endforeach
                </div>
                @error('image_ids')<p class="mt-3 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                @error('image_ids.*')<p class="mt-3 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
            @else
                <div class="rounded-2xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-8 text-center">
                    <p class="text-sm font-semibold text-[var(--admin-text)]">هنوز تصویری برای این محصول ثبت نشده</p>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">اول محصول را ذخیره کن و از بخش تصاویر محصول، عکس‌ها را اضافه کن؛ بعداً می‌توانی آن‌ها را به این گزینه وصل کنی.</p>
                </div>
            @endif
        </div>
    </section>

    <div class="sticky bottom-3 z-20 flex flex-col-reverse gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)]/95 p-3 shadow-xl backdrop-blur sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ $currentProductId ? route('admin.products.edit', $currentProductId) : route('admin.products.index') }}"
           class="admin-btn admin-btn-secondary text-center">انصراف</a>
        <div class="flex flex-col gap-2 sm:flex-row">
            @unless($editing)
                <button type="submit" name="continue" value="1" class="admin-btn admin-btn-secondary min-h-11 px-5">
                    ثبت و افزودن گزینه بعدی
                </button>
            @endunless
            <button type="submit" class="admin-btn admin-btn-primary min-h-11 px-7">
                {{ $editing ? 'ذخیره تغییرات تنوع' : 'ثبت تنوع محصول' }}
            </button>
        </div>
    </div>
</div>
