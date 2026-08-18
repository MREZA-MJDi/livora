<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="csrf-token"
    content="{{ csrf_token() }}"
>

<meta
    name="description"
    content="@yield('meta_description', 'Livora Admin Panel')"
>

<title>
    @hasSection('title')
        @yield('title') | {{ config('app.name', 'Livora') }}
    @else
        {{ config('app.name', 'Livora') }} Admin
    @endif
</title>

{{-- Favicon --}}
<link
    rel="icon"
    type="image/png"
    href="{{ asset('favicon.png') }}"
>

{{-- Vite --}}
@vite([
'resources/css/admin.css',
'resources/js/app.js',
])

@stack('head')
