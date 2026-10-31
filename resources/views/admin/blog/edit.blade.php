@extends('admin.layouts.app')

@section('title', 'ویرایش مقاله')
@section('page_title', 'ویرایش مقاله')

@section('content')
<div class="mb-7">
    <p class="mb-2 text-xs font-medium text-[var(--admin-accent)]">CONTENT / BLOG / EDIT</p>
    <h1 class="admin-title">ویرایش مقاله</h1>
    <p class="admin-subtitle mt-2">{{ $post->title }}</p>
</div>
<form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
    @method('PUT')
    @include('admin.blog.partials.form')
</form>
@endsection
