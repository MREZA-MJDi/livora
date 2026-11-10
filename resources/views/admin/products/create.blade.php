@extends('admin.layouts.app')

@section('title', 'افزودن محصول')
@section('page_title', 'افزودن محصول')

@section('content')

    <div class="mb-8">
        <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
            CATALOG / PRODUCTS / CREATE
        </div>

        <h2 class="admin-title">
            افزودن محصول
        </h2>

        <p class="admin-subtitle mt-2">
            یک محصول جدید برای فروشگاه SillaGallery ایجاد کنید.
        </p>
    </div>

    <div class="mb-6 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 text-sm leading-7 text-[var(--admin-text-soft)]">
        <strong class="block text-[var(--admin-text)]">مسیر درست ثبت محصول</strong>
        ابتدا اطلاعات اصلی و قیمت پایه را ذخیره کن. بعد از ایجاد محصول، در صفحه ویرایش همان محصول می‌توانی رنگ، جنس، اندازه و مدل را همراه با موجودی، تغییر قیمت و تصاویر اختصاصی تعریف کنی.
    </div>

    <form
        action="{{ route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @include('admin.products.partials.form')
    </form>

@endsection
