@extends('layouts.app')

@section('title', isset($activeTag) ? ('مطالب برچسب ' . $activeTag->name . ' | SillaGallery') : (\App\Models\SeoPageSetting::forRoute('blog.index')?->meta_title ?: 'مجله SillaGallery'))
@section('description', isset($activeTag) ? ('مقاله‌های برچسب ' . $activeTag->name . ' در مجله SillaGallery.') : (\App\Models\SeoPageSetting::forRoute('blog.index')?->meta_description ?: 'مقاله‌ها و راهنمای انتخاب مبلمان و لوازم خانه از SillaGallery.'))
@section('robots', \App\Models\SeoSetting::current()->robotsFor(false, false))
@section('canonical', isset($activeTag) ? route('blog.tag', $activeTag->slug) : route('blog.index'))

@section('content')
<section class="overflow-hidden bg-[var(--sg-cream)]">


    <x-layout.container>
        @if($tags->isNotEmpty())
            <nav aria-label="موضوعات مجله" class="flex flex-wrap gap-2 border-b border-[var(--sg-border)] py-5">
                <a href="{{ route('blog.index') }}" class="rounded-full border px-4 py-2 text-xs font-medium transition {{ !isset($activeTag) ? 'border-[var(--sg-ink)] bg-[var(--sg-ink)] text-white' : 'border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-stone)] hover:border-[var(--sg-accent)]' }}">همه مطالب</a>
                @foreach($tags as $tag)
                    <a href="{{ route('blog.tag', $tag->slug) }}" class="rounded-full border px-4 py-2 text-xs font-medium transition {{ isset($activeTag) && $activeTag->is($tag) ? 'border-[var(--sg-ink)] bg-[var(--sg-ink)] text-white' : 'border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-stone)] hover:border-[var(--sg-accent)]' }}">{{ $tag->name }}</a>
                @endforeach
            </nav>
        @endif

        <div class="py-7 sm:py-9">
            <div class="mb-8 flex items-center justify-between gap-4">
                <p class="text-sm text-[var(--sg-stone)]">{{ number_format($posts->total()) }} مطلب</p>
                <span class="text-xs tracking-[.16em] text-[var(--sg-accent)]">بخوانید · ایده بگیرید · انتخاب کنید</span>
            </div>

            <div class="grid grid-cols-1 items-stretch gap-5 md:grid-cols-12 md:gap-6">
                @forelse($posts as $post)
                    <article class="group relative flex min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-[var(--sg-border)] bg-[var(--sg-white)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_20px_55px_rgba(35,29,23,.09)] {{ $loop->first ? 'md:col-span-7 md:row-span-2 md:min-h-[36rem]' : 'md:col-span-5 md:min-h-[17rem]' }}">
                        <a href="{{ route('blog.show', $post->slug) }}" class="relative block shrink-0 overflow-hidden bg-[var(--sg-surface)] {{ $loop->first ? 'aspect-[16/10] md:absolute md:inset-0 md:h-full' : 'aspect-[16/10]' }}" aria-label="مطالعه {{ $post->title }}">
                            @if($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]" loading="lazy" decoding="async">
                            @else
                                <div class="flex h-full min-h-40 items-center justify-center"><span class="sg-eyebrow">مجله سیلا گالری</span></div>
                            @endif
                            @if($loop->first)
                                <span aria-hidden="true" class="pointer-events-none absolute inset-x-0 bottom-0 h-[58%] bg-gradient-to-t from-black/85 via-black/35 to-transparent"></span>
                            @endif
                        </a>
                        <div class="{{ $loop->first ? 'pointer-events-none relative z-10 mt-auto p-6 text-white sm:p-9' : 'flex flex-1 flex-col p-5 sm:p-6' }}">
                            <div class="flex flex-wrap items-center justify-between gap-3 text-xs {{ $loop->first ? 'text-white/75' : 'text-[var(--sg-stone)]' }}">
                                <span class="font-semibold {{ $loop->first ? 'text-white' : 'text-[var(--sg-accent)]' }}">{{ $post->category ?: 'مجله سیلاگالری' }}</span>
                                <time datetime="{{ $post->published_at?->toAtomString() }}">{{ $post->published_at?->format('Y/m/d') }}</time>
                            </div>
                            <h2 class="{{ $loop->first ? 'mt-4 max-w-2xl text-2xl font-semibold leading-10 sm:text-4xl sm:leading-[1.45]' : 'mt-3 text-lg font-semibold leading-8 text-[var(--sg-ink)] sm:text-xl' }}">
                                <a href="{{ route('blog.show', $post->slug) }}" class="{{ $loop->first ? '' : 'transition group-hover:text-[var(--sg-accent)]' }}">{{ $post->title }}</a>
                            </h2>
                            <p class="mt-3 line-clamp-2 text-sm leading-7 {{ $loop->first ? 'max-w-xl text-white/80' : 'text-[var(--sg-stone)]' }}">{{ $post->excerpt }}</p>
                            <p class="mt-auto pt-5 text-xs {{ $loop->first ? 'text-white/80' : 'text-[var(--sg-accent)]' }}">{{ $post->tags->take(2)->pluck('name')->implode(' · ') }}</p>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full mx-auto max-w-2xl rounded-3xl border border-dashed border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-16 text-center sm:px-12">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-2xl text-[var(--sg-accent)]" aria-hidden="true">✳</span>
                        <h2 class="mt-6 text-2xl font-semibold text-[var(--sg-ink)]">به‌زودی در مجله می‌بینیمتان</h2>
                        <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">مقاله‌ای برای نمایش منتشر نشده است. به‌زودی راهنماها و ایده‌های تازه اینجا قرار می‌گیرند.</p>
                        <a href="{{ route('shop.index') }}" class="mt-7 inline-flex min-h-11 items-center rounded-xl bg-[var(--sg-ink)] px-5 py-3 text-sm font-semibold text-white hover:bg-[var(--sg-accent)]">مشاهده فروشگاه</a>
                    </div>
                @endforelse
            </div>
            <div class="mt-10">{{ $posts->links() }}</div>
        </div>
    </x-layout.container>
</section>
@endsection