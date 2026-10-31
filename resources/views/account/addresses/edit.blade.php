@extends('layouts.app')

@section('title', 'ویرایش آدرس | SillaGallery')

@section('description', 'ویرایش نشانی ارسال سفارش در SillaGallery.')

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')
    <section class="min-h-[70vh] bg-[var(--sg-cream)] py-8 sm:py-12">
        <x-layout.container>
            <nav aria-label="مسیر صفحه" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--sg-stone)]">
                <a href="{{ route('home') }}" class="hover:text-[var(--sg-ink)]">خانه</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('account.index') }}" class="hover:text-[var(--sg-ink)]">حساب کاربری</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('account.addresses.index') }}" class="hover:text-[var(--sg-ink)]">آدرس‌ها</a>
                <span aria-hidden="true">/</span>
                <span class="text-[var(--sg-ink)]">ویرایش</span>
            </nav>

            <div class="mx-auto max-w-3xl overflow-hidden rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)]">
                <div class="border-b border-[var(--sg-border)] p-6 sm:p-8">
                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--sg-accent)]">EDIT ADDRESS</p>
                    <h1 class="mt-3 text-2xl font-semibold sm:text-3xl">ویرایش آدرس ارسال</h1>
                </div>

                <div class="p-6 sm:p-8">
                    @if($errors->any())
                        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            <ul class="list-inside list-disc space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('account.addresses.update', $address) }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="address-title" class="mb-1.5 block text-sm font-medium">عنوان آدرس (اختیاری)</label>
                            <input id="address-title" name="title" value="{{ old('title', $address->title) }}" maxlength="100" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="address-first-name" class="mb-1.5 block text-sm font-medium">نام گیرنده</label>
                                <input id="address-first-name" name="first_name" value="{{ old('first_name', $address->first_name) }}" required maxlength="100" autocomplete="given-name" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">
                            </div>
                            <div>
                                <label for="address-last-name" class="mb-1.5 block text-sm font-medium">نام خانوادگی</label>
                                <input id="address-last-name" name="last_name" value="{{ old('last_name', $address->last_name) }}" required maxlength="100" autocomplete="family-name" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">
                            </div>
                        </div>
                        <div>
                            <label for="address-phone" class="mb-1.5 block text-sm font-medium">موبایل گیرنده</label>
                            <input id="address-phone" name="phone" type="tel" inputmode="tel" dir="ltr" value="{{ old('phone', $address->phone) }}" required autocomplete="tel" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-left text-sm outline-none focus:border-[var(--sg-ink)]">
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="address-province" class="mb-1.5 block text-sm font-medium">استان</label>
                                <input id="address-province" name="province" value="{{ old('province', $address->province) }}" required maxlength="100" autocomplete="address-level1" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">
                            </div>
                            <div>
                                <label for="address-city" class="mb-1.5 block text-sm font-medium">شهر</label>
                                <input id="address-city" name="city" value="{{ old('city', $address->city) }}" required maxlength="100" autocomplete="address-level2" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">
                            </div>
                        </div>
                        <div>
                            <label for="address-address" class="mb-1.5 block text-sm font-medium">نشانی کامل</label>
                            <textarea id="address-address" name="address" rows="4" required maxlength="2000" autocomplete="street-address" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">{{ old('address', $address->address) }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="address-postal-code" class="mb-1.5 block text-sm font-medium">کد پستی ۱۰ رقمی</label>
                                <input id="address-postal-code" name="postal_code" inputmode="numeric" dir="ltr" value="{{ old('postal_code', $address->postal_code) }}" required minlength="10" maxlength="10" autocomplete="postal-code" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-left text-sm outline-none focus:border-[var(--sg-ink)]">
                            </div>
                            <div>
                                <label for="address-unit" class="mb-1.5 block text-sm font-medium">واحد (اختیاری)</label>
                                <input id="address-unit" name="unit" value="{{ old('unit', $address->unit) }}" maxlength="50" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-cream)] px-4 py-3.5 text-sm outline-none focus:border-[var(--sg-ink)]">
                            </div>
                        </div>
                        <label class="flex items-center gap-3 rounded-xl bg-[var(--sg-cream)] p-4 text-sm">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $address->is_default)) class="h-4 w-4 rounded border-[var(--sg-border)]">
                            این آدرس پیش‌فرض باشد
                        </label>
                        <div class="flex flex-col gap-3 border-t border-[var(--sg-border)] pt-5 sm:flex-row">
                            <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-[var(--sg-ink)] px-6 py-3 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]">ذخیره تغییرات</button>
                            <a href="{{ route('account.addresses.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-[var(--sg-border)] px-6 py-3 text-sm font-medium">انصراف</a>
                        </div>
                    </form>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
