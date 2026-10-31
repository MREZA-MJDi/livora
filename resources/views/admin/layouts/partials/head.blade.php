{{-- =========================================================
     META
========================================================= --}}

<meta charset="UTF-8">
<meta name="robots" content="noindex, nofollow">

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
    @yield('title', 'داشبورد مدیریت') | SillaGallery
</title>

<meta
    name="description"
    content="@yield(
        'meta_description',
        'پنل مدیریت فروشگاه SillaGallery'
    )"
/>


{{-- =========================================================
     VITE
========================================================= --}}

@vite([
'resources/css/admin.css',
])


{{-- =========================================================
     PAGE SPECIFIC
========================================================= --}}

@stack('styles')

@stack('head')
