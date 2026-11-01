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
                    <label class="admin-label" for="address">آدرس</label>
                    <textarea id="address" name="address" rows="3" class="admin-textarea">{{ old('address', $settings->address ?? '') }}</textarea>
                </div>
                <div>
                    <label class="admin-label" for="hours">ساعات کاری</label>
                    <textarea id="hours" name="hours" rows="3" class="admin-textarea">{{ old('hours', $settings->hours ?? '') }}</textarea>
                </div>
            </div>
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
@endsection
