@extends('layouts.app')

@section('title', 'تسویه حساب | SillaGallery')

@section(
    'description',
    'تکمیل اطلاعات سفارش و ادامه فرآیند پرداخت در SillaGallery.'
)

@section('content')

    <div class="overflow-hidden bg-[var(--sg-cream)]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--sg-stone)]"
                    >
                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--sg-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('cart.index') }}"
                            class="transition hover:text-[var(--sg-ink)]"
                        >
                            سبد خرید
                        </a>

                        <span>/</span>

                        <span class="text-[var(--sg-ink)]">
                        تسویه حساب
                    </span>
                    </nav>

                    <div class="mt-8">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--sg-accent)]">
                            CHECKOUT
                        </p>

                        <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                            تکمیل سفارش
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--sg-stone)]">
                            اطلاعات تحویل را وارد کنید تا سفارش شما ثبت شود و
                            به صفحه انتخاب روش پرداخت منتقل شوید.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        <x-layout.container>

            <div class="py-8 sm:py-10 lg:py-14">

                {{-- =================================================
                     ERRORS
                ================================================== --}}

                @if($errors->any())

                    <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white">
                                !
                            </div>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-red-800">
                                    اطلاعات سفارش کامل نیست.
                                </p>

                                <p class="mt-1 text-xs leading-6 text-red-700">
                                    موارد زیر را بررسی کنید:
                                </p>

                                <ul class="mt-3 space-y-2 text-xs leading-6 text-red-700">

                                    @foreach($errors->all() as $error)

                                        <li class="flex items-start gap-2">
                                            <span>•</span>
                                            <span>{{ $error }}</span>
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- =================================================
                     SESSION ERROR
                ================================================== --}}

                @if(session('error'))

                    <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">

                        <div class="flex items-start gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white">
                                !
                            </div>

                            <div>

                                <p class="text-sm font-semibold text-red-800">
                                    {{ session('error') }}
                                </p>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- =================================================
                     FORM
                ================================================== --}}

                <form
                    action="{{ route('checkout.place') }}"
                    method="POST"
                    x-data="iranShippingPicker(
                        @js($shippingRegions),
                        @js(old('province', $defaultAddress?->province ?? '')),
                        @js(old('city', $defaultAddress?->city ?? '')),
                        @js(old('city_is_other', false))
                    )"
                    class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px]"
                >

                    @csrf


                    {{-- =================================================
                         LEFT
                    ================================================== --}}

                    <div class="space-y-6">

                        {{-- Contact --}}
                        <section class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-5 sm:p-7">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                    01 · CONTACT
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    اطلاعات تماس
                                </h2>

                                <p class="mt-2 text-xs leading-6 text-[var(--sg-stone)]">
                                    اطلاعاتی که برای ثبت و پیگیری سفارش استفاده می‌شود.
                                </p>

                            </div>


                            <div class="mt-7 grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    name="first_name"
                                    label="نام"
                                    value="{{ old('first_name', $defaultAddress?->first_name ?? '') }}"
                                    error="{{ $errors->first('first_name') }}"
                                    required
                                />

                                <x-ui.input
                                    name="last_name"
                                    label="نام خانوادگی"
                                    value="{{ old('last_name', $defaultAddress?->last_name ?? '') }}"
                                    error="{{ $errors->first('last_name') }}"
                                    required
                                />

                                <x-ui.input
                                    name="phone"
                                    label="شماره موبایل"
                                    value="{{ old('phone', $defaultAddress?->phone ?? auth()->user()->phone ?? '') }}"
                                    error="{{ $errors->first('phone') }}"
                                    inputmode="tel"
                                    required
                                />

                                <div>
                                    <x-ui.input
                                        type="email"
                                        name="email"
                                        label="ایمیل (اختیاری)"
                                        value="{{ old('email', auth()->user()->email ?? '') }}"
                                        error="{{ $errors->first('email') }}"
                                        placeholder="برای دریافت رسید، در صورت تمایل"
                                    />
                                    <p class="mt-1.5 text-[11px] leading-5 text-[var(--sg-stone)]">بدون ایمیل هم می‌توانید سفارش را ثبت کنید.</p>
                                </div>

                            </div>

                        </section>


                        {{-- Address --}}
                        <section class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-5 sm:p-7">

                            <div>

                                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                    02 · DELIVERY
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    آدرس تحویل
                                </h2>

                                <p class="mt-2 text-xs leading-6 text-[var(--sg-stone)]">
                                    سفارش به این آدرس ارسال خواهد شد.
                                </p>

                            </div>


                            <div class="mt-7 grid gap-5 sm:grid-cols-2">

                                <input type="hidden" name="province" x-model="province">
                                <input type="hidden" name="city" x-model="city">
                                <input type="hidden" name="city_is_other" :value="cityIsOther ? '1' : '0'">

                                <div>
                                    <label class="mb-2 block text-xs font-medium text-[var(--sg-ink)]">استان</label>
                                    <button
                                        type="button"
                                        @click="openProvinceModal()"
                                        aria-haspopup="dialog"
                                        aria-controls="iran-shipping-location-modal"
                                        class="flex min-h-[3.25rem] w-full items-center justify-between gap-3 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3 text-right text-sm transition hover:border-[var(--sg-accent)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--sg-accent)]"
                                    >
                                        <span class="min-w-0 truncate" :class="province ? 'text-[var(--sg-ink)]' : 'text-[var(--sg-stone)]'" x-text="province || 'انتخاب استان'"></span>
                                        <span class="shrink-0 text-[var(--sg-stone)]" aria-hidden="true">⌄</span>
                                    </button>
                                    @error('province')
                                        <p class="mt-2 text-[11px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-xs font-medium text-[var(--sg-ink)]">شهر</label>
                                    <button
                                        type="button"
                                        @click="openCityModal()"
                                        :disabled="!province"
                                        aria-haspopup="dialog"
                                        aria-controls="iran-shipping-location-modal"
                                        class="flex min-h-[3.25rem] w-full items-center justify-between gap-3 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3 text-right text-sm transition hover:border-[var(--sg-accent)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--sg-accent)] disabled:cursor-not-allowed disabled:opacity-55"
                                    >
                                        <span class="min-w-0 truncate" :class="city ? 'text-[var(--sg-ink)]' : 'text-[var(--sg-stone)]'" x-text="city || (province ? 'انتخاب شهر' : 'ابتدا استان را انتخاب کنید')"></span>
                                        <span class="shrink-0 text-[var(--sg-stone)]" aria-hidden="true">⌄</span>
                                    </button>
                                    @error('city')
                                        <p class="mt-2 text-[11px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>


                                <div class="sm:col-span-2">

                                    <x-ui.input
                                        name="address"
                                        label="آدرس کامل"
                                        value="{{ old('address', $defaultAddress?->address ?? '') }}"
                                        error="{{ $errors->first('address') }}"
                                        required
                                    />

                                </div>


                                <x-ui.input
                                    name="postal_code"
                                    label="کد پستی"
                                    value="{{ old('postal_code', $defaultAddress?->postal_code ?? '') }}"
                                    error="{{ $errors->first('postal_code') }}"
                                    inputmode="numeric"
                                    required
                                />

                                <x-ui.input
                                    name="unit"
                                    label="واحد"
                                    value="{{ old('unit', $defaultAddress?->unit ?? '') }}"
                                    error="{{ $errors->first('unit') }}"
                                />


                                <div class="sm:col-span-2">

                                    <label
                                        for="notes"
                                        class="mb-2 block text-xs font-medium text-[var(--sg-ink)]"
                                    >
                                        توضیحات سفارش
                                    </label>

                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="4"
                                        placeholder="توضیحات مربوط به ارسال، ساختمان، هماهنگی و..."
                                        class="w-full rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3.5 text-sm text-[var(--sg-ink)] outline-none transition placeholder:text-[var(--sg-stone)] focus:border-[var(--sg-ink)]"
                                    >{{ old('notes') }}</textarea>

                                    @error('notes')

                                    <p class="mt-2 text-[11px] text-red-600">
                                        {{ $message }}
                                    </p>

                                    @enderror

                                </div>

                            </div>

                        </section>

                    </div>


                    {{-- =================================================
                         SUMMARY
                    ================================================== --}}

                    <aside>

                        <div class="sticky top-24 rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] p-5 sm:p-7">

                            <div class="flex items-center justify-between gap-4">

                                <div>

                                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--sg-accent)]">
                                        ORDER SUMMARY
                                    </p>

                                    <h2 class="mt-2 text-lg font-semibold">
                                        خلاصه سفارش
                                    </h2>

                                </div>

                                <span class="rounded-full bg-[var(--sg-surface)] px-3 py-1.5 text-[10px] text-[var(--sg-stone)]">
                                {{ $cart->items->count() }}
                                آیتم
                            </span>

                            </div>


                            <div class="mt-6 space-y-3">

                                @foreach($cart->items as $item)

                                    @php
                                        $image =
                                            $item->product?->images?->first()?->url;

                                        $variantOptions =
                                            collect($item->variant_options ?? []);
                                    @endphp

                                    <div class="flex gap-3 rounded-2xl bg-[var(--sg-surface)] p-3">

                                        <div class="h-16 w-14 shrink-0 overflow-hidden rounded-xl bg-[var(--sg-white)]">

                                            @if($image)

                                                <img
                                                    src="{{ $image }}"
                                                    alt="{{ $item->product->name }}"
                                                    class="h-full w-full object-cover"
                                                >

                                            @else

                                                <div class="flex h-full w-full items-center justify-center text-[8px] tracking-widest text-[var(--sg-stone)]">
                                                    LV
                                                </div>

                                            @endif

                                        </div>


                                        <div class="min-w-0 flex-1">

                                            <p class="truncate text-xs font-semibold">
                                                {{ $item->product->name }}
                                            </p>

                                            @if($variantOptions->isNotEmpty())

                                                <div class="mt-1 flex flex-wrap gap-1.5">

                                                    @foreach($variantOptions as $option)

                                                        <span class="rounded-full border border-[var(--sg-border)] px-2 py-1 text-[9px] text-[var(--sg-stone)]">

                                                            {{ $option['name'] ?? $option['type'] ?? 'ویژگی' }}:
                                                            <span class="font-medium text-[var(--sg-ink)]">
                                                                {{ $option['value'] ?? '—' }}
                                                            </span>

                                                        </span>

                                                    @endforeach

                                                </div>

                                            @endif

                                            <p class="mt-2 text-[10px] text-[var(--sg-stone)]">
                                                تعداد:
                                                {{ number_format($item->quantity) }}
                                            </p>

                                            <p class="mt-2 text-[11px] font-semibold">
                                                {{ number_format(
                                                    (float) $item->unit_price * $item->quantity
                                                ) }}
                                                تومان
                                            </p>

                                        </div>

                                    </div>

                                @endforeach

                            </div>


                            {{-- Totals --}}
                            <div class="mt-6 border-t border-[var(--sg-border)] pt-5">

                                <div class="flex items-center justify-between text-xs">

                                <span class="text-[var(--sg-stone)]">
                                    مجموع
                                </span>

                                    <span class="font-semibold">
                                    {{ number_format($cart->subtotal()) }}
                                    تومان
                                </span>

                                </div>

                                <div class="mt-3 flex items-center justify-between text-xs">

                                <span class="text-[var(--sg-stone)]">
                                    ارسال
                                </span>

                                    <span class="font-semibold">
                                    رایگان
                                </span>

                                </div>


                                <div class="mt-5 border-t border-[var(--sg-border)] pt-5">

                                    <div class="flex items-end justify-between gap-4">

                                        <div>

                                            <p class="text-sm font-semibold">
                                                مبلغ نهایی
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--sg-stone)]">
                                                قبل از انتخاب روش پرداخت
                                            </p>

                                        </div>

                                        <div class="text-left">

                                            <p class="text-2xl font-bold tracking-tight">
                                                {{ number_format($cart->subtotal()) }}
                                            </p>

                                            <p class="mt-1 text-[10px] text-[var(--sg-stone)]">
                                                تومان
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Submit --}}
                            <button
                                type="submit"
                                class="mt-6 flex w-full items-center justify-center gap-3 rounded-2xl bg-[var(--sg-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]"
                            >

                            <span>
                                ثبت سفارش و ادامه پرداخت
                            </span>

                                <span class="text-white/60">
                                ←
                            </span>

                            </button>


                            <div class="mt-4 flex items-start gap-3 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-surface)] p-4">

                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[var(--sg-white)]">
                                ✓
                            </span>

                                <p class="text-[10px] leading-6 text-[var(--sg-stone)]">
                                    پس از ثبت سفارش، به صفحه انتخاب روش پرداخت
                                    منتقل می‌شوید.
                                </p>

                            </div>

                        </div>

                    </aside>

                {{-- Province → city selection modal --}}
                <div
                    x-show="modalOpen"
                    x-cloak
                    x-transition.opacity
                    @keydown.escape.window="closeModal()"
                    @click.self="closeModal()"
                    class="fixed inset-0 z-[120] flex items-end justify-center bg-black/55 p-0 backdrop-blur-[2px] sm:items-center sm:p-5"
                    style="display: none;"
                >
                    <section
                        id="iran-shipping-location-modal"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="iran-shipping-location-title"
                        tabindex="-1"
                        class="relative flex max-h-[88dvh] w-full max-w-xl flex-col overflow-hidden rounded-t-[1.75rem] border border-[var(--sg-border)] bg-[var(--sg-white)] shadow-2xl sm:max-h-[min(82dvh,760px)] sm:rounded-[1.75rem]"
                    >
                        <header class="flex items-start justify-between gap-4 border-b border-[var(--sg-border)] px-5 py-5 sm:px-6">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[var(--sg-accent)]">DELIVERY LOCATION</p>
                                <h2 id="iran-shipping-location-title" class="mt-2 text-lg font-semibold text-[var(--sg-ink)]" x-text="stepTitle"></h2>
                                <p class="mt-1 text-xs text-[var(--sg-stone)]" x-show="step !== 'province'" x-text="province"></p>
                            </div>
                            <button
                                type="button"
                                @click="closeModal()"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--sg-surface)] text-lg text-[var(--sg-ink)] transition hover:bg-[var(--sg-border)]"
                                aria-label="بستن پنجره انتخاب استان و شهر"
                            >×</button>
                        </header>

                        <div class="border-b border-[var(--sg-border)] p-4 sm:px-6">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    x-show="step !== 'province'"
                                    @click="backToProvinces()"
                                    class="flex h-11 shrink-0 items-center gap-2 rounded-xl border border-[var(--sg-border)] px-3 text-xs font-medium text-[var(--sg-ink)]"
                                >
                                    <span aria-hidden="true">→</span>
                                    استان‌ها
                                </button>
                                <label class="relative min-w-0 flex-1">
                                    <span class="sr-only">جستجو در فهرست</span>
                                    <input
                                        x-ref="locationSearch"
                                        x-model="search"
                                        type="search"
                                        autocomplete="off"
                                        class="min-h-11 w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-surface)] px-4 text-sm outline-none transition focus:border-[var(--sg-accent)]"
                                        :placeholder="step === 'province' ? 'نام استان را جستجو کنید...' : 'نام شهر را جستجو کنید...'"
                                    >
                                </label>
                            </div>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-4 sm:p-5">
                            <div x-show="step === 'province'" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <template x-for="region in filteredProvinces" :key="region.name">
                                    <button
                                        type="button"
                                        @click="selectProvince(region)"
                                        class="flex min-h-12 items-center justify-between gap-3 rounded-xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3 text-right text-sm text-[var(--sg-ink)] transition hover:border-[var(--sg-accent)] hover:bg-[var(--sg-surface)]"
                                    >
                                        <span x-text="region.name"></span>
                                        <span class="text-[10px] text-[var(--sg-stone)]" x-text="(region.cities?.length || 0) + ' شهر'"></span>
                                    </button>
                                </template>
                                <p x-show="filteredProvinces.length === 0" class="col-span-full py-8 text-center text-sm text-[var(--sg-stone)]">استانی با این نام پیدا نشد.</p>
                            </div>

                            <div x-show="step === 'city'" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <template x-for="item in filteredCities" :key="item">
                                    <button
                                        type="button"
                                        @click="selectCity(item)"
                                        class="flex min-h-12 items-center justify-between gap-3 rounded-xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3 text-right text-sm text-[var(--sg-ink)] transition hover:border-[var(--sg-accent)] hover:bg-[var(--sg-surface)]"
                                    >
                                        <span x-text="item"></span>
                                        <span class="text-[var(--sg-accent)]" aria-hidden="true">←</span>
                                    </button>
                                </template>
                                <p x-show="filteredCities.length === 0" class="col-span-full py-8 text-center text-sm text-[var(--sg-stone)]">شهری با این نام در فهرست پیدا نشد.</p>

                                <button
                                    type="button"
                                    @click="chooseOtherCity()"
                                    class="col-span-full mt-2 flex min-h-12 items-center justify-center gap-2 rounded-xl border border-dashed border-[var(--sg-accent)] bg-[var(--sg-surface)] px-4 py-3 text-sm font-semibold text-[var(--sg-ink)] transition hover:bg-[var(--sg-border)]"
                                >
                                    شهر من در فهرست نیست
                                    <span aria-hidden="true">＋</span>
                                </button>
                            </div>

                            <div x-show="step === 'other'" class="space-y-4">
                                <div>
                                    <label for="checkout-other-city" class="mb-2 block text-xs font-medium text-[var(--sg-ink)]">نام شهر</label>
                                    <input
                                        id="checkout-other-city"
                                        x-ref="otherCityInput"
                                        x-model="otherCity"
                                        @keydown.enter.prevent="confirmOtherCity()"
                                        type="text"
                                        maxlength="100"
                                        autocomplete="address-level2"
                                        class="min-h-12 w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-surface)] px-4 text-sm outline-none transition focus:border-[var(--sg-accent)]"
                                        placeholder="نام شهر را وارد کنید"
                                    >
                                    <p class="mt-2 text-[11px] leading-6 text-[var(--sg-stone)]">اگر شهر شما در فهرست نبود، نام آن را بنویسید. تیم فروشگاه پیش از ارسال، امکان ارسال به نشانی را بررسی می‌کند.</p>
                                </div>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <button type="button" @click="confirmOtherCity()" :disabled="otherCity.trim().length < 2" class="min-h-11 flex-1 rounded-xl bg-[var(--sg-ink)] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[var(--sg-accent)] disabled:cursor-not-allowed disabled:opacity-45">تأیید شهر</button>
                                    <button type="button" @click="step = 'city'; otherCity = ''; search = ''" class="min-h-11 rounded-xl border border-[var(--sg-border)] px-5 py-3 text-sm font-medium text-[var(--sg-ink)]">بازگشت به فهرست</button>
                                </div>
                            </div>
                        </div>

                        <footer class="border-t border-[var(--sg-border)] bg-[var(--sg-surface)] px-5 py-3 text-center text-[10px] leading-6 text-[var(--sg-stone)] sm:px-6">
                            ابتدا استان و سپس شهر را انتخاب کنید. در صورت نبودن نام شهر، امکان ثبت دستی وجود دارد.
                        </footer>
                    </section>
                </div>

                </form>

            </div>

        </x-layout.container>

    </div>

@endsection
