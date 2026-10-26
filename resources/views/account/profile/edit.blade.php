@extends('layouts.app')

@section('title', 'ویرایش پروفایل | SilaGallery')

@section('description', 'مدیریت اطلاعات حساب کاربری در SilaGallery.')

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
                <span class="text-[var(--livora-ink)]">ویرایش پروفایل</span>
            </nav>

            <div class="mx-auto max-w-3xl overflow-hidden rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)]">
                <div class="border-b border-[var(--livora-border)] p-6 sm:p-8">
                    <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">ACCOUNT SETTINGS</p>
                    <h1 class="mt-3 text-2xl font-semibold sm:text-3xl">اطلاعات حساب</h1>
                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">نام و ایمیلی را ثبت کنید که برای پیگیری سفارش‌ها از آن استفاده می‌کنید.</p>
                </div>

                <div class="p-6 sm:p-8">
                    @if(session('success'))
                        <div role="status" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            <p class="font-semibold">اطلاعات واردشده را بررسی کنید.</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="profile-name" class="mb-2 block text-sm font-medium">نام و نام خانوادگی</label>
                            <input id="profile-name" name="name" type="text" value="{{ old('name', $user->name) }}" required minlength="2" maxlength="150" autocomplete="name" class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-4 py-3.5 text-sm outline-none transition focus:border-[var(--livora-ink)] @error('name') border-red-500 @enderror" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="profile-name-error">
                            @error('name')
                                <p id="profile-name-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="profile-email" class="mb-2 block text-sm font-medium">ایمیل</label>
                            <input id="profile-email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email" dir="ltr" class="w-full rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-cream)] px-4 py-3.5 text-left text-sm outline-none transition focus:border-[var(--livora-ink)] @error('email') border-red-500 @enderror" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="profile-email-error">
                            @error('email')
                                <p id="profile-email-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-col gap-3 border-t border-[var(--livora-border)] pt-6 sm:flex-row">
                            <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-6 py-3 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]">ذخیره تغییرات</button>
                            <a href="{{ route('account.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-[var(--livora-border)] px-6 py-3 text-sm font-medium transition hover:border-[var(--livora-ink)]">بازگشت به حساب</a>
                        </div>
                    </form>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
