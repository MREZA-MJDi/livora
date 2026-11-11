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
        <strong class="block text-[var(--admin-text)]">قبل از ثبت، ساختار محصول را مشخص کن</strong>
        <ul class="mt-2 list-disc space-y-1 pr-5">
            <li>اگر یک کالا فقط رنگ یا اندازه متفاوت دارد، آن را یک‌بار بساز و هر رنگ/اندازه را به‌عنوان «تنوع» با عکس، موجودی و قیمت مخصوص خودش ثبت کن.</li>
            <li>در فرم تنوع، قیمت نهایی همان گزینه را وارد می‌کنی؛ اختلاف آن با قیمت پایه خودکار محاسبه می‌شود.</li>
            <li>اگر اقلامی مثل جلو‌مبلی، عسلی یا میز TV جداگانه فروخته می‌شوند، هرکدام محصول مستقل باشند و در صورت نیاز به هم مرتبط شوند؛ آن‌ها را به‌عنوان رنگ یا اندازه ثبت نکن.</li>
        </ul>
        <p class="mt-2 text-xs text-[var(--admin-muted)]">مثال: «غذاخوری» یک محصول با تنوع‌های ۴، ۶، ۸ و ۱۰ نفره است؛ اما «جلو‌مبلی تک» کالای مستقل خودش است.</p>
    </div>

    <form
        action="{{ route('admin.products.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @include('admin.products.partials.form')
    </form>

@endsection
