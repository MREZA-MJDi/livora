<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'سیلاگالری')
    </title>

    <meta
        name="description"
        content="@yield('description', 'سیلاگالری — مبلمان و لوازم خانه')"
    >

    @hasSection('keywords')
        <meta
            name="keywords"
            content="@yield('keywords')"
        >
    @endif

    @hasSection('canonical')
        <link
            rel="canonical"
            href="@yield('canonical')"
        >
    @endif

    @stack('seo')

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body class="min-h-screen antialiased">

<div
    id="app"
    x-data="{
        mobileOpen: false,
        searchOpen: false,
        filterOpen: false,
        searchReturnFocus: null,
        openSearch(trigger) {
            const openedFromMenu = trigger?.closest?.('[role="dialog"][aria-label="منوی موبایل"]');
            this.searchReturnFocus = openedFromMenu
                ? document.querySelector('.livora-mobile-nav button[aria-label="جستجو"]')
                : (trigger || document.activeElement);
            this.mobileOpen = false;
            this.searchOpen = true;
            this.$nextTick(() => {
                document.getElementById('global-search')?.focus({ preventScroll: true });
            });
        },
        closeSearch() {
            this.searchOpen = false;
            this.$nextTick(() => {
                const target = this.searchReturnFocus;
                if (target?.isConnected && target.offsetParent !== null) {
                    target.focus?.({ preventScroll: true });
                } else {
                    document.querySelector('.livora-mobile-nav button[aria-label="جستجو"]')?.focus({ preventScroll: true });
                }
            });
        }
    }"
    class="min-h-screen pb-24 lg:pb-0"
>

    <x-layout.navbar />

    <x-layout.mobile-menu />

    <x-layout.search-overlay />

    <x-shop.filter-drawer />

    <main class="storefront-content">
        @yield('content')
    </main>

    <x-layout.footer />

    <x-layout.mobile-bottom-bar />

</div>

@stack('scripts')

</body>
</html>
