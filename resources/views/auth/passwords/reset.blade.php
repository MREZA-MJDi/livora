@extends('layouts.app')

@section('title', 'تعیین رمز عبور جدید | SilaGallery')
@section('description', 'تعیین رمز عبور جدید برای حساب SilaGallery.')

@push('seo')
    <meta name="robots" content="noindex,nofollow,noarchive">
@endpush

@section('content')
    <section class="min-h-[calc(100vh-80px)] bg-[var(--livora-cream)]">
        <x-layout.container>
            <div class="flex min-h-[calc(100vh-80px)] items-center justify-center py-12">
                <div class="w-full max-w-md">
                    <div class="text-center">
                        <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">SECURE YOUR ACCOUNT</p>
                        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)]">رمز عبور جدید</h1>
                        <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">رمز جدید باید حداقل ۸ کاراکتر و شامل حرف بزرگ، حرف کوچک و عدد باشد.</p>
                    </div>

                    @if($errors->any())
                        <div role="alert" class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4">
                            <ul class="space-y-1 text-xs leading-6 text-red-700">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-8 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">
                        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div>
                                <label for="reset-email" class="mb-2 block text-sm font-medium text-[var(--livora-ink)]">ایمیل</label>
                                <input id="reset-email" name="email" type="email" autocomplete="email" inputmode="email" dir="ltr" value="{{ old('email', $email) }}" required maxlength="255" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-left text-sm outline-none focus:border-[var(--livora-accent)]">
                            </div>

                            <div>
                                <label for="reset-password" class="mb-2 block text-sm font-medium text-[var(--livora-ink)]">رمز عبور جدید</label>
                                <input id="reset-password" name="password" type="password" autocomplete="new-password" required minlength="8" maxlength="255" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm outline-none focus:border-[var(--livora-accent)]">
                            </div>

                            <div>
                                <label for="reset-password-confirmation" class="mb-2 block text-sm font-medium text-[var(--livora-ink)]">تکرار رمز عبور</label>
                                <input id="reset-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" maxlength="255" class="w-full rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm outline-none focus:border-[var(--livora-accent)]">
                            </div>

                            <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]">تغییر رمز عبور</button>
                        </form>

                        <div class="mt-6 border-t border-[var(--livora-border)] pt-5 text-center">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-[var(--livora-accent)]">بازگشت به ورود</a>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
