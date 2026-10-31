@extends('errors.layout')

@section('title', 'سرویس موقتاً در دسترس نیست | SillaGallery')

@section('content')
<div class="wrap">
    <main class="card">
        <div class="brand">SillaGallery</div>
        <div class="code danger">503</div>
        <h1>سرویس موقتاً در دسترس نیست.</h1>
        <p>فروشگاه در حال به‌روزرسانی یا انجام عملیات موقت است. کمی بعد دوباره امتحان کنید.</p>
        <div class="actions">
            <a href="{{ route('home') }}" class="btn primary">بازگشت به فروشگاه</a>
        </div>
    </main>
</div>
@endsection
