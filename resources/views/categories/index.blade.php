@extends('layouts.app')

@section('title', \App\Models\SeoPageSetting::forRoute('categories')?->meta_title ?: 'مجموعه‌ها و دسته‌بندی‌ها | SillaGallery')

@section('description', \App\Models\SeoPageSetting::forRoute('categories')?->meta_description ?: 'مجموعه‌های مبلمان و دکوراسیون SillaGallery را مرور کنید و برای فضای خود انتخاب دقیق‌تری داشته باشید.')

@section('canonical', \App\Models\SeoPageSetting::forRoute('categories')?->canonical_url ?: route('categories.index'))

@section('robots', \App\Models\SeoPageSetting::forRoute('categories')?->robots() ?? \App\Models\SeoSetting::current()->robots())

@section('content')
    <div class="overflow-hidden bg-[var(--sg-cream)]">
        <section class="sg-category-intro relative overflow-hidden border-b border-[var(--sg-border)] bg-[var(--sg-white)]">
            <x-layout.container>
                <div class="py-10 sm:py-14 lg:py-20">
                    <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--sg-stone)]">
                        <a href="{{ route('home') }}" class="transition hover:text-[var(--sg-ink)]">خانه</a>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page" class="font-medium text-[var(--sg-ink)]">مجموعه‌ها</span>
                    </nav>

                    <div class="mt-10 grid items-end gap-8 lg:mt-14 lg:grid-cols-[minmax(0,1fr)_minmax(260px,0.62fr)] lg:gap-14">
                        <div class="max-w-3xl">
                            <p class="sg-eyebrow">CURATED FOR YOUR SPACE</p>
                            <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-[1.35] tracking-tight text-[var(--sg-ink)] sm:text-5xl lg:text-[3.75rem]">
                                هر فضا، داستان خودش را دارد.
                            </h1>
                            <p class="mt-5 max-w-2xl text-sm leading-8 text-[var(--sg-stone)] sm:text-base">
                                مجموعه‌ها را بر اساس نیاز و حال‌وهوای فضای خود مرور کنید. از هر دسته می‌توانید وارد محصولات واقعی فروشگاه شوید، جزئیات را ببینید و انتخابتان را با خیال راحت مقایسه کنید.
                            </p>

                            <div class="mt-8 flex flex-wrap items-center gap-3">
                                <a href="#category-collections" class="sg-button sg-button-primary">
                                    مرور مجموعه‌ها
                                    <span aria-hidden="true">↓</span>
                                </a>
                                <a href="{{ route('shop.index') }}" class="sg-button sg-button-secondary">
                                    مشاهده همه محصولات
                                    <span aria-hidden="true">←</span>
                                </a>
                            </div>
                        </div>

                        <aside class="sg-category-intro-panel relative overflow-hidden rounded-[2rem] bg-[var(--sg-ink)] p-6 text-white sm:p-8">
                            <span class="sg-category-intro-orbit sg-category-intro-orbit--one" aria-hidden="true"></span>
                            <span class="sg-category-intro-orbit sg-category-intro-orbit--two" aria-hidden="true"></span>
                            <div class="relative z-10">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-white/55">THE COLLECTIONS</p>
                                <p class="mt-6 text-5xl font-semibold leading-none tracking-tight sm:text-6xl">{{ number_format($categories->total()) }}</p>
                                <p class="mt-3 text-sm font-medium text-white/90">دسته‌بندی برای کشف انتخاب‌های تازه</p>
                                <div class="mt-7 flex items-center justify-between gap-3 border-t border-white/15 pt-5">
                                    <p class="max-w-[15rem] text-xs leading-7 text-white/60">هر مجموعه با تصویر، توضیح و تعداد محصولات خودش از اطلاعات فروشگاه ساخته می‌شود.</p>
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-white/20 bg-white/10 text-lg" aria-hidden="true">↙</span>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </x-layout.container>
        </section>

        <section id="category-collections" class="scroll-mt-24">
            <x-layout.container>
                <div class="py-12 sm:py-16 lg:py-20">
                    <div class="mb-8 flex flex-col gap-4 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p class="sg-eyebrow">BROWSE BY CATEGORY</p>
                            <h2 class="mt-3 text-2xl font-semibold tracking-tight text-[var(--sg-ink)] sm:text-3xl">از کدام فضا شروع کنیم؟</h2>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--sg-stone)]">برای دیدن محصولات هر مجموعه، روی کارت آن انتخاب کنید.</p>
                        </div>
                        <p class="text-xs text-[var(--sg-stone)]">
                            نمایش {{ number_format($categories->firstItem() ?? 0) }} تا {{ number_format($categories->lastItem() ?? 0) }}
                            از {{ number_format($categories->total()) }} مجموعه
                        </p>
                    </div>

                    @if($categories->isNotEmpty())
                        <div class="sg-category-grid sg-category-grid--index sg-category-index-grid">
                            @foreach($categories as $category)
                                <a
                                    href="{{ route('categories.show', $category->slug) }}"
                                    class="sg-category-index-tile group"
                                    aria-label="مشاهده مجموعه {{ $category->name }}، {{ number_format($category->products_count) }} محصول"
                                >
                                    <div class="sg-category-index-tile__media">
                                        @if($category->homepage_image_url)
                                            <img
                                                src="{{ $category->homepage_image_url }}"
                                                alt="{{ $category->image_alt ?: $category->name }}"
                                                width="1200"
                                                height="900"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        @else
                                            <div class="sg-category-index-tile__placeholder" aria-hidden="true">
                                                <span class="sillagallery-wordmark"><span class="sillagallery-wordmark__main">Silla</span><span class="sillagallery-wordmark__sub">Gallery</span></span>
                                            </div>
                                        @endif

                                        <span class="sg-category-index-tile__shade" aria-hidden="true"></span>

                                        <div class="sg-category-index-tile__top">
                                            <span class="sg-category-index-tile__eyebrow">
                                                {{ $loop->first && $categories->count() > 1 ? 'EXPLORE THE COLLECTION' : 'SILLAGALLERY COLLECTION' }}
                                            </span>
                                            <span class="sg-category-index-tile__count">{{ number_format($category->products_count) }} محصول</span>
                                        </div>

                                        <div class="sg-category-index-tile__content">
                                            <div class="min-w-0">
                                                @if($loop->first && $categories->count() > 1)
                                                    <p class="mb-2 text-[10px] font-medium uppercase tracking-[0.16em] text-white/70">پیشنهاد برای شروع</p>
                                                @endif
                                                <h3>{{ $category->name }}</h3>
                                                @if(filled($category->description))
                                                    <p class="sg-category-index-tile__description">{{ \Illuminate\Support\Str::limit(strip_tags($category->description), 110) }}</p>
                                                @endif
                                            </div>
                                            <span class="sg-category-index-tile__arrow" aria-hidden="true">←</span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        @if($categories->hasPages())
                            <div class="mt-10 sm:mt-12">
                                {{ $categories->withQueryString()->onEachSide(1)->links('pagination.silla') }}
                            </div>
                        @endif
                    @else
                        <div class="rounded-[2rem] border border-[var(--sg-border)] bg-[var(--sg-white)] px-6 py-20 text-center sm:py-24">
                            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--sg-surface)] text-xl text-[var(--sg-accent)]" aria-hidden="true">S</span>
                            <p class="sg-eyebrow mt-6">COLLECTIONS</p>
                            <h2 class="mt-3 text-2xl font-semibold text-[var(--sg-ink)]">هنوز مجموعه‌ای برای نمایش ثبت نشده است.</h2>
                            <p class="mx-auto mt-3 max-w-md text-sm leading-8 text-[var(--sg-stone)]">به‌زودی دسته‌بندی‌های فروشگاه در این بخش نمایش داده می‌شوند. در این فاصله می‌توانید محصولات موجود را مرور کنید.</p>
                            <a href="{{ route('shop.index') }}" class="sg-button sg-button-primary mt-7">مشاهده فروشگاه</a>
                        </div>
                    @endif
                </div>
            </x-layout.container>
        </section>

        <section class="border-t border-[var(--sg-border)] bg-[var(--sg-white)]">
            <x-layout.container>
                <div class="flex flex-col gap-6 py-10 sm:flex-row sm:items-center sm:justify-between sm:py-14">
                    <div>
                        <p class="sg-eyebrow">NEED A LITTLE HELP?</p>
                        <h2 class="mt-3 text-xl font-semibold text-[var(--sg-ink)] sm:text-2xl">برای انتخاب مناسب فضای خود راهنمایی می‌خواهید؟</h2>
                        <p class="mt-2 text-sm leading-7 text-[var(--sg-stone)]">تیم SillaGallery برای پاسخ به پرسش‌های شما در دسترس است.</p>
                    </div>
                    <a href="{{ route('contact') }}" class="sg-button sg-button-secondary shrink-0">ارتباط با ما <span aria-hidden="true">←</span></a>
                </div>
            </x-layout.container>
        </section>
    </div>
@endsection
