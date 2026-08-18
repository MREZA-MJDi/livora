@extends('admin.layouts.app')

@section('title', 'داشبورد')

@section('page_title', 'داشبورد')

@section('meta_description', 'داشبورد مدیریت فروشگاه LIVORA')

@section('content')

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
                LIVORA / ADMIN
            </div>

            <h2 class="admin-title">
                داشبورد مدیریت
            </h2>

            <p class="admin-subtitle mt-2">
                نمای کلی پنل مدیریت فروشگاه را از اینجا مشاهده و مدیریت کنید.
            </p>
        </div>

        <div class="text-xs text-[var(--admin-muted)]">
            {{ now()->translatedFormat('l، d F Y') }}
        </div>

    </div>


    {{-- Statistics --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Products --}}
        <div class="admin-stat p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="admin-stat-label">
                        محصولات
                    </p>

                    <p class="admin-stat-value">
                        —
                    </p>
                </div>

                <div class="admin-stat-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 8.25-9-5.25-9 5.25m18 0-9 5.25m9-5.25V15l-9 5.25M3 8.25l9 5.25m-9-5.25V15l9 5.25m0-6.75V20.25"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-4 text-xs text-[var(--admin-muted)]">
                آمار پس از تکمیل بخش محصولات نمایش داده می‌شود.
            </p>

        </div>


        {{-- Customers --}}
        <div class="admin-stat p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="admin-stat-label">
                        مشتریان
                    </p>

                    <p class="admin-stat-value">
                        —
                    </p>
                </div>

                <div class="admin-stat-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0ZM18.75 8.25a2.25 2.25 0 1 1-4.5 0"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-4 text-xs text-[var(--admin-muted)]">
                آمار مشتریان بعداً به‌صورت زنده نمایش داده می‌شود.
            </p>

        </div>


        {{-- Orders --}}
        <div class="admin-stat p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="admin-stat-label">
                        سفارش‌ها
                    </p>

                    <p class="admin-stat-value">
                        —
                    </p>
                </div>

                <div class="admin-stat-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-4 text-xs text-[var(--admin-muted)]">
                وضعیت سفارش‌ها پس از تکمیل ماژول سفارش نمایش داده می‌شود.
            </p>

        </div>


        {{-- Revenue --}}
        <div class="admin-stat p-5">

            <div class="flex items-center justify-between">

                <div>
                    <p class="admin-stat-label">
                        فروش
                    </p>

                    <p class="admin-stat-value">
                        —
                    </p>
                </div>

                <div class="admin-stat-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-6 w-6"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 6v12m4.5-9.75c0-1.243-1.007-2.25-2.25-2.25h-4.5A2.25 2.25 0 0 0 7.5 8.25v.75a2.25 2.25 0 0 0 2.25 2.25h4.5A2.25 2.25 0 0 1 16.5 13.5v.75a2.25 2.25 0 0 1-2.25 2.25h-4.5a2.25 2.25 0 0 1-2.25-2.25"
                        />
                    </svg>
                </div>

            </div>

            <p class="mt-4 text-xs text-[var(--admin-muted)]">
                مجموع فروش پس از تکمیل سیستم پرداخت محاسبه خواهد شد.
            </p>

        </div>

    </div>


    {{-- Quick Actions --}}
    <div class="mb-8">

        <div class="mb-4">
            <h3 class="text-base font-bold text-[var(--admin-text)]">
                دسترسی سریع
            </h3>

            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                عملیات‌های پرکاربرد مدیریت فروشگاه
            </p>
        </div>


        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Add Product --}}
            <a
                href="{{ route('admin.products.create') }}"
                class="admin-card admin-card-hover group p-5"
            >
                <div class="flex items-center gap-4">

                    <div class="admin-stat-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 5.25v13.5M5.25 12h13.5"
                            />
                        </svg>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                            افزودن محصول
                        </h4>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            ثبت محصول جدید
                        </p>
                    </div>

                </div>
            </a>


            {{-- Add Category --}}
            <a
                href="{{ route('admin.categories.create') }}"
                class="admin-card admin-card-hover group p-5"
            >
                <div class="flex items-center gap-4">

                    <div class="admin-stat-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 5.25v13.5M5.25 12h13.5"
                            />
                        </svg>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                            افزودن دسته‌بندی
                        </h4>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            ساخت دسته‌بندی جدید
                        </p>
                    </div>

                </div>
            </a>


            {{-- Orders --}}
            <a
                href="{{ route('admin.orders.index') }}"
                class="admin-card admin-card-hover group p-5"
            >
                <div class="flex items-center gap-4">

                    <div class="admin-stat-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5"
                            />
                        </svg>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                            سفارش‌ها
                        </h4>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            مشاهده سفارش‌های ثبت‌شده
                        </p>
                    </div>

                </div>
            </a>


            {{-- Customers --}}
            <a
                href="{{ route('admin.customers.index') }}"
                class="admin-card admin-card-hover group p-5"
            >
                <div class="flex items-center gap-4">

                    <div class="admin-stat-icon">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0Z"
                            />
                        </svg>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                            مشتریان
                        </h4>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            مدیریت مشتریان فروشگاه
                        </p>
                    </div>

                </div>
            </a>

        </div>

    </div>


    {{-- Main Grid --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Welcome --}}
        <section class="admin-card overflow-hidden xl:col-span-2">

            <div class="relative p-6 sm:p-8">

                <div
                    class="absolute -left-16 -top-16 h-40 w-40 rounded-full bg-[rgba(176,138,99,0.06)]"
                ></div>

                <div class="relative">

                    <span class="admin-badge admin-badge-neutral">
                        LIVORA ADMIN
                    </span>

                    <h3 class="mt-5 text-xl font-bold text-[var(--admin-text)]">
                        به پنل مدیریت LIVORA خوش آمدید.
                    </h3>

                    <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--admin-text-soft)]">
                        از این بخش می‌توانید محصولات، دسته‌بندی‌ها، سفارش‌ها،
                        مشتریان و سایر بخش‌های فروشگاه را مدیریت کنید.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">

                        <a
                            href="{{ route('admin.products.index') }}"
                            class="admin-btn admin-btn-primary"
                        >
                            مدیریت محصولات
                        </a>

                        <a
                            href="{{ route('admin.categories.index') }}"
                            class="admin-btn admin-btn-secondary"
                        >
                            مدیریت دسته‌بندی‌ها
                        </a>

                    </div>

                </div>

            </div>

        </section>


        {{-- System Status --}}
        <section class="admin-card p-6">

            <div class="mb-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    وضعیت سیستم
                </h3>

                <p class="mt-1 text-xs text-[var(--admin-muted)]">
                    وضعیت بخش‌های اصلی پنل
                </p>

            </div>


            <div class="space-y-4">

                {{-- Authentication --}}
                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--admin-success)]"></span>

                        <span class="text-sm text-[var(--admin-text-soft)]">
                            احراز هویت
                        </span>

                    </div>

                    <span class="admin-badge admin-badge-success">
                        فعال
                    </span>

                </div>


                {{-- Admin Area --}}
                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--admin-success)]"></span>

                        <span class="text-sm text-[var(--admin-text-soft)]">
                            پنل مدیریت
                        </span>

                    </div>

                    <span class="admin-badge admin-badge-success">
                        فعال
                    </span>

                </div>


                {{-- Product Management --}}
                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--admin-warning)]"></span>

                        <span class="text-sm text-[var(--admin-text-soft)]">
                            مدیریت محصولات
                        </span>

                    </div>

                    <span class="admin-badge admin-badge-warning">
                        در حال تکمیل
                    </span>

                </div>


                {{-- Orders --}}
                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-3">

                        <span class="h-2.5 w-2.5 rounded-full bg-[var(--admin-warning)]"></span>

                        <span class="text-sm text-[var(--admin-text-soft)]">
                            سفارش‌ها
                        </span>

                    </div>

                    <span class="admin-badge admin-badge-warning">
                        در حال تکمیل
                    </span>

                </div>

            </div>

        </section>

    </div>

@endsection
