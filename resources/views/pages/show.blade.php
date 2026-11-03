@extends('layouts.app')

@section('title', $page->meta_title ?: ($page->title . ' | SillaGallery'))
@section('description', $page->meta_description ?: ($page->excerpt ?: 'اطلاعات و مطالب SillaGallery'))
@section('robots', $robots)
@section('canonical', $page->canonical_url ?: route('pages.show', $page->slug))
@section('og_image', $page->og_image_url ?: $page->featured_image_url)
@section('og_type', 'article')

@section('content')
@php
    $body = (string) $page->body;
    $body = preg_replace_callback('/<(h1|h2)\\b[^>]*>(.*?)<\\/\\1>/is', function ($match) use ($page) {
        return trim(preg_replace('/\\s+/u', ' ', strip_tags($match[2]))) === trim($page->title)
            ? ''
            : $match[0];
    }, $body, 1);
@endphp
<section class="bg-[var(--sg-cream)] py-8 sm:py-12 lg:py-16">
    <x-layout.container>
        <nav aria-label="مسیر صفحه" class="mb-6 flex flex-wrap items-center gap-2 text-xs text-[var(--sg-stone)]">
            <a href="{{ route('home') }}" class="transition hover:text-[var(--sg-ink)]">خانه</a>
            <span aria-hidden="true">/</span>
            <span class="text-[var(--sg-ink)]">{{ $page->title }}</span>
        </nav>
        <article class="mx-auto max-w-5xl overflow-hidden rounded-[1.75rem] border border-[var(--sg-border)] bg-[var(--sg-white)] shadow-[0_20px_70px_rgba(35,29,23,.06)]">
            @if($page->featured_image_url)
                <figure class="relative max-h-[560px] overflow-hidden bg-[var(--sg-surface)]">
                    <img src="{{ $page->featured_image_url }}" alt="{{ $page->featured_image_alt ?: $page->title }}" class="max-h-[560px] w-full object-cover" fetchpriority="high">
                </figure>
            @endif
            <header class="border-b border-[var(--sg-border)] px-6 py-8 sm:px-10 sm:py-12 lg:px-16">
                <p class="sg-eyebrow">SILLAGALLERY · OUR STORY</p>
                <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight text-[var(--sg-ink)] sm:text-4xl lg:text-5xl">{{ $page->title }}</h1>
                @if($page->excerpt)
                    <p class="mt-5 max-w-3xl text-sm leading-8 text-[var(--sg-stone)] sm:text-base">{{ $page->excerpt }}</p>
                @endif
                <span class="mt-7 block h-px w-16 bg-[var(--sg-accent)]"></span>
            </header>
            <div class="cms-content px-6 py-8 sm:px-10 sm:py-12 lg:px-16 lg:py-14">
                {!! $body !!}
            </div>
        </article>
    </x-layout.container>
</section>
@endsection
