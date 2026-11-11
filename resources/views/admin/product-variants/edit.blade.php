@extends('admin.layouts.app')

@section('title', 'ویرایش تنوع')
@section('page_title', 'ویرایش تنوع محصول')

@section('content')
    <div class="mx-auto mb-6 w-full max-w-5xl">
        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">محصولات / تنوع‌ها / ویرایش</div>
        <h2 class="admin-title">ویرایش گزینه محصول</h2>
        <p class="admin-subtitle mt-2">مشخصات، قیمت، موجودی و تصاویر این گزینه را به‌روزرسانی کن.</p>
    </div>

    <form action="{{ route('admin.product-variants.update', $productVariant) }}" method="POST" enctype="multipart/form-data">
        @method('PUT')
        @include('admin.product-variants.partials.form', [
            'selectedProduct' => $selectedProduct,
            'productVariant' => $productVariant,
        ])
    </form>
@endsection
