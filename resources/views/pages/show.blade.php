@extends('layouts.app')

@section('title', $page->meta_title ?: ($page->title . ' | SillaGallery'))
@section('description', $page->meta_description ?: ($page->excerpt ?: 'اطلاعات و مطالب SillaGallery'))
@section('robots', $robots)
@section('canonical', $page->canonical_url ?: route('pages.show', $page->slug))
@section('og_image', $page->og_image_url ?: $page->featured_image_url)
@section('og_type', 'article')

@section('content')
<section class="min-h-[65vh] bg-[var(--sg-cream)] py-10 sm:py-16">
    <x-layout.container>
        <article class="mx-auto max-w-4xl overflow-hidden rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)]">
            @if($page->featured_image_url)
                <img src="{{ $page->featured_image_url }}" alt="{{ $page->featured_image_alt ?: $page->title }}" class="max-h-[520px] w-full object-cover">
            @endif
            <header class="border-b border-[var(--sg-border)] p-6 sm:p-10">
                <h1 class="text-3xl font-semibold leading-relaxed text-[var(--sg-ink)] sm:text-4xl">{{ $page->title }}</h1>
                @if($page->excerpt)<p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">{{ $page->excerpt }}</p>@endif
            </header>
            <div class="cms-content p-6 text-sm leading-9 text-[var(--sg-ink)] sm:p-10">
                {!! $page->body !!}
            </div>
        </article>
    </x-layout.container>
</section>
@endsection