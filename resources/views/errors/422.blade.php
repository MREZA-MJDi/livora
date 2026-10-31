@extends('errors.layout')

@section('title', 'اطلاعات قابل پردازش نیست | SillaGallery')

@section('content')
<div class="wrap">
    <main class="card">
        <div class="brand">SillaGallery</div>
        <div class="code">422</div>
        <h1>اطلاعات درخواست قابل پردازش نیست.</h1>
        <p>اطلاعات واردشده را بررسی کنید و دوباره تلاش کنید.</p>
        <div class="actions">
            @if(url()->previous() !== url()->current())
                <a href="{{ url()->previous() }}" class="btn secondary">بازگشت</a>
            @endif
            <a href="{{ route('home') }}" class="btn primary">بازگشت به فروشگاه</a>
        </div>
    </main>
</div>
@endsection
