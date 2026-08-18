<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    @include('admin.layouts.partials.head')
</head>

<body class="min-h-screen">

<main class="flex min-h-screen items-center justify-center p-4 sm:p-6">
    @yield('content')
</main>

@include('admin.layouts.partials.scripts')

@stack('scripts')

</body>
</html>
