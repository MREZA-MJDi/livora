@extends('admin.layouts.app')

@section('title', 'ویرایش دسته‌بندی')
@section('page_title', 'ویرایش دسته‌بندی')

@section('content')

    <div class="mb-8">

        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / CATEGORIES / EDIT
        </div>

        <h2 class="admin-title">
            ویرایش دسته‌بندی
        </h2>

        <p class="admin-subtitle mt-2">
            اطلاعات دسته‌بندی «{{ $category->name }}» را ویرایش کنید.
        </p>

    </div>


    <form
        action="{{ route('admin.categories.update', $category) }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @method('PUT')

        @include('admin.categories.partials.form')

    </form>

@endsection
