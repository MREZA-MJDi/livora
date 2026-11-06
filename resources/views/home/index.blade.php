@extends('layouts.app')

@section('title', \App\Models\SeoPageSetting::forRoute('home')?->meta_title ?: (($storeSettings?->brand_name ?: config('app.name')).' | مبلمان و دکوراسیون'))
@section('description', \App\Models\SeoPageSetting::forRoute('home')?->meta_description ?: ('مجموعه مبلمان و عناصر خانه در '.($storeSettings?->brand_name ?: config('app.name')).'.'))
@section('canonical', \App\Models\SeoPageSetting::forRoute('home')?->canonical_url ?: url('/'))
@section('robots', \App\Models\SeoPageSetting::forRoute('home')?->robots() ?? \App\Models\SeoSetting::current()->robots())

@section('content')
<div class="sila-home overflow-hidden bg-[var(--sg-cream)]">
    @foreach($homeSections as $section)
        @php
            $payload = $section->payload ?? [];
            $ctaUrl = filled($section->cta_url)
                ? (str_starts_with($section->cta_url, 'http') ? $section->cta_url : url($section->cta_url))
                : route('shop.index');
        @endphp

        @if(in_array($section->type, ['hero', 'editorial', 'cta'], true))
            <section class="border-b border-[var(--sg-border)] {{ $section->type === 'cta' ? 'bg-[var(--sg-ink)] text-white' : 'bg-[var(--sg-white)]' }}">
                <x-layout.container>
                    @php
                        $isHero = $section->type === 'hero';
                        $heroTitle = $isHero ? ($section->title ?: 'خانه‌ای که شبیه توست.') : $section->title;
                        $heroSubtitle = $isHero ? ($section->subtitle ?: 'مبلمان و جزئیات دکوراسیون را با آرامش ببینید و برای فضای زندگی‌تان انتخاب کنید.') : $section->subtitle;
                        $heroEyebrow = $isHero ? ($section->eyebrow ?: 'سیلا گالری | مبلمان و دکوراسیون') : $section->eyebrow;
                    @endphp
                    <div class="{{ $isHero ? 'sila-home-hero-grid' : 'grid' }} grid-cols-1 items-center gap-8 py-10 sm:py-14 lg:grid-cols-2 lg:gap-14 lg:py-16">
                        <div class="{{ $isHero ? 'sila-home-hero-copy' : 'max-w-2xl' }} max-w-2xl {{ $section->type === 'cta' ? 'lg:order-2' : '' }}">
                            @if($heroEyebrow)<p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--sg-accent)]">{{ $heroEyebrow }}</p>@endif
                            @if($heroTitle)<h1 class="{{ $isHero ? 'sila-home-hero-title' : 'mt-4 text-3xl sm:text-5xl' }} font-semibold leading-tight tracking-tight {{ $section->type === 'cta' ? 'text-white' : 'text-[var(--sg-ink)]' }}">{{ $heroTitle }}</h1>@endif
                            @if($heroSubtitle)<p class="mt-5 max-w-xl text-sm leading-8 {{ $section->type === 'cta' ? 'text-white/65' : 'text-[var(--sg-stone)]' }}">{{ $heroSubtitle }}</p>@endif
                            @if($section->cta_label)<a href="{{ $ctaUrl }}" class="mt-7 inline-flex min-h-12 items-center justify-center rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-3 text-sm font-semibold text-[var(--sg-ink)] transition hover:border-[var(--sg-accent)]">{{ $section->cta_label }} <span class="mr-3" aria-hidden="true">←</span></a>@endif
                        </div>
                        @php($heroProduct = $section->type === 'hero' ? $section->products->first() : null)
                        @php($heroImage = $section->image_url ?: $heroProduct?->images?->first()?->detail_url)
                        <div class="{{ $isHero ? 'sila-home-hero-media' : 'min-w-0' }} min-w-0 {{ $section->type === 'cta' ? 'lg:order-1' : '' }}">
                            @if($heroImage)
                                <div class="overflow-hidden rounded-[1.5rem] sm:rounded-[2rem] bg-[var(--sg-surface)]">
                                    <img src="{{ $heroImage }}" alt="{{ $heroTitle ?: 'سیلا گالری' }}" class="sila-home-hero-image w-full object-cover" fetchpriority="{{ $section->type === 'hero' ? 'high' : 'auto' }}">
                                </div>
                            @endif
                        </div>
                    </div>
                </x-layout.container>
            </section>
        @elseif($section->type === 'categories' && $section->categories->isNotEmpty())
            <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)] py-14 sm:py-20">
                <x-layout.container>
                    <div class="mb-8 max-w-2xl">
                        @if($section->eyebrow)<p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--sg-accent)]">{{ $section->eyebrow }}</p>@endif
                        @if($section->title)<h2 class="mt-3 text-3xl font-semibold tracking-tight text-[var(--sg-ink)] sm:text-4xl">{{ $section->title }}</h2>@endif
                        @if($section->subtitle)<p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">{{ $section->subtitle }}</p>@endif
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($section->categories as $category)
                            <x-shop.category-card :name="$category->name" :image="$category->homepage_image_url" :href="route('categories.show', $category->slug)" :count="$category->products_count" />
                        @endforeach
                    </div>
                </x-layout.container>
            </section>

        @elseif($section->type === 'products' && $section->products->isNotEmpty())
            <section class="border-b border-[var(--sg-border)] bg-[var(--sg-cream)] py-14 sm:py-20">
                <x-layout.container>
                    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                        <div class="max-w-2xl">
                            @if($section->eyebrow)<p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--sg-accent)]">{{ $section->eyebrow }}</p>@endif
                            @if($section->title)<h2 class="mt-3 text-3xl font-semibold tracking-tight text-[var(--sg-ink)] sm:text-4xl">{{ $section->title }}</h2>@endif
                            @if($section->subtitle)<p class="mt-4 text-sm leading-8 text-[var(--sg-stone)]">{{ $section->subtitle }}</p>@endif
                        </div>
                        @if($section->cta_label)<a href="{{ $ctaUrl }}" class="text-sm font-semibold text-[var(--sg-ink)] transition hover:text-[var(--sg-accent)]">{{ $section->cta_label }} ←</a>@endif
                    </div>
                    <div data-sg-carousel class="relative">
                        <div class="mb-3 hidden items-center justify-end gap-2 sm:flex">
                            <button type="button" data-carousel-prev aria-label="محصول قبلی" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-ink)] transition hover:border-[var(--sg-ink)]">←</button>
                            <button type="button" data-carousel-next aria-label="محصول بعدی" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)] text-[var(--sg-ink)] transition hover:border-[var(--sg-ink)]">→</button>
                        </div>
                        <div class="sg-product-carousel" data-carousel-track>
                            @foreach($section->products as $product)
                                <div class="sg-product-carousel-item"><x-product.card :product="$product" /></div>
                            @endforeach
                        </div>
                    </div>
                </x-layout.container>
            </section>

        @elseif($section->type === 'trust')
            @php($items = $payload['items'] ?? [])
            @if(count($items))
                <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)] py-12 sm:py-16">
                    <x-layout.container>
                        <div class="mb-8 max-w-2xl">
                            @if($section->eyebrow)<p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--sg-accent)]">{{ $section->eyebrow }}</p>@endif
                            @if($section->title)<h2 class="mt-3 text-2xl font-semibold tracking-tight text-[var(--sg-ink)] sm:text-3xl">{{ $section->title }}</h2>@endif
                            @if($section->subtitle)<p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">{{ $section->subtitle }}</p>@endif
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach($items as $item)<article class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)] p-5"><h3 class="font-semibold text-[var(--sg-ink)]">{{ $item['title'] ?? '' }}</h3><p class="mt-3 text-sm leading-7 text-[var(--sg-stone)]">{{ $item['body'] ?? '' }}</p></article>@endforeach
                        </div>
                    </x-layout.container>
                </section>
            @endif
        @endif
    @endforeach

            <section class="border-b border-[var(--sg-border)] bg-[var(--sg-white)] py-14 sm:py-20">
            <x-layout.container>
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--sg-accent)]">مجله سیلا</p>
                        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-[var(--sg-ink)] sm:text-4xl">ایده‌هایی برای خانه‌ای دلنشین‌تر</h2>
                        <p class="mt-3 text-sm leading-8 text-[var(--sg-stone)]">راهنماها و نوشته‌های تازه برای انتخاب آگاهانه‌تر.</p>
                    </div>
                    <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-[var(--sg-ink)] transition hover:text-[var(--sg-accent)]">همه نوشته‌ها ←</a>
                </div>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @if($blogPosts->isNotEmpty())
                    @foreach($blogPosts as $post)
                        <article class="group overflow-hidden rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)]">
                            <a href="{{ route('blog.show', $post->slug) }}" class="block overflow-hidden bg-[var(--sg-surface)]">
                                @if($post->featured_image_url)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->featured_image_alt ?: $post->title }}" class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-[1.035]" loading="lazy" decoding="async">
                                @else
                                    <div class="flex aspect-[16/10] items-center justify-center text-sm text-[var(--sg-stone)]">مجله سیلا</div>
                                @endif
                            </a>
                            <div class="p-5">
                                <p class="text-[10px] text-[var(--sg-stone)]">{{ $post->published_at?->format('Y/m/d') }}</p>
                                <h3 class="mt-2 text-base font-semibold leading-7 text-[var(--sg-ink)]"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                                @if(filled($post->excerpt))
                                    <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--sg-stone)]">{{ $post->excerpt }}</p>
                                @endif
                                <a href="{{ route('blog.show', $post->slug) }}" class="mt-4 inline-flex text-xs font-semibold text-[var(--sg-accent)]">ادامه مطلب ←</a>
                            </div>
                        </article>
                    @endforeach
                    @else
                        <article class="rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-cream)] p-6 sm:col-span-2 lg:col-span-3">
                            <p class="text-sm font-semibold text-[var(--sg-ink)]">راهنمای انتخاب مبلمان</p>
                            <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">پیش از انتخاب، ابعاد فضا، متریال و شرایط سفارش را بررسی کنید.</p>
                            <a href="{{ url('/buying-guide') }}" class="mt-4 inline-flex text-xs font-semibold text-[var(--sg-accent)]">مطالعه راهنمای خرید ←</a>
                        </article>
                    @endif
                </div>
            </x-layout.container>
        </section>
    @if($finalCta)
        <section class="border-b border-[var(--sg-border)] bg-[var(--sg-ink)] py-14 text-white sm:py-20">
            <x-layout.container>
                <div class="flex flex-col items-start justify-between gap-7 sm:flex-row sm:items-center">
                    <div class="max-w-2xl">
                        @if($finalCta->eyebrow)<p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-white/55">{{ $finalCta->eyebrow }}</p>@endif
                        <h2 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl">{{ $finalCta->title }}</h2>
                        @if($finalCta->subtitle)<p class="mt-4 text-sm leading-8 text-white/65">{{ $finalCta->subtitle }}</p>@endif
                    </div>
                    @if($finalCta->cta_label)<a href="{{ filled($finalCta->cta_url) ? url($finalCta->cta_url) : route('shop.index') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-6 py-4 text-sm font-semibold text-[var(--sg-ink)]">{{ $finalCta->cta_label }} ←</a>@endif
                </div>
            </x-layout.container>
        </section>
    @endif
</div>
@endsection
