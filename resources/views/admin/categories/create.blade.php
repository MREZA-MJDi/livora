@extends('admin.layouts.app')

@section('title', 'افزودن دسته‌بندی')
@section('page_title', 'افزودن دسته‌بندی')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / CATEGORIES / CREATE
        </div>

        <h2 class="admin-title">
            افزودن دسته‌بندی
        </h2>

        <p class="admin-subtitle mt-2">
            یک دسته‌بندی جدید برای فروشگاه ایجاد کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.categories.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @include('admin.categories.partials.form')

    </form>

@endsection
