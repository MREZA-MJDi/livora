@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: ($post->excerpt ?: 'مقاله‌ای از مجله SillaGallery'))
@section('robots', $robots)
@section('canonical', route('blog.show', $post->slug))

@section('content')
<article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('blog.index') }}" class="text-sm text-[var(--livora-accent)]">← بازگشت به مجله</a>
    <header class="mt-6">
        @if($post->category)<p class="text-xs text-[var(--livora-accent)]">{{ $post->category }}</p>@endif
        <h1 class="mt-3 text-3xl font-semibold leading-relaxed text-[var(--livora-ink)]">{{ $post->title }}</h1>
        <p class="mt-3 text-xs text-[var(--livora-stone)]">{{ $post->published_at?->format('Y/m/d') }}</p>
    </header>
    @if($post->featured_image_url)<img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="mt-8 max-h-[560px] w-full rounded-3xl object-cover">@endif
    @if($post->excerpt)<p class="mt-8 text-lg leading-9 text-[var(--livora-stone)]">{{ $post->excerpt }}</p>@endif
    <div class="prose prose-lg mt-8 max-w-none whitespace-pre-line leading-9 text-[var(--livora-ink)]">{{ $post->body }}</div>
</article>
@endsection
