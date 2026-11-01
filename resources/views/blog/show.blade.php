@extends('layouts.app')

@section('title', $post->meta_title ?: ($post->title . ' | SillaGallery'))
@section('description', $post->meta_description ?: ($post->excerpt ?: 'مقاله‌ای از مجله SillaGallery'))
@section('robots', $robots)
@section('canonical', $post->canonical_url ?: route('blog.show', $post->slug))
@section('og_title', $post->meta_title ?: ($post->title . ' | SillaGallery'))
@section('og_description', $post->meta_description ?: ($post->excerpt ?: 'مقاله‌ای از مجله SillaGallery'))
@section('og_image', $post->og_image_url)
@section('og_type', 'article')

@push('seo')
@php
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->meta_title ?: $post->title,
        'description' => $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 300)),
        'mainEntityOfPage' => route('blog.show', $post->slug),
        'datePublished' => $post->published_at?->toAtomString(),
        'dateModified' => $post->updated_at?->toAtomString(),
        'author' => $post->author
            ? ['@type' => 'Person', 'name' => $post->author->name]
            : ['@type' => 'Organization', 'name' => 'SillaGallery'],
        'publisher' => ['@type' => 'Organization', 'name' => 'SillaGallery'],
        'articleSection' => $post->category,
        'keywords' => $post->tags->pluck('name')->values()->all(),
    ];
    if ($post->featured_image_url) {
        $articleSchema['image'] = [$post->featured_image_url];
    }
@endphp
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<article class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('blog.index') }}" class="text-sm text-[var(--sg-accent)]">← بازگشت به مجله</a>
    <header class="mt-6">
        @if($post->category)<p class="text-xs text-[var(--sg-accent)]">{{ $post->category }}</p>@endif
        <h1 class="mt-3 text-3xl font-semibold leading-relaxed text-[var(--sg-ink)]">{{ $post->title }}</h1>
        <p class="mt-3 text-xs text-[var(--sg-stone)]">{{ $post->published_at?->format('Y/m/d') }}</p>
    </header>
    @if($post->featured_image_url)<img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="mt-8 max-h-[560px] w-full rounded-3xl object-cover">@endif
    @if($post->excerpt)<p class="mt-8 text-lg leading-9 text-[var(--sg-stone)]">{{ $post->excerpt }}</p>@endif
    <div class="cms-content prose prose-lg mt-8 max-w-none leading-9 text-[var(--sg-ink)]">{!! $post->body !!}</div>
    @if($post->tags->isNotEmpty())
        <footer class="mt-10 flex flex-wrap gap-2 border-t border-[var(--sg-border)] pt-6">
            @foreach($post->tags as $tag)
                <a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border border-[var(--sg-border)] px-3 py-2 text-xs text-[var(--sg-stone)] hover:border-[var(--sg-accent)]">{{ $tag->name }}</a>
            @endforeach
        </footer>
    @endif
</article>
@endsection