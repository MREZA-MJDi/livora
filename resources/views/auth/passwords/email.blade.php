@extends('layouts.app')

@section('title', 'بازنشانی رمز عبور | SillaGallery')
@section('description', 'درخواست لینک بازنشانی رمز عبور حساب SillaGallery.')

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')
    <section class="min-h-[calc(100vh-80px)] bg-[var(--sg-cream)]">
        <x-layout.container>
            <div class="flex min-h-[calc(100vh-80px)] items-center justify-center py-12">
                <div class="w-full max-w-md">
                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--sg-accent)]">ACCOUNT RECOVERY</p>
                        <h1 class="mt-3 text-3xl font-semibold text-[var(--sg-ink)]">بازنشانی رمز عبور</h1>
                        <p class="mt-3 text-sm leading-7 text-[var(--sg-stone)]">ایمیلی را وارد کنید که با آن در SillaGallery ثبت‌نام کرده‌اید.</p>
                    </div>

                    @if(session('status'))
                        <div role="status" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                            <ul class="space-y-1 text-xs leading-6 text-red-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-8 rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] p-6 sm:p-8">
                        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
                            @csrf
                            <div>
                                <label for="recovery-email" class="mb-2 block text-sm font-medium text-[var(--sg-ink)]">ایمیل</label>
                                <input id="recovery-email" name="email" type="email" autocomplete="email" inputmode="email" dir="ltr" value="{{ old('email') }}" required maxlength="255" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="recovery-email-error" class="w-full rounded-xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-4 py-3 text-left text-sm text-[var(--sg-ink)] outline-none transition focus:border-[var(--sg-accent)] @error('email') border-red-400 @enderror">
                                @error('email')
                                    <p id="recovery-email-error" class="mt-2 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-[var(--sg-ink)] px-5 py-3 text-sm font-medium text-white transition hover:bg-[var(--sg-accent)]">ارسال لینک بازنشانی</button>
                        </form>

                        <div class="mt-6 border-t border-[var(--sg-border)] pt-5 text-center">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-[var(--sg-accent)]">بازگشت به ورود</a>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
