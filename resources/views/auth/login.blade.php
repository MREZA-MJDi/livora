@extends('layouts.app')

@section('title', 'ورود | SilaGallery')

@section('description', 'ورود به حساب کاربری SilaGallery')

@section('content')

    <section class="min-h-[calc(100vh-80px)]">

        <x-layout.container>

            <div class="flex min-h-[calc(100vh-80px)] items-center justify-center py-12">

                <div class="w-full max-w-md">

                    <div class="text-center">

                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            WELCOME BACK
                        </p>

                        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)]">
                            ورود به حساب
                        </h1>

                        <p class="mt-3 text-sm text-[var(--livora-stone)]">
                            برای ادامه وارد حساب SilaGallery خود شوید.
                        </p>

                    </div>

                    @if($errors->any())
                        <div
                            class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-xs leading-6 text-red-700"
                            role="alert"
                        >
                            اطلاعات ورود را بررسی کنید؛ خطای هر فیلد زیر همان فیلد نمایش داده می‌شود.
                        </div>
                    @endif

                    <div class="mt-8 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                        <form
                            action="{{ route('login.store') }}"
                            method="POST"
                            class="space-y-5"
                            novalidate
                        >

                            @csrf

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

                            <div>

                                <div class="mb-2 flex items-center justify-between gap-4">

                                    <label
                                        for="password"
                                        class="text-sm font-medium text-[var(--livora-ink)]"
                                    >
                                        رمز عبور
                                    </label>

                                    <a
                                        href="#"
                                        class="text-xs text-[var(--livora-accent)]"
                                    >
                                        رمز عبور را فراموش کرده‌اید؟
                                    </a>

                                </div>

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    autocomplete="current-password"
                                    minlength="8"
                                    maxlength="255"
                                    placeholder="رمز عبور"
                                    required
                                    aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                    aria-describedby="{{ $errors->has('password') ? 'password-error' : 'password-hint' }}"
                                    class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm text-[var(--livora-ink)] outline-none transition-all duration-300 placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-accent)] focus:ring-1 focus:ring-[var(--livora-accent)] @error('password') border-red-300 focus:border-red-500 focus:ring-red-500/10 @enderror"
                                >

                                @error('password')
                                    <p
                                        id="password-error"
                                        class="mt-2 flex items-start gap-2 text-[11px] leading-6 text-red-600"
                                        role="alert"
                                    >
                                        <span aria-hidden="true">!</span>
                                        <span>{{ $message }}</span>
                                    </p>
                                @else
                                    <p
                                        id="password-hint"
                                        class="mt-2 text-[11px] leading-6 text-[var(--livora-stone)]"
                                    >
                                        رمز عبور باید حداقل ۸ کاراکتر باشد.
                                    </p>
                                @enderror

                            </div>

                            <label class="flex cursor-pointer items-center gap-3">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                    class="h-4 w-4 rounded border-[var(--livora-border)] accent-[var(--livora-accent)]"
                                >

                                <span class="text-sm text-[var(--livora-stone)]">
                                    مرا به خاطر بسپار
                                </span>

                            </label>

                            <x-ui.button
                                type="submit"
                                size="lg"
                                class="w-full"
                            >
                                ورود به حساب
                            </x-ui.button>

                        </form>

                        <div class="mt-7 border-t border-[var(--livora-border)] pt-6 text-center">

                            <p class="text-sm text-[var(--livora-stone)]">
                                هنوز حساب کاربری ندارید؟
                            </p>

                            <a
                                href="{{ route('register') }}"
                                class="mt-2 inline-block text-sm font-medium text-[var(--livora-accent)]"
                            >
                                ایجاد حساب جدید
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>

    </section>

@endsection
