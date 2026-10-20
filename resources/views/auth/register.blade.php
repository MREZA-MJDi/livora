@extends('layouts.app')

@section('title', 'ثبت نام | SilaGallery')

@section('description', 'ایجاد حساب کاربری در SilaGallery')

@section('content')

    <section class="min-h-[calc(100vh-80px)]">

        <x-layout.container>

            <div class="flex min-h-[calc(100vh-80px)] items-center justify-center py-12">

                <div class="w-full max-w-lg">

                    <div class="text-center">

                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            JOIN SilaGallery
                        </p>

                        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)]">
                            ایجاد حساب
                        </h1>

                        <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">
                            برای خرید و پیگیری سفارش‌ها حساب SilaGallery خود را ایجاد کنید.
                        </p>

                    </div>

                    @if($errors->any())
                        <div
                            class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-6 text-red-700"
                            role="alert"
                        >
                            اطلاعات واردشده را بررسی کنید؛ خطای هر فیلد زیر همان فیلد نمایش داده می‌شود.
                        </div>
                    @endif

                    <div class="mt-8 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                        <form
                            action="{{ route('register.store') }}"
                            method="POST"
                            class="space-y-5"
                            novalidate
                        >

                            @csrf

                            <div class="grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    id="first_name"
                                    name="first_name"
                                    label="نام"
                                    placeholder="نام شما"
                                    autocomplete="given-name"
                                    :value="old('first_name')"
                                    required
                                />

                                <x-ui.input
                                    id="last_name"
                                    name="last_name"
                                    label="نام خانوادگی"
                                    placeholder="نام خانوادگی"
                                    autocomplete="family-name"
                                    :value="old('last_name')"
                                    required
                                />

                            </div>

                            <x-ui.input
                                id="email"
                                type="email"
                                name="email"
                                label="ایمیل"
                                placeholder="example@email.com"
                                autocomplete="email"
                                inputmode="email"
                                dir="ltr"
                                :value="old('email')"
                                required
                            />

                            <x-ui.input
                                id="phone"
                                type="tel"
                                name="phone"
                                label="شماره موبایل"
                                placeholder="09121234567"
                                autocomplete="tel"
                                inputmode="tel"
                                maxlength="11"
                                dir="ltr"
                                :value="old('phone')"
                                hint="در صورت وارد کردن، شماره را به شکل 09123456789 بنویسید."
                            />

                            <div class="grid gap-5 sm:grid-cols-2">

                                <x-ui.input
                                    id="password"
                                    type="password"
                                    name="password"
                                    label="رمز عبور"
                                    placeholder="حداقل ۸ کاراکتر"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="255"
                                    :hint="'حداقل ۸ کاراکتر، شامل حداقل یک حرف بزرگ، یک حرف کوچک و یک عدد.'"
                                    required
                                />

                                <x-ui.input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    label="تکرار رمز عبور"
                                    placeholder="رمز عبور را دوباره وارد کنید"
                                    autocomplete="new-password"
                                    minlength="8"
                                    maxlength="255"
                                    required
                                />

                            </div>

                            <div>

                                <label class="flex cursor-pointer items-start gap-3">

                                    <input
                                        id="terms"
                                        type="checkbox"
                                        name="terms"
                                        value="1"
                                        @checked(old('terms'))
                                        required
                                        aria-invalid="{{ $errors->has('terms') ? 'true' : 'false' }}"
                                        aria-describedby="terms-error"
                                        class="mt-1 h-4 w-4 rounded border-[var(--livora-border)] accent-[var(--livora-accent)] @error('terms') border-red-300 @enderror"
                                    >

                                    <span class="text-xs leading-6 text-[var(--livora-stone)]">
                                        با ایجاد حساب، با
                                        <a
                                            href="#"
                                            class="font-medium text-[var(--livora-accent)]"
                                        >
                                            قوانین و شرایط
                                        </a>
                                        SilaGallery موافقم.
                                    </span>

                                </label>

                                @if($errors->first('terms'))
                                    <p
                                        id="terms-error"
                                        class="mt-2 flex items-start gap-2 text-[11px] leading-6 text-red-600"
                                        role="alert"
                                    >
                                        <span aria-hidden="true">!</span>
                                        <span>{{ $errors->first('terms') }}</span>
                                    </p>
                                @endif

                            </div>

                            <x-ui.button
                                type="submit"
                                size="lg"
                                class="w-full"
                            >
                                ایجاد حساب
                            </x-ui.button>

                        </form>

                        <div class="mt-7 border-t border-[var(--livora-border)] pt-6 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                قبلاً حساب ساخته‌اید؟
                            </p>

                            <a
                                href="{{ route('login') }}"
                                class="mt-2 inline-block text-sm font-medium text-[var(--livora-accent)]"
                            >
                                ورود به حساب
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>

    </section>

@endsection
