@extends('admin.layouts.app')

@section('title', 'دسترسی غیرمجاز')
@section('page_title', '403')

@section('content')

    <div class="flex min-h-[60vh] items-center justify-center">

        <div class="admin-card w-full max-w-2xl p-8 text-center sm:p-12">

            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[rgba(168,93,93,0.12)] text-[var(--admin-danger)]">

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
                        d="M16.5 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m-2.25 0h13.5l-1.5 9h-10.5l-1.5-9Z"
                    />
                </svg>

            </div>

            <p class="mt-8 text-6xl font-black text-[var(--admin-accent)]">
                403
            </p>

            <h2 class="mt-4 text-xl font-bold text-[var(--admin-text)]">
                دسترسی غیرمجاز
            </h2>

            <p class="mx-auto mt-3 max-w-lg text-sm leading-8 text-[var(--admin-text-soft)]">
                شما اجازه دسترسی به این بخش را ندارید.
            </p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">

                <a
                    href="{{ route('admin.dashboard') }}"
                    class="admin-btn admin-btn-primary"
                >
                    بازگشت به داشبورد
                </a>

                <a
                    href="{{ route('home') }}"
                    class="admin-btn admin-btn-secondary"
                >
                    فروشگاه
                </a>

            </div>

        </div>

    </div>

@endsection
