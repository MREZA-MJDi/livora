@extends('errors.layout')

@section('title', 'سرویس موقتاً در دسترس نیست | SilaGallery')

@section('content')
<div class="wrap">
    <main class="card">
        <div class="brand">SilaGallery</div>
        <div class="code danger">503</div>
        <h1>سرویس فعلاً در دسترس نیست.</h1>
        <p>سیستم احتمالاً در حال بروزرسانی یا انجام عملیات موقت است. کمی بعد دوباره امتحان کنید.</p>
        <div class="actions">
            @if(url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="btn secondary">بازگشت</a>
            @endif
            <a href="{{ route('home') }}" class="btn primary">بازگشت به فروشگاه</a>
            @auth
                @if(auth()->user()->isCustomer() && IlluminateSupportFacadesRoute::has('account.index'))
                    <a href="{{ route('account.index') }}" class="btn secondary">حساب کاربری</a>
                @elseif(auth()->user()->isAdmin() && IlluminateSupportFacadesRoute::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="btn secondary">پنل مدیریت</a>
                @endif
            @endauth
        </div>
    </main>
</div>
@endsection
