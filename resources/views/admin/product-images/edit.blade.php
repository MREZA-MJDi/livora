@extends('admin.layouts.app')

@section('title', 'ویرایش تصویر')
@section('page_title', 'ویرایش تصویر محصول')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCT IMAGES / EDIT
        </div>

        <h2 class="admin-title">
            ویرایش تصویر محصول
        </h2>

        <p class="admin-subtitle mt-2">
            اطلاعات و مشخصات تصویر را ویرایش کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.product-images.update', $productImage) }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @method('PUT')

        @include('admin.product-images.partials.form', [
            'products' => $products,
            'productImage' => $productImage,
        ])

    </form>

@endsection
