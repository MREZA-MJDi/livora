@extends('admin.layouts.app')

@section('title', 'افزودن رسانه')

@section('content')

    <div class="mx-auto max-w-4xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center gap-4">

            <a
                href="{{ route('admin.media.index') }}"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] text-[var(--admin-text-soft)] transition hover:bg-[var(--admin-bg)] hover:text-[var(--admin-text)]"
                title="بازگشت"
            >

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.7"
                    stroke="currentColor"
                    class="h-5 w-5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 19.5 8.25 12l7.5-7.5"
                    />
                </svg>

            </a>

            <div>

                <h1 class="text-xl font-semibold text-[var(--admin-text)]">
                    افزودن رسانه
                </h1>

                <p class="mt-1 text-sm text-[var(--admin-text-soft)]">
                    یک تصویر یا فایل جدید به کتابخانه رسانه اضافه کنید.
                </p>

            </div>

        </div>


        {{-- Form Card --}}
        <div class="rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-surface)] p-5 shadow-sm sm:p-6">

            @include('admin.media.partials.upload-form')

        </div>

    </div>

@endsection
