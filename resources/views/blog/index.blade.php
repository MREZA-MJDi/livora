@extends('layouts.app')

@section('title', 'وبلاگ SillaGallery')
@section('description', 'مقاله‌ها و راهنمای انتخاب مبلمان و لوازم خانه از SillaGallery.')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <header class="mb-10 max-w-2xl">
        <p class="text-xs uppercase tracking-[0.2em] text-[var(--livora-accent)]">SILAGALLERY JOURNAL</p>
        <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)]">مجله SillaGallery</h1>
        <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">راهنماها، ایده‌ها و مطالب مرتبط با انتخاب و نگهداری مبلمان و فضای زندگی.</p>
    </header>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <article class="overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-white">
                @if($post->featured_image_url)<a href="{{ route('blog.show', $post->slug) }}"><img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-[4/3] w-full object-cover"></a>@endif
                <div class="p-5">
                    @if($post->category)<p class="text-xs text-[var(--livora-accent)]">{{ $post->category }}</p>@endif
                    <h2 class="mt-2 text-lg font-semibold text-[var(--livora-ink)]"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h2>
                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post->slug) }}" class="mt-5 inline-flex text-sm font-semibold text-[var(--livora-accent)]">ادامه مطلب ←</a>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-[var(--livora-border)] p-10 text-sm text-[var(--livora-stone)]">مقاله‌ای برای نمایش منتشر نشده است.</div>
        @endforelse
    </div>
    <div class="mt-10">{{ $posts->links() }}</div>
</section>
@endsection
