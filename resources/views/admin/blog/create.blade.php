@extends('admin.layouts.app')

@section('title', 'افزودن مقاله')
@section('page_title', 'افزودن مقاله')

@section('content')
<div class="mb-7">
    <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / BLOG / CREATE</p>
    <h1 class="admin-title">افزودن مقاله</h1>
    <p class="admin-subtitle mt-2">مقاله را ابتدا به‌صورت پیش‌نویس ذخیره کنید و پس از تأیید SEO منتشر کنید.</p>
</div>
<form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
    @include('admin.blog.partials.form')
</form>
@endsection
