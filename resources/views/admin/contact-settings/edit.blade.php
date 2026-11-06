@extends('admin.layouts.app')

@section('title', 'اطلاعات تماس')
@section('page_title', 'مدیریت تماس با ما')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <header>
        <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / CONTACT</p>
        <h1 class="admin-title">مدیریت صفحه تماس با ما</h1>
        <p class="admin-subtitle mt-2">اطلاعات تماس و پرسش‌های متداول این فرم مستقیماً از این بخش در سایت نمایش داده می‌شوند.</p>
    </header>

    @if(session('success'))
        <div class="admin-card border border-emerald-200 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.contact-settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="admin-card space-y-5 p-6">
            <div>
                <h2 class="text-base font-bold">هویت و ارتباطات فروشگاه</h2>
                <p class="admin-help mt-1">فقط مقدارهای ثبت‌شده در سایت نمایش داده می‌شوند؛ وارد نکردن یک مقدار، همان بخش را پنهان می‌کند.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="admin-label" for="brand_name">نام برند</label><input id="brand_name" name="brand_name" class="admin-input" maxlength="120" value="{{ old('brand_name', $settings->brand_name ?? config('app.name')) }}">@error('brand_name')<p class="admin-error">{{ $message }}</p>@enderror</div>
                <div><label class="admin-label" for="logo_path">نشانی لوگو</label><input id="logo_path" name="logo_path" type="url" dir="ltr" class="admin-input" value="{{ old('logo_path', $settings->logo_path ?? '') }}"><p class="admin-help">اختیاری؛ از رسانه‌های تأییدشده سایت استفاده کنید.</p>@error('logo_path')<p class="admin-error">{{ $message }}</p>@enderror</div>
                @foreach(['whatsapp_url' => 'واتساپ', 'telegram_url' => 'تلگرام', 'instagram_url' => 'اینستاگرام', 'eitaa_url' => 'ایتا', 'bale_url' => 'بله'] as $field => $label)
                    <div><label class="admin-label" for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="url" dir="ltr" class="admin-input" value="{{ old($field, $settings->{$field} ?? '') }}">@error($field)<p class="admin-error">{{ $message }}</p>@enderror</div>
                @endforeach
            </div>
            <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] px-4 py-3 text-xs"><input type="hidden" name="floating_actions_enabled" value="0"><input type="checkbox" name="floating_actions_enabled" value="1" class="admin-checkbox" @checked(old('floating_actions_enabled', $settings->floating_actions_enabled ?? false))> نمایش دکمه‌های شناور ارتباط در سایت</label>
        </section>

        <section class="admin-card space-y-5 p-6">
            <div><h2 class="text-base font-bold">معرفی برند و نشان‌های رسمی</h2><p class="admin-help mt-1">نام، لوگو و توضیح برند از همین منبع برای هدر، فوتر و اطلاعات سازمانی SEO استفاده می‌شوند.</p></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="sm:col-span-2"><span class="admin-label">توضیح برند</span><textarea name="brand_description" rows="3" maxlength="3000" class="admin-textarea">{{ old('brand_description', $settings->brand_description ?? '') }}</textarea>@error('brand_description')<p class="admin-error">{{ $message }}</p>@enderror</label>
                <label><span class="admin-label">لینک نماد اعتماد (Enamad)</span><input name="enamad_url" type="url" dir="ltr" class="admin-input" value="{{ old('enamad_url', $settings->enamad_url ?? '') }}">@error('enamad_url')<p class="admin-error">{{ $message }}</p>@enderror</label>
                <label><span class="admin-label">لینک ساماندهی</span><input name="samandehi_url" type="url" dir="ltr" class="admin-input" value="{{ old('samandehi_url', $settings->samandehi_url ?? '') }}">@error('samandehi_url')<p class="admin-error">{{ $message }}</p>@enderror</label>
                <label class="sm:col-span-2"><span class="admin-label">متن کپی‌رایت</span><input name="copyright_text" maxlength="255" class="admin-input" value="{{ old('copyright_text', $settings->copyright_text ?? '') }}" placeholder="تمام حقوق برای سیلا گالری محفوظ است.">@error('copyright_text')<p class="admin-error">{{ $message }}</p>@enderror</label>
            </div>
        </section>

        <section class="admin-card space-y-5 p-6" x-data="footerSettingsBuilder(
            @js(old('footer_menu.shop', data_get($settings->footer_menu, 'shop', []))),
            @js(old('footer_menu.guide', data_get($settings->footer_menu, 'guide', []))),
            @js(old('footer_menu.contact', data_get($settings->footer_menu, 'contact', []))),
            @js(old('payment_methods', $settings->payment_methods ?? []))
        )">
            <div><h2 class="text-base font-bold">سازنده منوی فوتر</h2><p class="admin-help mt-1">لینک‌های هر ستون را اضافه، حذف و ویرایش کنید. مسیر داخلی با / و لینک خارجی با https:// وارد شود.</p></div>
            <template x-for="group in groups" :key="group.key">
                <div class="space-y-3 rounded-2xl border border-[var(--admin-border)] p-4">
                    <div class="flex items-center justify-between gap-3"><h3 class="font-semibold" x-text="group.label"></h3><button type="button" class="admin-btn admin-btn-secondary" @click="addLink(group.key)">افزودن لینک</button></div>
                    <template x-for="(link, index) in menus[group.key]" :key="group.key + '-' + index">
                        <div class="grid gap-2 sm:grid-cols-[1fr_1.4fr_auto]">
                            <input class="admin-input" :name="'footer_menu[' + group.key + '][' + index + '][label]'" x-model="link.label" placeholder="عنوان لینک">
                            <input class="admin-input" dir="ltr" :name="'footer_menu[' + group.key + '][' + index + '][url]'" x-model="link.url" placeholder="/shop">
                            <button type="button" class="admin-btn admin-btn-danger" @click="menus[group.key].splice(index, 1)">حذف</button>
                        </div>
                    </template>
                    <p class="admin-help" x-show="menus[group.key].length === 0">برای این ستون هنوز لینکی ثبت نشده است.</p>
                </div>
            </template>
            <div class="space-y-3 rounded-2xl border border-[var(--admin-border)] p-4">
                <div class="flex items-center justify-between gap-3"><h3 class="font-semibold">روش‌های پرداخت و نشان‌های تصویری</h3><button type="button" class="admin-btn admin-btn-secondary" @click="payments.push({label:'', image:'', url:''})">افزودن نشان</button></div>
                <template x-for="(item, index) in payments" :key="'payment-' + index">
                    <div class="grid gap-2 sm:grid-cols-[1fr_1.3fr_1.3fr_auto]">
                        <input class="admin-input" :name="'payment_methods[' + index + '][label]'" x-model="item.label" placeholder="عنوان">
                        <input class="admin-input" dir="ltr" :name="'payment_methods[' + index + '][image]'" x-model="item.image" placeholder="URL تصویر نشان">
                        <input class="admin-input" dir="ltr" :name="'payment_methods[' + index + '][url]'" x-model="item.url" placeholder="لینک مقصد (اختیاری)">
                        <button type="button" class="admin-btn admin-btn-danger" @click="payments.splice(index, 1)">حذف</button>
                    </div>
                </template>
                <p class="admin-help">فقط نشان‌ها و روش‌هایی نمایش داده می‌شوند که واقعاً توسط فروشگاه تأیید شده باشند.</p>
            </div>
        </section>

        <section class="admin-card space-y-5 p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">اطلاعات اصلی تماس</h2>
                    <p class="admin-help mt-1">این مقادیر در کارت‌های تماس و بخش اطلاعات صفحه عمومی استفاده می‌شوند.</p>
                </div>
                <label class="flex items-center gap-2 rounded-xl border border-[var(--admin-border)] px-4 py-3 text-xs">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="admin-checkbox" @checked(old('is_active', $settings->is_active ?? true))>
                    نمایش اطلاعات تماس در سایت
                </label>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="admin-label" for="phone">شماره تماس</label>
                    <input id="phone" name="phone" class="admin-input" dir="ltr" value="{{ old('phone', $settings->phone ?? '') }}" placeholder="+98...">
                    @error('phone')<p class="admin-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="admin-label" for="email">ایمیل</label>
                    <input id="email" name="email" type="email" class="admin-input" dir="ltr" value="{{ old('email', $settings->email ?? '') }}">
                    @error('email')<p class="admin-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="admin-label" for="hours">ساعات کاری</label>
                    <textarea id="hours" name="hours" rows="3" class="admin-textarea">{{ old('hours', $settings->hours ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section
            class="admin-card space-y-5 p-6"
            x-data="{
                address: @js(old('address', $settings->address ?? '')),
                latitude: @js((string) old('map_latitude', $settings->map_latitude ?? '')),
                longitude: @js((string) old('map_longitude', $settings->map_longitude ?? '')),
                locationLink: '',
                mapNotice: '',
                get hasValidCoordinates() {
                    if (this.latitude === '' || this.longitude === '') return false;
                    const lat = Number(this.latitude);
                    const lng = Number(this.longitude);
                    return Number.isFinite(lat) && Number.isFinite(lng)
                        && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
                },
                get mapPreviewUrl() {
                    if (!this.hasValidCoordinates) return '';
                    return 'https://maps.google.com/maps?q='
                        + encodeURIComponent(this.latitude + ',' + this.longitude)
                        + '&z=15&output=embed';
                },
                applyLocationLink() {
                    const source = this.locationLink.trim();
                    const patterns = [
                        /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/i,
                        /@(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/,
                        /[?&](?:q|query|ll)=(-?\d+(?:\.\d+)?)(?:%2C|,)\s*(-?\d+(?:\.\d+)?)/i,
                        /^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/
                    ];
                    for (const pattern of patterns) {
                        const match = source.match(pattern);
                        if (!match) continue;
                        const lat = Number(match[1]);
                        const lng = Number(match[2]);
                        if (!Number.isFinite(lat) || !Number.isFinite(lng)
                            || lat < -90 || lat > 90 || lng < -180 || lng > 180) break;
                        this.latitude = lat.toFixed(7);
                        this.longitude = lng.toFixed(7);
                        this.mapNotice = 'مختصات از لینک نقشه دریافت شد. پس از ذخیره، همین موقعیت در صفحه تماس نمایش داده می‌شود.';
                        return;
                    }
                    this.mapNotice = 'مختصات از این لینک قابل تشخیص نبود. لینک کامل Google Maps یا دو عدد عرض و طول را وارد کنید.';
                }
            }"
        >
            <div>
                <h2 class="text-base font-bold">آدرس فروشگاه و انتخاب موقعیت روی Google Maps</h2>
                <p class="admin-help mt-1">آدرس و مختصات را همین‌جا تنظیم کنید. نقشه پیش‌نمایش زنده دارد و بعد از ذخیره در صفحه تماس عمومی نمایش داده می‌شود.</p>
            </div>

            <div class="grid gap-5 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="address">آدرس کامل فروشگاه</label>
                        <textarea id="address" name="address" rows="3" maxlength="2000" class="admin-textarea" x-model="address">{{ old('address', $settings->address ?? '') }}</textarea>
                        @error('address')<p class="admin-error mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="admin-label" for="google_map_location_link">لینک Google Maps یا مختصات</label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input id="google_map_location_link" type="text" inputmode="url" dir="ltr" class="admin-input min-w-0 flex-1" x-model="locationLink" placeholder="https://maps.google.com/... یا 35.6892,51.3890">
                            <button type="button" class="admin-btn admin-btn-secondary shrink-0" @click="applyLocationLink()">استخراج مختصات</button>
                        </div>
                        <p class="admin-help mt-2">لینک مکان را از Google Maps کپی کنید یا مختصات را به شکل latitude,longitude وارد کنید. لینک کوتاه maps.app.goo.gl را ابتدا باز و نشانی کامل را کپی کنید.</p>
                        <p x-cloak x-show="mapNotice" class="mt-2 text-xs leading-6 text-[var(--admin-accent)]" role="status" x-text="mapNotice"></p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="admin-label" for="map_latitude">عرض جغرافیایی (Latitude)</label>
                            <input id="map_latitude" name="map_latitude" type="number" min="-90" max="90" step="0.0000001" inputmode="decimal" class="admin-input" dir="ltr" x-model="latitude" aria-describedby="map-latitude-help">
                            <p id="map-latitude-help" class="admin-help mt-1">محدوده: ۹۰- تا ۹۰</p>
                            @error('map_latitude')<p class="admin-error mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="admin-label" for="map_longitude">طول جغرافیایی (Longitude)</label>
                            <input id="map_longitude" name="map_longitude" type="number" min="-180" max="180" step="0.0000001" inputmode="decimal" class="admin-input" dir="ltr" x-model="longitude" aria-describedby="map-longitude-help">
                            <p id="map-longitude-help" class="admin-help mt-1">محدوده: ۱۸۰- تا ۱۸۰</p>
                            @error('map_longitude')<p class="admin-error mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[var(--admin-surface)] p-4">
                        <p class="text-xs font-semibold text-[var(--admin-text)]">راهنمای انتخاب موقعیت</p>
                        <ol class="mt-2 list-inside list-decimal space-y-1 text-xs leading-6 text-[var(--admin-muted)]">
                            <li>محل فروشگاه را در Google Maps پیدا کنید.</li>
                            <li>نشانی کامل را کپی و در کادر لینک قرار دهید.</li>
                            <li>«استخراج مختصات» را بزنید و پیش‌نمایش را بررسی کنید.</li>
                            <li>در پایان، فرم تنظیمات را ذخیره کنید.</li>
                        </ol>
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-[var(--admin-text)]">پیش‌نمایش موقعیت فروشگاه</p>
                        <span class="rounded-full border border-[var(--admin-border)] px-3 py-1 text-[10px] text-[var(--admin-muted)]" x-text="hasValidCoordinates ? 'مختصات معتبر' : 'منتظر مختصات'"></span>
                    </div>
                    <div class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface)]">
                        <template x-if="hasValidCoordinates">
                            <iframe
                                :src="mapPreviewUrl"
                                title="پیش‌نمایش موقعیت فروشگاه در Google Maps"
                                class="aspect-[4/3] min-h-[260px] w-full border-0"
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                            ></iframe>
                        </template>
                        <div x-show="!hasValidCoordinates" class="flex min-h-[260px] aspect-[4/3] flex-col items-center justify-center px-6 py-10 text-center">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-lg text-[var(--admin-muted)]" aria-hidden="true">⌖</span>
                            <p class="mt-4 text-sm font-semibold text-[var(--admin-text)]">هنوز موقعیت دقیقی انتخاب نشده</p>
                            <p class="mt-2 max-w-sm text-xs leading-6 text-[var(--admin-muted)]">برای جلوگیری از نمایش آدرس اشتباه، تا زمانی که مختصات معتبر وارد نشود نقشه‌ای روی سایت مشتری نمایش داده نمی‌شود.</p>
                        </div>
                    </div>
                    <template x-if="hasValidCoordinates">
                        <a :href="'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(latitude + ',' + longitude)" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-[var(--admin-accent)]">باز کردن مسیر در Google Maps <span aria-hidden="true">↗</span></a>
                    </template>
                </div>
            </div>

            @foreach(['warranty_text' => 'متن گارانتی', 'shipping_text' => 'متن ارسال', 'installation_text' => 'متن نصب/خدمات', 'returns_text' => 'متن مرجوعی'] as $field => $label)
                <div>
                    <label class="admin-label" for="{{ $field }}">{{ $label }}</label>
                    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" maxlength="5000" class="admin-textarea">{{ old($field, $settings->{$field} ?? '') }}</textarea>
                    @error($field)<p class="admin-error mt-1">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </section>

        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">عنوان و توضیحات کارت‌ها</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['phone_label'=>'عنوان کارت تلفن','email_label'=>'عنوان کارت ایمیل','support_label'=>'عنوان پشتیبانی','online_label'=>'عنوان ارتباط آنلاین'] as $field=>$label)
                    <div>
                        <label class="admin-label" for="{{ $field }}">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" class="admin-input" value="{{ old($field, $settings->{$field} ?? match($field) {'phone_label'=>'تماس تلفنی','email_label'=>'ایمیل','support_label'=>'پشتیبانی','online_label'=>'ارتباط آنلاین'}) }}">
                        @error($field)<p class="admin-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div>
                    <label class="admin-label" for="support_description">توضیح پشتیبانی</label>
                    <textarea id="support_description" name="support_description" rows="3" class="admin-textarea">{{ old('support_description', $settings->support_description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="admin-label" for="online_description">توضیح ارتباط آنلاین</label>
                    <textarea id="online_description" name="online_description" rows="3" class="admin-textarea">{{ old('online_description', $settings->online_description ?? '') }}</textarea>
                </div>
            </div>
        </section>

        @php
            $copyFields = [
                'hero_kicker' => ['label' => 'بالای تیتر اصلی', 'type' => 'text', 'default' => 'GET IN TOUCH'],
                'hero_title' => ['label' => 'تیتر اصلی', 'type' => 'text', 'default' => 'برای انتخاب بهتر،'],
                'hero_highlight' => ['label' => 'بخش تأکیدی تیتر', 'type' => 'text', 'default' => 'کنار شما هستیم.'],
                'hero_description' => ['label' => 'توضیح ابتدای صفحه', 'type' => 'textarea', 'default' => 'برای مشاوره خرید، پیگیری سفارش و دریافت اطلاعات بیشتر با ما در ارتباط باشید.'],
                'hero_cta' => ['label' => 'متن دکمه ابتدای صفحه', 'type' => 'text', 'default' => 'ارسال پیام'],
                'breadcrumb_label' => ['label' => 'متن مسیر راهنما', 'type' => 'text', 'default' => 'تماس با ما'],
                'address_label' => ['label' => 'عنوان بخش آدرس', 'type' => 'text', 'default' => 'ADDRESS'],
                'hours_label' => ['label' => 'عنوان بخش ساعات کاری', 'type' => 'text', 'default' => 'HOURS'],
                'installment_label' => ['label' => 'عنوان بخش اقساط', 'type' => 'text', 'default' => 'INSTALLMENT'],
                'info_kicker' => ['label' => 'برچسب بخش اطلاعات', 'type' => 'text', 'default' => "LET'S TALK"],
                'info_title' => ['label' => 'تیتر بخش اطلاعات', 'type' => 'text', 'default' => 'چه کمکی از دست ما برمی‌آید؟'],
                'info_description' => ['label' => 'توضیح بخش اطلاعات', 'type' => 'textarea', 'default' => 'برای پرسش درباره محصول، موجودی، قیمت، ارسال یا سفارش خود پیام بگذارید.'],
                'installment_note' => ['label' => 'توضیح شرایط اقساط', 'type' => 'textarea', 'default' => 'برای اطلاع از شرایط اقساط هر محصول، صفحه همان محصول را بررسی کنید یا از طریق فرم با ما تماس بگیرید.'],
                'form_kicker' => ['label' => 'برچسب فرم', 'type' => 'text', 'default' => 'CONTACT FORM'],
                'form_title' => ['label' => 'تیتر فرم', 'type' => 'text', 'default' => 'پیام خود را ارسال کنید'],
                'form_description' => ['label' => 'توضیح فرم', 'type' => 'textarea', 'default' => 'اطلاعات تماس خود را وارد کنید تا بتوانیم پاسخ دقیق‌تری ارائه دهیم.'],
                'faq_kicker' => ['label' => 'برچسب FAQ', 'type' => 'text', 'default' => 'FAQ'],
                'faq_title' => ['label' => 'تیتر FAQ', 'type' => 'text', 'default' => 'پرسش‌های متداول'],
                'faq_description' => ['label' => 'توضیح FAQ', 'type' => 'textarea', 'default' => 'پاسخ چند سؤال رایج درباره ارتباط با فروشگاه و خرید.'],
                'final_kicker' => ['label' => 'برچسب فراخوان پایانی', 'type' => 'text', 'default' => 'SillaGallery'],
                'final_title' => ['label' => 'تیتر فراخوان پایانی', 'type' => 'text', 'default' => 'آماده‌ای انتخابت را پیدا کنی؟'],
                'final_description' => ['label' => 'توضیح فراخوان پایانی', 'type' => 'textarea', 'default' => 'مجموعه محصولات را ببین و انتخاب بعدی‌ات را پیدا کن.'],
                'final_cta' => ['label' => 'متن دکمه پایانی', 'type' => 'text', 'default' => 'ورود به فروشگاه'],
                'success_message' => ['label' => 'پیام موفقیت ارسال فرم', 'type' => 'textarea', 'default' => 'پیام شما با موفقیت ارسال شد.'],
            ];
        @endphp
        <section class="admin-card space-y-5 p-6">
            <h2 class="text-base font-bold">متن‌ها و بخش‌های صفحه تماس</h2>
            <p class="admin-help">تیترها، توضیحات، دکمه‌ها و پیام موفقیت از این بخش کنترل می‌شوند. ساختار و چیدمان صفحه ثابت می‌ماند.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($copyFields as $field => $config)
                    <div class="{{ $config['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                        <label class="admin-label" for="page-content-{{ $field }}">{{ $config['label'] }}</label>
                        @if($config['type'] === 'textarea')
                            <textarea id="page-content-{{ $field }}" name="page_content[{{ $field }}]" rows="3" maxlength="1000" class="admin-textarea">{{ old("page_content.{$field}", $settings->page_content[$field] ?? $config['default']) }}</textarea>
                        @else
                            <input id="page-content-{{ $field }}" name="page_content[{{ $field }}]" maxlength="255" class="admin-input" value="{{ old("page_content.{$field}", $settings->page_content[$field] ?? $config['default']) }}">
                        @endif
                        @error("page_content.{$field}")<p class="admin-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
            </div>
        </section>

        <section class="admin-card space-y-5 p-6" x-data="{
            items: @js(old('faqs', $settings->faqs ?? [])),
            add() { this.items.push({ question: '', answer: '' }); },
            remove(index) { this.items.splice(index, 1); }
        }">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold">پرسش‌های متداول صفحه تماس</h2>
                    <p class="admin-help mt-1">حداکثر ۲۰ پرسش؛ ترتیب این فهرست همان ترتیب نمایش در سایت است.</p>
                </div>
                <button type="button" class="admin-btn admin-btn-secondary" @click="add()" :disabled="items.length >= 20">افزودن پرسش</button>
            </div>
            <template x-for="(faq, index) in items" :key="index">
                <div class="rounded-2xl border border-[var(--admin-border)] p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <strong class="text-sm" x-text="'پرسش ' + (index + 1)"></strong>
                        <button type="button" class="text-xs text-red-600" @click="remove(index)">حذف</button>
                    </div>
                    <label class="admin-label" :for="'faq-question-' + index">سؤال</label>
                    <input :id="'faq-question-' + index" class="admin-input" type="text" maxlength="255" x-model="faq.question" :name="'faqs[' + index + '][question]'">
                    <label class="admin-label mt-4" :for="'faq-answer-' + index">پاسخ</label>
                    <textarea :id="'faq-answer-' + index" class="admin-textarea" rows="3" maxlength="2000" x-model="faq.answer" :name="'faqs[' + index + '][answer]'"></textarea>
                </div>
            </template>
            <div x-show="items.length === 0" class="rounded-xl border border-dashed border-[var(--admin-border)] p-6 text-center text-sm text-[var(--admin-muted)]">
                هنوز پرسش متداولی ثبت نشده است.
            </div>
            @error('faqs.*.question')<p class="admin-error">{{ $message }}</p>@enderror
            @error('faqs.*.answer')<p class="admin-error">{{ $message }}</p>@enderror
        </section>

        <div class="flex flex-wrap justify-between gap-3">
            <a href="{{ route('contact') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary">مشاهده صفحه تماس</a>
            <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنظیمات تماس</button>
        </div>
    </form>
</div>
@push('scripts')
<script>
function footerSettingsBuilder(shop, guide, contact, payments) {
    return {
        groups: [{key:'shop',label:'ستون فروشگاه'},{key:'guide',label:'راهنمای مشتریان'},{key:'contact',label:'ارتباط با ما'}],
        menus: {shop:Array.isArray(shop)?shop:[],guide:Array.isArray(guide)?guide:[],contact:Array.isArray(contact)?contact:[]},
        payments: Array.isArray(payments)?payments:[],
        addLink(key) { this.menus[key].push({label:'',url:''}); }
    };
}
</script>
@endpush

@endsection
