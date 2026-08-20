@extends('admin.layouts.app')

@section('title', 'صفحه پیدا نشد')
@section('page_title', '404')

@section('content')

    <div class="flex min-h-[60vh] items-center justify-center">

        <div class="admin-card w-full max-w-2xl p-8 text-center sm:p-12">

            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[rgba(176,138,99,0.10)] text-[var(--admin-accent)]">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-10 w-10"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9.75 9.75 14.25 14.25m0-4.5-4.5 4.5M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z"
                    />
                </svg>

            </div>

            <p class="mt-8 text-6xl font-black text-[var(--admin-accent)]">
                404
            </p>

            <h2 class="mt-4 text-xl font-bold text-[var(--admin-text)]">
                صفحه پیدا نشد
            </h2>

            <p class="mx-auto mt-3 max-w-lg text-sm leading-8 text-[var(--admin-text-soft)]">
                صفحه‌ای که به دنبال آن هستید وجود ندارد یا حذف شده است.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="admin-btn admin-btn-primary"
                >
                    داشبورد
                </a>

                <a
                    href="{{ route('admin.products.index') }}"
                    class="admin-btn admin-btn-secondary"
                >
                    محصولات
                </a>

            </div>

        </div>

    </div>

@endsection
