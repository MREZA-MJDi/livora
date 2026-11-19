@extends('admin.layouts.app')

@section('title', 'محصولات')
@section('page_title', 'محصولات')

@section('content')
    <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <span class="text-xs font-medium text-[var(--admin-accent)]">CATALOG / PRODUCTS</span>
            <h2 class="admin-title mt-2">محصولات</h2>
            <p class="admin-subtitle mt-2">مدیریت محصولات، قیمت، موجودی و وضعیت انتشار</p>
            <p class="mt-2 text-xs text-[var(--admin-muted)]">انتخاب‌شده برای اسلایدر صفحه اصلی: <strong>{{ $homeHeroProductCount }}/10</strong></p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary">+ افزودن محصول</a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-5 flex flex-col gap-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-3 sm:flex-row">
        <label for="product-search" class="sr-only">جستجوی محصول</label>
        <input id="product-search" name="search" value="{{ request('search') }}" type="search" maxlength="120" placeholder="جستجو بر اساس نام یا SKU..." class="admin-input min-w-0 flex-1">
        <div class="flex gap-2">
            <button type="submit" class="admin-btn admin-btn-secondary">جستجو</button>
            @if(request()->filled('search'))
                <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-ghost">پاک‌کردن</a>
            @endif
        </div>
    </form>

    <div class="mb-3 flex items-center justify-between text-xs text-[var(--admin-muted)]">
        <span>نمایش {{ number_format($products->firstItem() ?? 0) }} تا {{ number_format($products->lastItem() ?? 0) }} از {{ number_format($products->total()) }} محصول</span>
        <span>۱۲ محصول در هر صفحه</span>
    </div>

    @if($products->isNotEmpty())
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 2xl:grid-cols-3">
            @foreach($products as $product)
                @php
                    $primaryImage = $product->images->first();
                    $hasVariantHeroImage = $product->variants->contains(fn ($variant) => $variant->images->isNotEmpty());
                    $canSelectForHero = $product->status === 'active'
                        && (bool) $product->category?->is_active
                        && ($primaryImage || $hasVariantHeroImage);
                    $heroSelectionDisabled = ! $product->is_home_hero
                        && (! $canSelectForHero || $homeHeroProductCount >= 10);
                @endphp
                <article class="min-w-0 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] shadow-sm">
                    <div class="flex min-w-0 gap-3 p-3">
                        <a href="{{ route('admin.products.show', $product) }}" class="h-24 w-20 shrink-0 overflow-hidden rounded-xl bg-[var(--admin-surface)]">
                            @if($primaryImage)
                                <img src="{{ $primaryImage->url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full items-center justify-center text-xs text-[var(--admin-muted)]">بدون تصویر</span>
                            @endif
                        </a>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <a href="{{ route('admin.products.show', $product) }}" class="min-w-0 flex-1 truncate text-sm font-semibold text-[var(--admin-text)] hover:text-[var(--admin-accent)]">{{ $product->name }}</a>
                                @switch($product->status)
                                    @case('active') <span class="admin-badge admin-badge-success">فعال</span> @break
                                    @case('draft') <span class="admin-badge admin-badge-warning">پیش‌نویس</span> @break
                                    @case('archived') <span class="admin-badge admin-badge-neutral">آرشیو</span> @break
                                    @default <span class="admin-badge admin-badge-neutral">{{ $product->status }}</span>
                                @endswitch
                            </div>
                            <p class="mt-1 truncate text-xs text-[var(--admin-muted)]">{{ $product->category?->name ?? 'بدون دسته‌بندی' }} <span class="mx-1">·</span> <span dir="ltr">{{ $product->sku }}</span></p>
                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs">
                                <span class="font-semibold text-[var(--admin-text)]">{{ number_format((float) $product->price) }} تومان</span>
                                <span class="{{ $product->stock > 0 ? 'text-emerald-700' : 'text-rose-700' }}">{{ $product->stock > 0 ? number_format($product->stock).' موجود' : 'ناموجود' }}</span>
                                @if($product->is_featured)<span class="admin-badge admin-badge-info">ویژه</span>@endif
                                @if($product->is_new)<span class="admin-badge admin-badge-success">جدید</span>@endif
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--admin-border)] px-3 py-2.5">
                        <form action="{{ route('admin.products.home-hero', $product) }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            <label class="inline-flex cursor-pointer items-center gap-2 text-xs text-[var(--admin-text)]">
                                <input type="checkbox" @checked($product->is_home_hero) @disabled($heroSelectionDisabled) onchange="this.form.submit()" class="h-4 w-4 rounded border-[var(--admin-border)] text-[var(--admin-accent)] focus:ring-[var(--admin-accent)]" aria-label="نمایش {{ $product->name }} در اسلایدر صفحه اصلی">
                                <span>اسلایدر خانه</span>
                            </label>
                            @if($product->is_home_hero)<span class="admin-badge admin-badge-success">انتخاب شده</span>@endif
                        </form>
                        <div class="flex items-center gap-1.5">
                            @if($product->status === 'active' && $product->category?->is_active)
                                <a href="{{ route('product.show', $product->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-ghost px-2.5 py-1.5 text-xs">سایت</a>
                            @endif
                            <a href="{{ route('admin.products.show', $product) }}" class="admin-btn admin-btn-ghost px-2.5 py-1.5 text-xs">جزئیات</a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn admin-btn-secondary px-2.5 py-1.5 text-xs">ویرایش</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('آیا از حذف این محصول مطمئن هستید؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger px-2.5 py-1.5 text-xs">حذف</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        @if($products->hasPages())
            <nav class="mt-6" aria-label="صفحه‌بندی محصولات">{{ $products->withQueryString()->onEachSide(1)->links() }}</nav>
        @endif
    @else
        <div class="admin-empty rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-10 text-center">
            <h3 class="text-sm font-semibold text-[var(--admin-text)]">{{ request()->filled('search') ? 'محصولی با این جستجو پیدا نشد.' : 'هنوز محصولی ثبت نشده است.' }}</h3>
            <p class="mt-2 text-xs text-[var(--admin-muted)]">عبارت جستجو را تغییر دهید یا اولین محصول را ایجاد کنید.</p>
            <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn-primary mt-5">افزودن محصول</a>
        </div>
    @endif
@endsection
