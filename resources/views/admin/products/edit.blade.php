@extends('admin.layouts.app')

@section('title', 'ویرایش محصول')
@section('page_title', 'ویرایش محصول')

@section('content')

    <div class="mb-8">
        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCTS / EDIT
        </div>

        <h2 class="admin-title">
            ویرایش محصول
        </h2>

        <p class="admin-subtitle mt-2">
            اطلاعات محصول «{{ $product->name }}» را ویرایش کنید.
        </p>
    </div>

    <form
        action="{{ route('admin.products.update', $product) }}"
        method="POST"
    >
        @method('PUT')

        @include('admin.products.partials.form')
    </form>

@endsection
