<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
    class="h-full"
>
<head>

    @include(
        'admin.layouts.partials.head'
    )

</head>
<body
    class="min-h-full bg-[var(--admin-bg)] text-[var(--admin-text)] antialiased"
    data-admin-session-timeout="{{ (int) config('admin.session_timeout_minutes', 30) }}"
    data-admin-path="{{ config('admin.path', 'admin') }}"
>
<div
    x-data="{
        sidebarOpen: false,
        userMenuOpen: false,
        notificationOpen: false,
        sessionExpired: false,
        sessionLoginUrl: '/login'
    }"
    x-on:admin:session-expired.window="sessionExpired = true; sessionLoginUrl = $event.detail?.login_url || sessionLoginUrl"
    class="admin-shell min-h-screen"
>

    {{-- =========================================================
         MOBILE SIDEBAR BACKDROP
    ========================================================== --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        data-admin-sidebar-backdrop
        class="fixed inset-0 z-40 bg-black/35 backdrop-blur-sm lg:hidden"
        @click="sidebarOpen = false"
    ></div>


    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}
    @include('admin.layouts.partials.sidebar')


    {{-- =========================================================
         MAIN AREA
    ========================================================== --}}
    <div class="flex min-h-screen flex-col lg:mr-[260px]">

        {{-- =====================================================
             NAVBAR
        ====================================================== --}}
        @include('admin.layouts.partials.navbar')


        {{-- =====================================================
             CONTENT
        ====================================================== --}}
        <main
            class="admin-main flex-1 px-4 py-5 sm:px-6 sm:py-6 lg:px-8 lg:py-8"
        >

            <div
                class="admin-content admin-page-enter"
            >

                {{-- Breadcrumbs --}}
                @includeIf(
                    'admin.layouts.partials.breadcrumbs'
                )


                {{-- Flash Messages --}}
                @includeIf(
                    'admin.layouts.partials.flash-messages'
                )


                {{-- Page Content --}}
                @yield('content')

            </div>

        </main>


        {{-- =====================================================
             FOOTER
        ====================================================== --}}
        @includeIf(
            'admin.layouts.partials.footer'
        )

    </div>


    {{-- =========================================================
         GLOBAL LOADING
    ========================================================== --}}
    <div
        x-data="{ loading: false }"
        x-on:livewire:navigating.window="loading = true"
        x-on:livewire:navigated.window="loading = false"
    >

        <div
            x-show="loading"
            x-cloak
            class="fixed inset-x-0 top-0 z-[200] h-0.5 bg-[var(--admin-accent)]"
        ></div>

    </div>


    {{-- Admin session expired overlay (AJAX and dashboard widgets) --}}
    <div
        x-show="sessionExpired"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[500] flex items-center justify-center bg-black/55 p-4 backdrop-blur-sm"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="admin-session-expired-title"
    >
        <section class="w-full max-w-md rounded-3xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-6 shadow-2xl">
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-lg text-amber-800">⌛</div>
            <h2 id="admin-session-expired-title" class="mt-5 text-lg font-bold">نشست مدیریت به پایان رسیده است</h2>
            <p class="mt-3 text-sm leading-7 text-[var(--admin-muted)]">
                برای حفظ امنیت پنل، لازم است دوباره وارد شوید. پس از ورود، به صفحه مدیریت قبلی بازگردانده می‌شوید.
            </p>
            <a
                :href="sessionLoginUrl"
                class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-2xl bg-[var(--admin-text)] px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"
            >
                ورود مجدد به پنل
            </a>
        </section>
    </div>

</div>


{{-- =========================================================
     SCRIPTS
========================================================== --}}
{{-- Renew the admin session only after recent real interaction with the dashboard. --}}
<script>
(() => {
    const timeoutMinutes = Number(document.body.dataset.adminSessionTimeout || 30);
    const heartbeatEveryMs = Math.max(60_000, Math.min(300_000, Math.floor(timeoutMinutes * 60_000 / 3)));
    const recentInteractionWindowMs = heartbeatEveryMs * 2;
    const heartbeatUrl = @json(route('admin.session.heartbeat'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!csrfToken || !heartbeatUrl) return;

    let lastInteractionAt = Date.now();
    let lastHeartbeatAt = Date.now();
    let heartbeatInFlight = false;

    const registerInteraction = () => {
        lastInteractionAt = Date.now();
    };

    ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach((eventName) => {
        window.addEventListener(eventName, registerInteraction, { passive: true });
    });

    window.setInterval(async () => {
        const now = Date.now();
        const hasRecentInteraction = now - lastInteractionAt < recentInteractionWindowMs;
        const heartbeatDue = now - lastHeartbeatAt >= heartbeatEveryMs;

        if (!hasRecentInteraction || !heartbeatDue || heartbeatInFlight) return;

        heartbeatInFlight = true;

        try {
            const response = await window.fetch(heartbeatUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });

            if (response.ok) lastHeartbeatAt = Date.now();
        } catch (_) {
            // Failed heartbeats never extend the server-side session.
        } finally {
            heartbeatInFlight = false;
        }
    }, 30_000);
})();
</script>

@stack('scripts')

@includeIf(
    'admin.layouts.partials.scripts'
)

</body>
</html>
