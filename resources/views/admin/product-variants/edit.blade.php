@extends('admin.layouts.app')

@section('title', 'ویرایش تنوع')
@section('page_title', 'ویرایش تنوع محصول')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCT VARIANTS / EDIT
        </div>

        <h2 class="admin-title">
            ویرایش تنوع محصول
        </h2>

        <p class="admin-subtitle mt-2">
            اطلاعات تنوع «{{ $productVariant->name }}» را ویرایش کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.product-variants.update', $productVariant) }}"
        method="POST"
    >

        @method('PUT')

        @include('admin.product-variants.partials.form', [
            'products' => $products,
            'productVariant' => $productVariant,
        ])

    </form>

@endsection
