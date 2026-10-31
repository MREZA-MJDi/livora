@extends('errors.layout')

@section('title', 'درخواست‌های زیاد | SillaGallery')

@section('content')
<div class="wrap">
    <main class="card">
        <div class="brand">SillaGallery</div>
        <div class="code">429</div>
        <h1>درخواست‌های زیادی ارسال شده است.</h1>
        <p>چند لحظه صبر کنید و سپس دوباره تلاش کنید.</p>
        <div class="actions">
            @if(url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="btn secondary">بازگشت</a>
            @endif
            <a href="{{ route('home') }}" class="btn primary">بازگشت به فروشگاه</a>
        </div>
    </main>
</div>
@endsection
