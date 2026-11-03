@extends('layouts.app')

@section('title', $post->meta_title ?: ($post->title . ' | SillaGallery'))
@section('description', $post->meta_description ?: ($post->excerpt ?: 'مقاله‌ای از مجله SillaGallery'))
@section('robots', $robots)
@section('canonical', $post->canonical_url ?: route('blog.show', $post->slug))
@section('og_title', $post->meta_title ?: ($post->title . ' | SillaGallery'))
@section('og_description', $post->meta_description ?: ($post->excerpt ?: 'مقاله‌ای از مجله SillaGallery'))
@section('og_image', $post->og_image_url ?: $post->featured_image_url)
@section('og_type', 'article')

@push('seo')
@php
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $post->meta_title ?: $post->title,
        'description' => $post->meta_description ?: ($post->excerpt ?: \\Illuminate\\Support\\Str::limit(strip_tags($post->body), 300)),
        'mainEntityOfPage' => route('blog.show', $post->slug),
        'datePublished' => $post->published_at?->toAtomString(),
        'dateModified' => $post->updated_at?->toAtomString(),
        'author' => $post->author ? ['@type' => 'Person', 'name' => $post->author->name] : ['@type' => 'Organization', 'name' => 'SillaGallery'],
        'publisher' => ['@type' => 'Organization', 'name' => 'SillaGallery'],
        'articleSection' => $post->category,
        'keywords' => $post->tags->pluck('name')->values()->all(),
    ];
    if ($post->featured_image_url) $articleSchema['image'] = [$post->featured_image_url];
@endphp
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
@endpush

@section('content')
<section class="bg-[var(--sg-cream)] py-8 sm:py-12 lg:py-16">
    <x-layout.container>
        <nav aria-label="مسیر مقاله" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--sg-stone)]">
            <a href="{{ route('home') }}" class="hover:text-[var(--sg-ink)]">خانه</a><span>/</span><a href="{{ route('blog.index') }}" class="hover:text-[var(--sg-ink)]">مجله</a><span>/</span><span class="text-[var(--sg-ink)]">{{ $post->title }}</span>
        </nav>
        <article class="mx-auto max-w-5xl overflow-hidden rounded-[1.75rem] border border-[var(--sg-border)] bg-[var(--sg-white)] shadow-[0_20px_70px_rgba(35,29,23,.06)]">
            @if($post->featured_image_url)
                <figure class="max-h-[620px] overflow-hidden bg-[var(--sg-surface)]"><img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="max-h-[620px] w-full object-cover" fetchpriority="high"></figure>
            @endif
            <header class="border-b border-[var(--sg-border)] px-6 py-8 sm:px-10 sm:py-12 lg:px-16">
                <p class="sg-eyebrow">{{ $post->category ?: 'SILLAGALLERY JOURNAL' }}</p>
                <h1 class="mt-4 text-3xl font-semibold leading-[1.5] tracking-tight text-[var(--sg-ink)] sm:text-4xl lg:text-5xl">{{ $post->title }}</h1>
                <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-[var(--sg-stone)]">
                    @if($post->published_at)<time datetime="{{ $post->published_at->toAtomString() }}">انتشار: {{ $post->published_at->format('Y/m/d') }}</time>@endif
                    @if($post->author)<span>نویسنده: {{ $post->author->name }}</span>@endif
                </div>
                @if($post->excerpt)<p class="mt-7 max-w-4xl border-r-2 border-[var(--sg-accent)] pr-4 text-base leading-9 text-[var(--sg-stone)] sm:text-lg">{{ $post->excerpt }}</p>@endif
            </header>
            <div class="cms-content px-6 py-8 sm:px-10 sm:py-12 lg:px-16 lg:py-14">{!! $post->body !!}</div>
            @if($post->tags->isNotEmpty())
                <footer class="flex flex-wrap items-center gap-2 border-t border-[var(--sg-border)] px-6 py-6 sm:px-10 lg:px-16">
                    <span class="ml-2 text-xs font-semibold text-[var(--sg-stone)]">موضوعات:</span>
                    @foreach($post->tags as $tag)<a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border border-[var(--sg-border)] px-3 py-2 text-xs text-[var(--sg-stone)] transition hover:border-[var(--sg-accent)] hover:text-[var(--sg-accent)]">#{{ $tag->name }}</a>@endforeach
                </footer>
            @endif
        </article>
        <div class="mt-8 flex justify-between gap-4">
            <a href="{{ route('blog.index') }}" class="inline-flex min-h-11 items-center rounded-xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-5 py-3 text-sm font-semibold text-[var(--sg-ink)] hover:border-[var(--sg-accent)]">بازگشت به مجله</a>
            <a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center rounded-xl bg-[var(--sg-ink)] px-5 py-3 text-sm font-semibold text-white hover:bg-[var(--sg-accent)]">پرسش از ما</a>
        </div>
    </x-layout.container>
</section>
@endsection
