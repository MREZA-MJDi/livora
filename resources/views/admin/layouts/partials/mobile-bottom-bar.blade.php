<nav
    class="fixed inset-x-3 bottom-3 z-[80] lg:hidden"
    aria-label="ناوبری سریع پنل مدیریت"
>
    <div class="mx-auto grid max-w-lg grid-cols-5 items-center gap-1 rounded-[1.35rem] border border-[var(--admin-border)] bg-[var(--admin-white)]/95 p-1.5 shadow-[0_16px_45px_rgba(28,27,25,.14)] backdrop-blur-xl">

        <a
            href="{{ route('admin.dashboard') }}"
            @class([
                'flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-[9px] font-medium transition',
                'bg-[var(--admin-text)] text-white shadow-sm' => request()->routeIs('admin.dashboard'),
                'text-[var(--admin-muted)] active:bg-[var(--admin-surface)]' => !request()->routeIs('admin.dashboard'),
            ])
            aria-label="داشبورد"
            @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"/>
            </svg>
            <span>داشبورد</span>
        </a>

        <a
            href="{{ route('admin.products.index') }}"
            @class([
                'flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-[9px] font-medium transition',
                'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.products.*'),
                'text-[var(--admin-muted)] active:bg-[var(--admin-surface)]' => !request()->routeIs('admin.products.*'),
            ])
            aria-label="محصولات"
            @if(request()->routeIs('admin.products.*')) aria-current="page" @endif
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-8.25-4.5-8.25 4.5m16.5 0-8.25 4.5m8.25-4.5V16.5L12 21l-8.25-4.5V7.5m0 0L12 12m0 0v9"/>
            </svg>
            <span>محصولات</span>
        </a>

        <a
            href="{{ route('admin.orders.index') }}"
            @class([
                'flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-[9px] font-medium transition',
                'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.orders.*'),
                'text-[var(--admin-muted)] active:bg-[var(--admin-surface)]' => !request()->routeIs('admin.orders.*'),
            ])
            aria-label="سفارش‌ها"
            @if(request()->routeIs('admin.orders.*')) aria-current="page" @endif
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"/>
            </svg>
            <span>سفارش‌ها</span>
        </a>

        <a
            href="{{ route('admin.customers.index') }}"
            @class([
                'flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-[9px] font-medium transition',
                'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.customers.*'),
                'text-[var(--admin-muted)] active:bg-[var(--admin-surface)]' => !request()->routeIs('admin.customers.*'),
            ])
            aria-label="مشتریان"
            @if(request()->routeIs('admin.customers.*')) aria-current="page" @endif
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <circle cx="12" cy="8" r="3.5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 20a7 7 0 0 1 14 0"/>
            </svg>
            <span>مشتریان</span>
        </a>

        <button
            type="button"
            @click="sidebarOpen = true"
            class="flex min-w-0 flex-col items-center justify-center gap-1 rounded-xl px-2 py-2.5 text-[9px] font-medium text-[var(--admin-muted)] transition active:bg-[var(--admin-surface)]"
            aria-label="منوی مدیریت"
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span>منو</span>
        </button>

    </div>
</nav>
