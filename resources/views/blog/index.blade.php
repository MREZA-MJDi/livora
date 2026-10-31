@extends('layouts.app')

@section('title', ($activeTag->name ?? null) ? ('مطالب برچسب ' . $activeTag->name . ' | SillaGallery') : 'مجله SillaGallery')
@section('description', 'مقاله‌ها و راهنمای انتخاب مبلمان و لوازم خانه از SillaGallery.')
@section('robots', \App\Models\SeoSetting::current()->robotsFor(false, false))
@section('canonical', isset($activeTag) ? route('blog.tag', $activeTag->slug) : route('blog.index'))

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <header class="mb-10 max-w-2xl">
        <p class="text-xs uppercase tracking-[0.2em] text-[var(--sg-accent)]">SILLAGALLERY JOURNAL</p>
        <h1 class="mt-3 text-3xl font-semibold text-[var(--sg-ink)]">{{ isset($activeTag) ? 'مطالب: '.$activeTag->name : 'مجله SillaGallery' }}</h1>
        <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">راهنماها، ایده‌ها و مطالب مرتبط با انتخاب و نگهداری مبلمان و فضای زندگی.</p>
    </header>
    @if($tags->isNotEmpty())
        <nav aria-label="برچسب‌های مجله" class="mb-8 flex flex-wrap gap-2">
            @foreach($tags as $tag)
                <a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border border-[var(--sg-border)] px-3 py-2 text-xs {{ isset($activeTag) && $activeTag->is($tag) ? 'bg-[var(--sg-ink)] text-white' : 'bg-white text-[var(--sg-stone)]' }}">{{ $tag->name }}</a>
            @endforeach
        </nav>
    @endif
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <article class="overflow-hidden rounded-3xl border border-[var(--sg-border)] bg-white">
                @if($post->featured_image_url)<a href="{{ route('blog.show', $post->slug) }}"><img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-[4/3] w-full object-cover" loading="lazy"></a>@endif
                <div class="p-5">
                    @if($post->category)<p class="text-xs text-[var(--sg-accent)]">{{ $post->category }}</p>@endif
                    <h2 class="mt-2 text-lg font-semibold text-[var(--sg-ink)]"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                    <p class="mt-3 text-sm leading-7 text-[var(--sg-stone)]">{{ $post->excerpt }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">@foreach($post->tags as $tag)<a class="text-xs text-[var(--sg-accent)]" href="{{ route('blog.tag', $tag->slug) }}">#{{ $tag->name }}</a>@endforeach</div>
                    <a href="{{ route('blog.show', $post->slug) }}" class="mt-5 inline-flex text-sm font-semibold text-[var(--sg-accent)]">ادامه مطلب ←</a>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-[var(--sg-border)] p-10 text-sm text-[var(--sg-stone)]">مقاله‌ای برای نمایش منتشر نشده است.</div>
        @endforelse
    </div>
    <div class="mt-10">{{ $posts->links() }}</div>
</section>
@endsection