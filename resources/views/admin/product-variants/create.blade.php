@extends('admin.layouts.app')

@section('title', 'افزودن تنوع')
@section('page_title', 'افزودن تنوع محصول')

@section('content')
    <div class="mx-auto mb-6 w-full max-w-5xl">
        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">محصولات / تنوع‌ها / افزودن</div>
        <h2 class="admin-title">افزودن گزینه جدید</h2>
        <p class="admin-subtitle mt-2">مثلاً برای مبل آریا، اندازه «سه نفره» را با قیمت و موجودی خودش اضافه کن.</p>
    </div>

    <form action="{{ route('admin.product-variants.store') }}" method="POST" enctype="multipart/form-data">
        @include('admin.product-variants.partials.form', [
            'products' => $products,
            'selectedProductId' => $selectedProductId ?? null,
            'defaultType' => $defaultType ?? 'اندازه',
            'defaultName' => $defaultName ?? 'اندازه مبل',
        ])
    </form>
@endsection
