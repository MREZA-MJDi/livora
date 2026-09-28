@extends('errors.layout')

@section('title', 'درخواست‌های زیاد | SilaGallery')

@section('content')
<div class="wrap">
    <main class="card">
        <div class="brand">SilaGallery</div>
        <div class="code">429</div>
        <h1>کمی آرام‌تر :)</h1>
        <p>درخواست‌های زیادی در مدت کوتاه ارسال شده است. چند لحظه بعد دوباره تلاش کنید.</p>
        <div class="actions">
            @if(url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="btn secondary">بازگشت</a>
            @endif
            <a href="{{ route('home') }}" class="btn primary">بازگشت به فروشگاه</a>
            @auth
                @if(auth()->user()->isCustomer() && \Illuminate\Support\Facades\Route::has('account.index'))
                    <a href="{{ route('account.index') }}" class="btn secondary">حساب کاربری</a>
                @elseif(auth()->user()->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="btn secondary">پنل مدیریت</a>
                @endif
            @endauth
        </div>
    </main>
</div>
@endsection
