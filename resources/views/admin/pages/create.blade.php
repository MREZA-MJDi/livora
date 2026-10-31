@extends('admin.layouts.app')
@section('title', 'افزودن صفحه')
@section('page_title', 'افزودن صفحه')
@section('content')
<div class="mb-7"><p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / PAGES / CREATE</p><h1 class="admin-title">افزودن صفحه</h1><p class="admin-subtitle mt-2">صفحه را به‌صورت پیش‌نویس آماده و بعد از بازبینی منتشر کنید.</p></div>
<form method="POST" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data">@include('admin.pages.partials.form')</form>
@endsection