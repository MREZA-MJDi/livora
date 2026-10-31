@extends('admin.layouts.app')
@section('title', 'ویرایش صفحه')
@section('page_title', 'ویرایش صفحه')
@section('content')
<div class="mb-7"><p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / PAGES / EDIT</p><h1 class="admin-title">ویرایش صفحه</h1><p class="admin-subtitle mt-2">{{ $page->title }}</p></div>
<form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data">@method('PUT')@include('admin.pages.partials.form')</form>
@endsection