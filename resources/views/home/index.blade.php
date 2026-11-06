@extends('layouts.app')

@section('title', \App\Models\SeoPageSetting::forRoute('home')?->meta_title ?: 'سیلا گالری | مبلمان و دکوراسیون')
@section('description', \App\Models\SeoPageSetting::forRoute('home')?->meta_description ?: 'مجموعه مبلمان و عناصر خانه در سیلا گالری.')
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
                    <div class="grid items-center gap-8 py-12 sm:py-16 lg:grid-cols-2 lg:gap-14 lg:py-20">
                        <div class="max-w-2xl {{ $section->type === 'cta' ? 'lg:order-2' : '' }}">
                            @if($section->eyebrow)<p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--sg-accent)]">{{ $section->eyebrow }}</p>@endif
                            @if($section->title)<h1 class="mt-4 text-3xl font-semibold leading-tight tracking-tight sm:text-5xl {{ $section->type === 'cta' ? 'text-white' : 'text-[var(--sg-ink)]' }}">{{ $section->title }}</h1>@endif
                            @if($section->subtitle)<p class="mt-5 max-w-xl text-sm leading-8 {{ $section->type === 'cta' ? 'text-white/65' : 'text-[var(--sg-stone)]' }}">{{ $section->subtitle }}</p>@endif
                            @if($section->cta_label)<a href="{{ $ctaUrl }}" class="mt-8 inline-flex items-center justify-center rounded-2xl border border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-4 text-sm font-semibold text-[var(--sg-ink)] transition hover:border-[var(--sg-accent)]">{{ $section->cta_label }} <span class="mr-3" aria-hidden="true">←</span></a>@endif
                        </div>
                        @php($heroProduct = $section->type === 'hero' ? $section->products->first() : null)
                        @php($heroImage = $section->image_url ?: $heroProduct?->images?->first()?->detail_url)
                        <div class="min-w-0 space-y-4 {{ $section->type === 'cta' ? 'lg:order-1' : '' }}">
                            @if($heroImage)
                                <div class="overflow-hidden rounded-[2rem] bg-[var(--sg-surface)]">
                                    <img src="{{ $heroImage }}" alt="{{ $section->title ?: 'SillaGallery' }}" class="aspect-[5/4] h-full w-full object-cover" fetchpriority="{{ $section->type === 'hero' ? 'high' : 'auto' }}">
                                </div>
                            @endif
                            @if($section->type === 'hero' && $section->products->isNotEmpty())
                                <div data-sg-carousel class="relative">
                                    <div class="mb-2 hidden items-center justify-end gap-2 sm:flex">
                                        <button type="button" data-carousel-prev aria-label="محصول قبلی" class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)]">←</button>
                                        <button type="button" data-carousel-next aria-label="محصول بعدی" class="flex h-9 w-9 items-center justify-center rounded-full border border-[var(--sg-border)] bg-[var(--sg-white)]">→</button>
                                    </div>
                                    <div class="sg-product-carousel" data-carousel-track>
                                        @foreach($section->products as $product)
                                            <div class="sg-product-carousel-item"><x-product.card :product="$product" /></div>
                                        @endforeach
                                    </div>
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
</div>
@endsection
