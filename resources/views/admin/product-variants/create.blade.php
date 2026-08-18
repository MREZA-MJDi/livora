@extends('admin.layouts.app')

@section('title', 'افزودن تنوع')
@section('page_title', 'افزودن تنوع محصول')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCT VARIANTS / CREATE
        </div>

        <h2 class="admin-title">
            افزودن تنوع محصول
        </h2>

        <p class="admin-subtitle mt-2">
            یک تنوع جدید برای محصول ایجاد کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.product-variants.store') }}"
        method="POST"
    >

        @include('admin.product-variants.partials.form', [
            'products' => $products,
            'selectedProductId' => $selectedProductId ?? null,
        ])

    </form>

@endsection
