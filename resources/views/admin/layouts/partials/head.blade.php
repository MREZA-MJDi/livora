{{-- =========================================================
     META
========================================================= --}}

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
/>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

<meta
    name="theme-color"
    content="#1c1b19"
/>


{{-- =========================================================
     SEO
========================================================= --}}

<title>
    @yield('title', 'داشبورد مدیریت') | LIVORA
</title>

<meta
    name="description"
    content="@yield(
        'meta_description',
        'پنل مدیریت فروشگاه LIVORA'
    )"
/>


{{-- =========================================================
     FONTS
========================================================= --}}

<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet"
>


{{-- =========================================================
     VITE
========================================================= --}}

@vite([
'resources/css/app.css',
'resources/css/admin.css',
'resources/js/app.js',
])


{{-- =========================================================
     PAGE SPECIFIC
========================================================= --}}

@stack('styles')

@stack('head')
