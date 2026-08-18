<aside
    id="admin-sidebar"
    class="admin-sidebar fixed inset-y-0 right-0 z-50 flex w-72 translate-x-full flex-col border-l transition-transform duration-300 lg:translate-x-0"
>

    {{-- Brand --}}
    <div class="flex h-16 shrink-0 items-center border-b border-[var(--admin-border)] px-6">

        <a
            href="{{ route('admin.dashboard') }}"
            class="group"
        >
            <span class="admin-sidebar-brand text-xl font-semibold transition-opacity group-hover:opacity-75">
                LIVORA
            </span>

            <span class="mt-0.5 block text-[10px] tracking-[0.2em] text-[var(--admin-muted)]">
                ADMIN PANEL
            </span>
        </a>

    </div>


    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-4 py-5">

        {{-- Main --}}
        <div>

            <p class="mb-3 px-3 text-[10px] font-semibold tracking-[0.12em] text-[var(--admin-muted)]">
                مدیریت
            </p>


            {{-- Dashboard --}}
            <a
                href="{{ route('admin.dashboard') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.dashboard'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"
                    />
                </svg>

                <span>داشبورد</span>
            </a>


            {{-- Products --}}
            <a
                href="{{ route('admin.products.index') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.products.*'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="m21 8.25-9-5.25-9 5.25m18 0-9 5.25m9-5.25V15l-9 5.25M3 8.25l9 5.25m-9-5.25V15l9 5.25m0-6.75V20.25"
                    />
                </svg>

                <span>محصولات</span>
            </a>


            {{-- Categories --}}
            <a
                href="{{ route('admin.categories.index') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.categories.*'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9.568 3.964a.75.75 0 0 1 .864 0l7.5 5.25a.75.75 0 0 1 .318.614v8.422a.75.75 0 0 1-.75.75H6.5a.75.75 0 0 1-.75-.75V9.828a.75.75 0 0 1 .318-.614l7.5-5.25Z"
                    />
                </svg>

                <span>دسته‌بندی‌ها</span>
            </a>


            {{-- Orders --}}
            <a
                href="{{ route('admin.orders.index') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.orders.*'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"
                    />
                </svg>

                <span>سفارش‌ها</span>
            </a>


            {{-- Customers --}}
            <a
                href="{{ route('admin.customers.index') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.customers.*'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0Zm10.5-6.75a3 3 0 1 1-6 0"
                    />
                </svg>

                <span>مشتریان</span>
            </a>

        </div>


        {{-- Catalog --}}
        <div class="mt-7">

            <p class="mb-3 px-3 text-[10px] font-semibold tracking-[0.12em] text-[var(--admin-muted)]">
                کاتالوگ
            </p>


            {{-- Product Images --}}
            <a
                href="{{ route('admin.product-images.index') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.product-images.*'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 5.25A1.5 1.5 0 0 1 5.25 3.75h13.5a1.5 1.5 0 0 1 1.5 1.5v13.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V5.25Zm3.75 9 2.25-2.25 2.25 2.25 3-3 3 3"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8.25 8.25h.008v.008H8.25V8.25Z"
                    />
                </svg>

                <span>تصاویر محصولات</span>
            </a>


            {{-- Product Variants --}}
            <a
                href="{{ route('admin.product-variants.index') }}"
                @class([
                    'admin-sidebar-link mb-1',
                    'active' => request()->routeIs('admin.product-variants.*'),
                ])
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"
                    />
                </svg>

                <span>تنوع محصولات</span>
            </a>

        </div>

    </nav>


    {{-- Bottom --}}
    <div class="shrink-0 border-t border-[var(--admin-border)] p-4">

        <form
            action="{{ route('logout') }}"
            method="POST"
        >
            @csrf

            <button
                type="submit"
                class="admin-sidebar-link w-full text-[var(--admin-danger)]"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-5 w-5 shrink-0"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-3H10.5m0 0 3-3m-3 3 3 3"
                    />
                </svg>

                <span>خروج از حساب</span>
            </button>

        </form>

    </div>

</aside>


{{-- Mobile Overlay --}}
<div
    id="admin-sidebar-overlay"
    class="fixed inset-0 z-40 hidden bg-black/60 lg:hidden"
></div>
