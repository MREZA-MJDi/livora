@extends('layouts.app')

@php($blogPageSeo = \App\Models\SeoPageSetting::forRoute('blog.index'))
@section('title', isset($activeTag) ? ('مطالب برچسب ' . $activeTag->name . ' | SillaGallery') : ($blogPageSeo?->meta_title ?: 'مجله SillaGallery'))
@section('description', isset($activeTag) ? ('مقاله‌های برچسب ' . $activeTag->name . ' در مجله SillaGallery.') : ($blogPageSeo?->meta_description ?: 'مقاله‌ها و راهنمای انتخاب مبلمان و لوازم خانه از SillaGallery.'))
@section('robots', \App\Models\SeoSetting::current()->robotsFor(false, false))
@section('canonical', isset($activeTag) ? route('blog.tag', $activeTag->slug) : route('blog.index'))

@section('content')
<section class="bg-[var(--sg-cream)]">
    <header class="border-b border-[var(--sg-border)] bg-[var(--sg-white)]">
        <x-layout.container>
            <div class="grid gap-8 py-12 sm:py-16 lg:grid-cols-[1fr_.7fr] lg:items-end lg:py-20">
                <div>
                    <p class="sg-eyebrow">مجله سیلا گالری</p>
                    <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight text-[var(--sg-ink)] sm:text-5xl">{{ isset($activeTag) ? 'مطالب: '.$activeTag->name : 'مجله سیلاگالری' }}</h1>
                </div>
                <p class="max-w-xl text-sm leading-8 text-[var(--sg-stone)] sm:text-base">{{ isset($activeTag) ? ('مقاله‌های مرتبط با برچسب ' . $activeTag->name) : ($blogPageSeo?->meta_description ?: 'ایده‌ها، راهنماها و روایت‌هایی برای انتخاب آگاهانه‌تر مبلمان و ساختن فضایی که به شما تعلق دارد.') }}</p>
            </div>
        </x-layout.container>
    </header>

    <x-layout.container>
        @if($tags->isNotEmpty())
            <nav aria-label="موضوعات مجله" class="flex flex-wrap gap-2 border-b border-[var(--sg-border)] py-6">
                <a href="{{ route('blog.index') }}" class="rounded-full border px-4 py-2 text-xs font-medium transition {{ !isset($activeTag) ? 'border-[var(--sg-ink)] bg-[var(--sg-ink)] text-white' : 'border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-stone)] hover:border-[var(--sg-accent)]' }}">همه مطالب</a>
                @foreach($tags as $tag)
                    <a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border px-4 py-2 text-xs font-medium transition {{ isset($activeTag) && $activeTag->is($tag) ? 'border-[var(--sg-ink)] bg-[var(--sg-ink)] text-white' : 'border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-stone)] hover:border-[var(--sg-accent)]' }}">{{ $tag->name }}</a>
                @endforeach
            </nav>
        @endif

        <div class="py-10 sm:py-14">
            @if($posts->count())
                <div class="mb-7 flex items-center justify-between gap-4">
                    <p class="text-sm text-[var(--sg-stone)]">{{ number_format($posts->total()) }} مطلب</p>
                    <span class="text-xs tracking-[.16em] text-[var(--sg-accent)]">بخوانید · ایده بگیرید · انتخاب کنید</span>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($posts as $post)
                        <article class="group flex min-w-0 flex-col overflow-hidden rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_50px_rgba(35,29,23,.08)]">
                            <a href="{{ route('blog.show', $post->slug) }}" class="relative block aspect-[16/10] overflow-hidden bg-[var(--sg-surface)]">
                                @if($post->featured_image_url)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.035]" loading="lazy" decoding="async">
                                @else
                                    <div class="flex h-full items-center justify-center"><span class="sg-eyebrow">مجله سیلا گالری</span></div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-5 sm:p-6">
                                <div class="flex flex-wrap items-center justify-between gap-3 text-xs text-[var(--sg-stone)]">
                                    @if($post->category)<span class="font-semibold text-[var(--sg-accent)]">{{ $post->category }}</span>@else<span>مجله سیلاگالری</span>@endif
                                    @if($post->published_at)<time datetime="{{ $post->published_at->toAtomString() }}">{{ $post->published_at->format('Y/m/d') }}</time>@endif
                                </div>
                                <h2 class="mt-4 text-xl font-semibold leading-8 text-[var(--sg-ink)]"><a href="{{ route('blog.show', $post->slug) }}" class="transition group-hover:text-[var(--sg-accent)]">{{ $post->title }}</a></h2>
                                @if($post->excerpt)<p class="mt-3 line-clamp-3 text-sm leading-7 text-[var(--sg-stone)]">{{ $post->excerpt }}</p>@endif
                                @if($post->tags->isNotEmpty())
                                    <div class="mt-4 flex flex-wrap gap-2">@foreach($post->tags->take(3) as $tag)<a href="{{ route('blog.tag', $tag->slug) }}" class="text-xs text-[var(--sg-accent)] hover:underline">#{{ $tag->name }}</a>@endforeach</div>
                                @endif
                                <a href="{{ route('blog.show', $post->slug) }}" class="mt-auto inline-flex w-fit items-center gap-2 pt-6 text-sm font-semibold text-[var(--sg-ink)]">مطالعه مقاله <span aria-hidden="true">←</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-10">{{ $posts->links() }}</div>
            @else
                <div class="mx-auto max-w-2xl rounded-3xl border border-dashed border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-16 text-center sm:px-12">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-2xl text-[var(--sg-accent)]" aria-hidden="true">✳</span>
                    <h2 class="mt-6 text-2xl font-semibold text-[var(--sg-ink)]">به‌زودی در مجله می‌بینیمتان</h2>
                    <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">مقاله‌ای برای نمایش منتشر نشده است. به‌زودی راهنماها و ایده‌های تازه اینجا قرار می‌گیرند.</p>
                    <a href="{{ route('shop.index') }}" class="mt-7 inline-flex min-h-11 items-center rounded-xl bg-[var(--sg-ink)] px-5 py-3 text-sm font-semibold text-white hover:bg-[var(--sg-accent)]">مشاهده فروشگاه</a>
                </div>
            @endif
        </div>
    </x-layout.container>
</section>
@endsection
