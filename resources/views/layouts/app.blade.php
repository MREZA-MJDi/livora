<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="@yield('description', 'LIVORA — Furniture & Living')"
    >

    <title>
        @yield('title', 'LIVORA')
    </title>

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

    {{-- ==================================================
        GLOBAL NAVIGATION
    =================================================== --}}
    <x-layout.navbar />


    {{-- ==================================================
        MOBILE NAVIGATION
    =================================================== --}}
    <x-layout.mobile-menu />


    {{-- ==================================================
        SEARCH OVERLAY
    =================================================== --}}
    <x-layout.search-overlay />


    {{-- ==================================================
        SHOP FILTER DRAWER
    =================================================== --}}
    <x-shop.filter-drawer />


    {{-- ==================================================
        MAIN CONTENT
    =================================================== --}}
    <main>
        @yield('content')
    </main>


    {{-- ==================================================
        GLOBAL FOOTER
    =================================================== --}}
    <x-layout.footer />

</div>


@stack('scripts')

</body>

</html>
