@extends('admin.layouts.app')

@section('title', 'افزودن تصویر')
@section('page_title', 'افزودن تصویر محصول')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCT IMAGES / CREATE
        </div>

        <h2 class="admin-title">
            افزودن تصویر محصول
        </h2>

        <p class="admin-subtitle mt-2">
            یک تصویر جدید برای محصول انتخاب کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.product-images.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @include('admin.product-images.partials.form', [
            'products' => $products,
            'selectedProductId' => $selectedProductId ?? null,
        ])

    </form>

@endsection
