@extends('admin.layouts.app')

@section('title', $category->name)
@section('page_title', 'جزئیات دسته‌بندی')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / CATEGORIES / VIEW
            </div>

            <h2 class="admin-title">
                {{ $category->name }}
            </h2>

            <p class="admin-subtitle mt-2">
                جزئیات دسته‌بندی و وضعیت آن
            </p>

        </div>


        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('admin.categories.edit', $category) }}"
                class="admin-btn admin-btn-primary"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652l-9.193 9.193a4.5 4.5 0 0 1-1.897 1.13l-3.426.98.98-3.426a4.5 4.5 0 0 1 1.13-1.897l9.193-9.193Z" />
                </svg>

                ویرایش
            </a>

            <a
                href="{{ route('admin.categories.index') }}"
                class="admin-btn admin-btn-secondary"
            >
                بازگشت
            </a>

        </div>

    </div>


    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Image --}}
        <div class="xl:col-span-1">

            <div class="admin-card p-5">

                <div class="admin-image-preview aspect-square">

                    @if($category->image)

                        <img
                            src="{{ str_starts_with($category->image, 'http://') || str_starts_with($category->image, 'https://') || str_starts_with($category->image, '//')
                                                    ? $category->image
                                                    : asset('storage/' . ltrim($category->image, '/')) }}"
                            alt="{{ $category->name }}"
                            class="admin-image"
                        >

                    @else

                        <div class="flex h-full items-center justify-center text-[var(--admin-muted)]">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor"
                                 class="h-12 w-12">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z" />
                            </svg>

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- Information --}}
        <div class="space-y-6 xl:col-span-2">

            <div class="admin-card p-6">

                <div class="mb-6">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        اطلاعات دسته‌بندی
                    </h3>

                </div>


                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    <div>
                        <dt class="admin-stat-label">
                            نام
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ $category->name }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            Slug
                        </dt>

                        <dd class="mt-2 break-all font-mono text-sm text-[var(--admin-text-soft)]">
                            {{ $category->slug ?? '—' }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            ترتیب نمایش
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $category->sort_order ?? 0 }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            وضعیت
                        </dt>

                        <dd class="mt-2">

                            @if(isset($category->is_active) ? $category->is_active : true)

                                <span class="admin-badge admin-badge-success">
                                    فعال
                                </span>

                            @else

                                <span class="admin-badge admin-badge-danger">
                                    غیرفعال
                                </span>

                            @endif

                        </dd>
                    </div>

                </dl>

            </div>


            <div class="admin-card p-6">

                <div class="mb-5">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        توضیحات
                    </h3>

                </div>

                <div class="text-sm leading-8 text-[var(--admin-text-soft)]">

                    @if(filled($category->description ?? null))

                        {!! nl2br(e($category->description)) !!}

                    @else

                        <span class="text-[var(--admin-muted)]">
                            توضیحی برای این دسته‌بندی ثبت نشده است.
                        </span>

                    @endif

                </div>

            </div>


            <div class="admin-card p-6">

                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">

                    <div>
                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                            CATEGORY PRODUCTS
                        </p>

                        <h3 class="mt-2 text-base font-bold text-[var(--admin-text)]">
                            محصولات این دسته‌بندی
                        </h3>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            {{ number_format($products->total()) }} محصول ثبت شده در این دسته‌بندی.
                        </p>
                    </div>

                    <a
                        href="{{ route('admin.products.index', ['category' => $category->id]) }}"
                        class="admin-btn admin-btn-secondary"
                    >
                        مدیریت همه
                    </a>

                </div>

                @if($products->isNotEmpty())

                    <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">

                        @foreach($products as $product)

                            <a
                                href="{{ route('admin.products.show', $product) }}"
                                class="group flex items-center gap-3 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-3 transition hover:border-[var(--admin-accent)]"
                            >

                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-xl bg-[var(--admin-surface)]">

                                    @if($product->primaryImage?->url)

                                        <img
                                            src="{{ $product->primaryImage->url }}"
                                            alt="{{ $product->primaryImage->alt ?: $product->name }}"
                                            class="h-full w-full object-cover"
                                            loading="lazy"
                                        >

                                    @endif

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-semibold text-[var(--admin-text)] group-hover:text-[var(--admin-accent)]">
                                        {{ $product->name }}
                                    </p>

                                    <p class="mt-1 font-mono text-[10px] text-[var(--admin-muted)]">
                                        {{ $product->sku }}
                                    </p>

                                    <p class="mt-1 text-[10px] text-[var(--admin-muted)]">
                                        {{ number_format((float) $product->price) }} تومان
                                    </p>

                                </div>

                            </a>

                        @endforeach

                    </div>

                    @if($products->hasPages())

                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>

                    @endif

                @else

                    <div class="admin-empty mt-6">

                        <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                            محصولی در این دسته‌بندی نیست.
                        </h4>

                        <a
                            href="{{ route('admin.products.create') }}"
                            class="admin-btn admin-btn-primary mt-4"
                        >
                            افزودن محصول
                        </a>

                    </div>

                @endif

            </div>


            <div class="admin-card p-6">

                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                    <div>

                        <h3 class="text-sm font-bold text-[var(--admin-text)]">
                            حذف دسته‌بندی
                        </h3>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            این عملیات قابل بازگشت نیست.
                        </p>

                    </div>

                    <form
                        action="{{ route('admin.categories.destroy', $category) }}"
                        method="POST"
                        onsubmit="return confirm('آیا از حذف این دسته‌بندی مطمئن هستید؟');"
                    >

                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="admin-btn admin-btn-danger"
                        >
                            حذف دسته‌بندی
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

@endsection
