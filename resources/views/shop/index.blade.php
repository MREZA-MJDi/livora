@extends('layouts.app')

@section('title', \App\Models\SeoPageSetting::forRoute('shop')?->meta_title ?: 'فروشگاه مبلمان | SillaGallery')
@section('description', \App\Models\SeoPageSetting::forRoute('shop')?->meta_description ?: 'خرید مبلمان و لوازم خانه از SillaGallery؛ مجموعه محصولات، قیمت‌ها و شرایط خرید اقساطی.')
@section('canonical', \App\Models\SeoPageSetting::forRoute('shop')?->canonical_url ?: route('shop.index'))
@section('robots', \App\Models\SeoPageSetting::forRoute('shop')?->robots() ?? \App\Models\SeoSetting::current()->robots())

@push('styles')
    @vite('resources/css/shop.css')
@endpush

@section('content')
    <div class="sg-shop-page" x-data="{ filterOpen: false }">
        <header class="sg-shop-header">
            <div class="sg-shop-shell">
                <nav class="sg-shop-breadcrumb" aria-label="مسیر صفحه">
                    <a href="{{ route('home') }}">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">فروشگاه</span>
                </nav>

                <div class="sg-shop-header__content">
                    <div>
                        <p class="sg-shop-eyebrow">SILLAGALLERY / COLLECTION</p>
                        <h1 class="sg-shop-title">فروشگاه</h1>
                        <p class="sg-shop-description">
                            مجموعه مبلمان و عناصر خانه؛ محصولات را بر اساس دسته‌بندی، قیمت و شرایط خرید بررسی کنید.
                        </p>
                    </div>
                    <p class="sg-shop-total">
                        <strong>{{ number_format($products->total()) }}</strong>
                        <span>محصول</span>
                    </p>
                </div>
            </div>
        </header>

        <div class="sg-shop-shell sg-shop-workspace">
            {{-- LEFT CONTROL RAIL: live categories and actual GET filters --}}
            <aside class="sg-shop-sidebar" aria-label="دسته‌بندی و فیلتر محصولات">
                <section class="sg-shop-control-group">
                    <div class="sg-shop-control-heading">
                        <span class="sg-shop-eyebrow">COLLECTIONS</span>
                        <h2>دسته‌بندی‌ها</h2>
                    </div>

                    @php
                        $shopBaseQuery = request()->except(['category', 'page']);
                        $selectedCategorySlug = request('category');
                    @endphp

                    <nav class="sg-shop-category-list" aria-label="دسته‌بندی محصولات">
                        <a
                            href="{{ route('shop.index', $shopBaseQuery) }}"
                            class="sg-shop-category-link {{ blank($selectedCategorySlug) ? 'is-active' : '' }}"
                            @if(blank($selectedCategorySlug)) aria-current="page" @endif
                        >
                            <span class="sg-shop-category-image sg-shop-category-image--all" aria-hidden="true">SG</span>
                            <span class="sg-shop-category-copy">
                                <span class="sg-shop-category-name">همه محصولات</span>
                                <span class="sg-shop-category-count">مشاهده مجموعه کامل</span>
                            </span>
                        </a>

                        @foreach($categories as $category)
                            @php
                                $categoryImage = $category->homepage_image_url;
                                $categoryIsActive = $selectedCategorySlug === $category->slug;
                                $categoryQuery = array_merge($shopBaseQuery, ['category' => $category->slug]);
                            @endphp

                            <a
                                href="{{ route('shop.index', $categoryQuery) }}"
                                class="sg-shop-category-link {{ $categoryIsActive ? 'is-active' : '' }}"
                                @if($categoryIsActive) aria-current="page" @endif
                            >
                                <span class="sg-shop-category-image">
                                    @if($categoryImage)
                                        <img src="{{ $categoryImage }}" alt="" loading="lazy" decoding="async">
                                    @else
                                        <span class="sg-shop-category-placeholder">SG</span>
                                    @endif
                                </span>
                                <span class="sg-shop-category-copy">
                                    <span class="sg-shop-category-name">{{ $category->name }}</span>
                                    <span class="sg-shop-category-count">{{ number_format($category->products_count) }} محصول</span>
                                </span>
                            </a>
                        @endforeach
                    </nav>
                </section>

                <section class="sg-shop-filter-panel">
                    <div class="sg-shop-control-heading">
                        <span class="sg-shop-eyebrow">REFINE</span>
                        <div class="sg-shop-filter-heading-row">
                            <h2>فیلترها</h2>
                            <a href="{{ route('shop.index') }}">پاک کردن</a>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('shop.index') }}" class="sg-shop-filter-form">
                        @if(request()->filled('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request()->filled('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif
                        <x-shop.filters
                            :categories="$categories"
                            :selected-category="request('category')"
                            :show-categories="false"
                        />
                    </form>
                </section>
            </aside>

            {{-- CENTRAL CATALOG --}}
            <section class="sg-shop-main" aria-label="محصولات فروشگاه">
                <div class="sg-shop-toolbar">
                    <div class="sg-shop-toolbar__meta">
                        <p class="sg-shop-eyebrow">CURATED OBJECTS</p>
                        <p class="sg-shop-result-count">
                            @if($products->total() > 0)
                                نمایش {{ number_format($products->firstItem()) }} تا {{ number_format($products->lastItem()) }}
                                از {{ number_format($products->total()) }} محصول
                            @else
                                نتیجه‌ای برای نمایش وجود ندارد
                            @endif
                        </p>
                    </div>

                    <div class="sg-shop-toolbar__actions">
                        <button
                            type="button"
                            class="sg-shop-mobile-filter"
                            @click="filterOpen = true"
                            aria-haspopup="dialog"
                        >
                            <span aria-hidden="true">☷</span>
                            فیلترها
                        </button>
                        <div class="sg-shop-sort">
                            <x-shop.sorting :products="$products" />
                        </div>
                    </div>
                </div>

                @if(request()->filled('category') || request()->filled('search') || request()->filled('min_price') || request()->filled('max_price') || request()->boolean('in_stock') || request()->boolean('installment') || request()->boolean('featured') || request()->boolean('new'))
                    <div class="sg-shop-active-filters" aria-label="فیلترهای فعال">
                        @if(request()->filled('category'))
                            @php $activeCategory = $categories->firstWhere('slug', request('category')); @endphp
                            <span>{{ $activeCategory?->name ?? request('category') }}</span>
                        @endif
                        @if(request()->filled('search')) <span>جستجو: {{ request('search') }}</span> @endif
                        @if(request()->boolean('in_stock')) <span>موجود</span> @endif
                        @if(request()->boolean('installment')) <span>خرید اقساطی</span> @endif
                        @if(request()->boolean('featured')) <span>ویژه</span> @endif
                        @if(request()->boolean('new')) <span>جدید</span> @endif
                        <a href="{{ route('shop.index') }}">حذف همه</a>
                    </div>
                @endif

                @if($products->isNotEmpty())
                    <div
                        class="sg-shop-grid grid--type-a"
                        data-shop-grid
                        data-shop-grid-effect="Amun"
                        aria-label="فهرست محصولات"
                    >
                        <div class="grid__sizer" aria-hidden="true"></div>

                        @foreach($products as $product)
                            <x-product.card
                                :product="$product"
                                shop-grid-item
                                :image-loading="$loop->index < 4 ? 'eager' : 'lazy'"
                                :image-priority="$loop->index < 2 ? 'high' : 'auto'"
                            />
                        @endforeach
                    </div>

                    @if($products->hasPages())
                        <nav class="sg-shop-pagination" aria-label="صفحه‌بندی محصولات">
                            {{ $products->withQueryString()->links() }}
                        </nav>
                    @endif
                @else
                    <section class="sg-shop-empty-state">
                        <span class="sg-shop-empty-mark" aria-hidden="true">SG</span>
                        <p class="sg-shop-eyebrow">NO MATCHING PRODUCTS</p>
                        <h2>محصولی با این مشخصات پیدا نشد.</h2>
                        <p>فیلترها را تغییر دهید یا همه محصولات فروشگاه را مشاهده کنید.</p>
                        <a href="{{ route('shop.index') }}">مشاهده همه محصولات</a>
                    </section>
                @endif
            </section>

            {{-- RIGHT CONTROL RAIL: the three original grid geometries --}}
            <aside class="sg-shop-layout-panel" aria-label="انتخاب چیدمان محصولات">
                <div class="sg-shop-control-heading">
                    <span class="sg-shop-eyebrow">VIEW</span>
                    <h2>چیدمان</h2>
                </div>

                <div class="sg-shop-layout-options" role="group" aria-label="نوع چیدمان شبکه">
                    <button type="button" class="sg-shop-layout-option" data-shop-grid-layout="a" aria-pressed="true">
                        <span class="sg-shop-layout-icon sg-shop-layout-icon--a" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                        <span>Grid A</span>
                    </button>
                    <button type="button" class="sg-shop-layout-option" data-shop-grid-layout="b" aria-pressed="false">
                        <span class="sg-shop-layout-icon sg-shop-layout-icon--b" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></span>
                        <span>Grid B</span>
                    </button>
                    <button type="button" class="sg-shop-layout-option" data-shop-grid-layout="c" aria-pressed="false">
                        <span class="sg-shop-layout-icon sg-shop-layout-icon--c" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                        <span>Grid C</span>
                    </button>
                </div>

                <p class="sg-shop-layout-hint">
                    تراکم و فاصله تصاویر را متناسب با سلیقه خود تغییر دهید.
                </p>
            </aside>
        </div>

        <x-shop.filter-drawer
            :categories="$categories"
            :selected-category="request('category')"
            :show-categories="false"
        />
    </div>
@endsection
