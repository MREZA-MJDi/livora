<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'LIVORA')
    </title>

    <meta
        name="description"
        content="@yield('description', 'LIVORA — Furniture & Living')"
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
        filterOpen: false
    }"
    class="min-h-screen"
>

    <x-layout.navbar />

    <x-layout.mobile-menu />

    <x-layout.search-overlay />

    <x-shop.filter-drawer />

    <main>
        @yield('content')
    </main>

    <x-layout.footer />

</div>

@stack('scripts')

</body>
</html>
