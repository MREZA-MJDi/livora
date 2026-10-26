@extends('errors.layout')

@section('title', 'صفحه پیدا نشد | SilaGallery')

@section('content')
<div class="wrap">
    <main class="card">
        <div class="brand">SilaGallery</div>
        <div class="code ">404</div>
        <h1>این صفحه را پیدا نکردیم.</h1>
        <p>ممکن است نشانی اشتباه باشد، یا محصول/صفحه تغییر کرده یا حذف شده باشد.</p>
        <div class="actions">
            @if(url()->previous() !== url()->current() && parse_url(url()->previous(), PHP_URL_HOST) === parse_url(url('/'), PHP_URL_HOST))
                <a href="{{ url()->previous() }}" class="btn secondary">بازگشت</a>
            @endif
            <a href="{{ route('home') }}" class="btn primary">بازگشت به فروشگاه</a>
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('account.index') }}" class="btn secondary">حساب کاربری</a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn secondary">پنل مدیریت</a>
                @endif
            @endauth
        </div>
    </main>
</div>
@endsection
