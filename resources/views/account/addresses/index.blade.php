@extends('layouts.app')

@section('title', 'آدرس‌های من | SilaGallery')

@section('description', 'مدیریت نشانی‌های ارسال سفارش در SilaGallery.')

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')
    <section class="min-h-[70vh] bg-[var(--livora-cream)] py-8 sm:py-12">
        <x-layout.container>
            <nav aria-label="مسیر صفحه" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--livora-stone)]">
                <a href="{{ route('home') }}" class="hover:text-[var(--livora-ink)]">خانه</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('account.index') }}" class="hover:text-[var(--livora-ink)]">حساب کاربری</a>
                <span aria-hidden="true">/</span>
                <span class="text-[var(--livora-ink)]">آدرس‌ها</span>
            </nav>

            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">DELIVERY ADDRESSES</p>
                    <h1 class="mt-3 text-3xl font-semibold sm:text-4xl">آدرس‌های ارسال</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--livora-stone)]">نشانی‌های ذخیره‌شده را مدیریت کنید و یک آدرس را برای سفارش‌های بعدی پیش‌فرض قرار دهید.</p>
                </div>
                <a href="{{ route('account.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-5 text-sm font-medium">بازگشت به حساب</a>
            </div>

            @if(session('success'))
                <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-7 text-red-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    <p class="font-semibold">لطفاً موارد زیر را اصلاح کنید.</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,.9fr)]">
                <section class="space-y-4" aria-labelledby="saved-addresses-heading">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="saved-addresses-heading" class="text-lg font-semibold">آدرس‌های ذخیره‌شده</h2>
                        <span class="rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-1.5 text-xs text-[var(--livora-stone)]">{{ number_format($addresses->total()) }} آدرس</span>
                    </div>

                    @forelse($addresses as $address)
                        <article class="rounded-[1.6rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-semibold">{{ $address->title ?: $address->full_name }}</h3>
                                        @if($address->is_default)
                                            <span class="rounded-full bg-[var(--livora-surface)] px-3 py-1 text-[10px] font-semibold text-[var(--livora-accent)]">پیش‌فرض</span>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">{{ $address->full_name }} — {{ $address->phone }}</p>
                                    <p class="mt-1 break-words text-sm leading-7 text-[var(--livora-ink)]">{{ $address->full_address }}</p>
                                    <p class="mt-2 text-xs text-[var(--livora-stone)]">کد پستی: <span dir="ltr">{{ $address->postal_code }}</span>@if($address->unit) — واحد {{ $address->unit }}@endif</p>
                                </div>
                                <div class="flex shrink-0 flex-wrap gap-2">
                                    <a href="{{ route('account.addresses.edit', $address) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--livora-border)] px-3 text-xs font-medium transition hover:border-[var(--livora-ink)]">ویرایش</a>
                                    @unless($address->is_default)
                                        <form action="{{ route('account.addresses.default', $address) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--livora-border)] px-3 text-xs font-medium transition hover:border-[var(--livora-ink)]">پیش‌فرض کردن</button>
                                        </form>
                                    @endunless
                                </div>
                            </div>

                            <div class="mt-5 flex justify-end border-t border-[var(--livora-border)] pt-4">
                                <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" onsubmit="return confirm('این آدرس حذف شود؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="min-h-10 rounded-xl px-3 text-xs font-medium text-red-600 transition hover:bg-red-50">حذف آدرس</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-[1.6rem] border border-dashed border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-12 text-center">
                            <p class="text-base font-semibold">هنوز آدرسی ذخیره نشده است.</p>
                            <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">فرم کنار صفحه را پر کنید تا اولین نشانی ارسال شما ثبت شود.</p>
                        </div>
                    @endforelse

                    @if($addresses->hasPages())
                        <div class="pt-2">
                            {{ $addresses->links() }}
                        </div>
                    @endif
                </section>

                <section class="h-fit rounded-[1.6rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-5 sm:p-6" aria-labelledby="add-address-heading">
                    <h2 id="add-address-heading" class="text-lg font-semibold">افزودن آدرس جدید</h2>
                    <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">اطلاعات گیرنده و نشانی را وارد کنید.</p>

                    <form action="{{ route('account.addresses.store') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label for="new-address-title" class="mb-1.5 block text-xs font-medium">عنوان آدرس (اختیاری)</label>
                            <input id="new-address-title" name="title" value="{{ old('title') }}" maxlength="100" placeholder="مثلاً خانه یا محل کار" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="new-address-first-name" class="mb-1.5 block text-xs font-medium">نام گیرنده</label>
                                <input id="new-address-first-name" name="first_name" value="{{ old('first_name') }}" required maxlength="100" autocomplete="given-name" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">
                            </div>
                            <div>
                                <label for="new-address-last-name" class="mb-1.5 block text-xs font-medium">نام خانوادگی</label>
                                <input id="new-address-last-name" name="last_name" value="{{ old('last_name') }}" required maxlength="100" autocomplete="family-name" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">
                            </div>
                        </div>
                        <div>
                            <label for="new-address-phone" class="mb-1.5 block text-xs font-medium">موبایل گیرنده</label>
                            <input id="new-address-phone" name="phone" type="tel" inputmode="tel" dir="ltr" value="{{ old('phone') }}" required autocomplete="tel" placeholder="09123456789" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-left text-sm outline-none focus:border-[var(--livora-ink)]">
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="new-address-province" class="mb-1.5 block text-xs font-medium">استان</label>
                                <input id="new-address-province" name="province" value="{{ old('province') }}" required maxlength="100" autocomplete="address-level1" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">
                            </div>
                            <div>
                                <label for="new-address-city" class="mb-1.5 block text-xs font-medium">شهر</label>
                                <input id="new-address-city" name="city" value="{{ old('city') }}" required maxlength="100" autocomplete="address-level2" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">
                            </div>
                        </div>
                        <div>
                            <label for="new-address-address" class="mb-1.5 block text-xs font-medium">نشانی کامل</label>
                            <textarea id="new-address-address" name="address" rows="3" required maxlength="2000" autocomplete="street-address" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">{{ old('address') }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="new-address-postal-code" class="mb-1.5 block text-xs font-medium">کد پستی ۱۰ رقمی</label>
                                <input id="new-address-postal-code" name="postal_code" inputmode="numeric" dir="ltr" value="{{ old('postal_code') }}" required minlength="10" maxlength="10" autocomplete="postal-code" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-left text-sm outline-none focus:border-[var(--livora-ink)]">
                            </div>
                            <div>
                                <label for="new-address-unit" class="mb-1.5 block text-xs font-medium">واحد (اختیاری)</label>
                                <input id="new-address-unit" name="unit" value="{{ old('unit') }}" maxlength="50" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-3.5 py-3 text-sm outline-none focus:border-[var(--livora-ink)]">
                            </div>
                        </div>
                        <label class="flex items-center gap-3 rounded-xl bg-[var(--livora-cream)] p-3.5 text-sm">
                            <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="h-4 w-4 rounded border-[var(--livora-border)]">
                            این آدرس پیش‌فرض باشد
                        </label>
                        <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]">ذخیره آدرس</button>
                    </form>
                </section>
            </div>
        </x-layout.container>
    </section>
@endsection
