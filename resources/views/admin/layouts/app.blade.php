<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    @include('admin.layouts.partials.head')
</head>

<body class="min-h-screen">

<div class="admin-shell">

    {{-- Sidebar --}}
    @include('admin.layouts.partials.sidebar')

    {{-- Main Area --}}
    <div class="lg:pr-72">

        {{-- Navbar --}}
        @include('admin.layouts.partials.navbar')

        <main class="admin-main min-h-[calc(100vh-4rem)] p-4 sm:p-6 lg:p-8">

            {{-- Breadcrumbs --}}
            @include('admin.layouts.partials.breadcrumbs')

            {{-- Flash Messages --}}
            @include('admin.layouts.partials.flash-messages')

            {{-- Page Content --}}
            @yield('content')

        </main>

        {{-- Footer --}}
        @include('admin.layouts.partials.footer')

    </div>

</div>

{{-- Scripts --}}
@include('admin.layouts.partials.scripts')

@stack('scripts')

</body>
</html>
