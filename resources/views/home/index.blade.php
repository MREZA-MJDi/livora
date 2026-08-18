@extends('layouts.app')

@section('title', 'LIVORA | Furniture & Living')

@section('description', 'LIVORA — Furniture & Living')

@section('content')

    <section class="overflow-hidden">

        <x-layout.container>

            <div class="grid min-h-[70vh] items-center gap-10 py-16 lg:grid-cols-2">

                <div>

                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                        Furniture & Living
                    </p>

                    <h1 class="mt-4 text-5xl font-semibold tracking-tight text-[var(--livora-ink)] sm:text-6xl">
                        انتخابی برای
                        <span class="text-[var(--livora-accent)]">
                            ماندن
                        </span>
                    </h1>

                    <p class="mt-6 max-w-xl text-sm leading-8 text-[var(--livora-stone)]">
                        مجموعه‌ای از محصولات منتخب LIVORA برای فضاهایی که قرار است شخصیت داشته باشند.
                    </p>

                    <a
                        href="{{ route('shop.index') }}"
                        class="mt-8 inline-flex rounded-xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white hover:bg-[var(--livora-accent)]"
                    >
                        مشاهده فروشگاه
                    </a>

                </div>

                <div class="overflow-hidden rounded-3xl bg-[var(--livora-white)]">
                    @if($featuredProducts->first()?->images->first())
                        <img
                            src="{{ $featuredProducts->first()->images->first()->url }}"
                            alt="{{ $featuredProducts->first()->name }}"
                            class="aspect-[4/5] h-full w-full object-cover"
                        >
                    @endif
                </div>

            </div>

        </x-layout.container>

    </section>


    <section class="border-t border-[var(--livora-border)]">

        <x-layout.container>

            <div class="py-16">

                <div class="mb-8">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        CATEGORIES
                    </p>

                    <h2 class="mt-3 text-2xl font-semibold">
                        دسته‌بندی‌ها
                    </h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">

                    @foreach($categories as $category)

                        @if($category->image)

                            <x-shop.category-card
                                :name="$category->name"
                                :image="$category->image"
                                :href="route('shop.index', ['category' => $category->slug])"
                                :count="$category->products_count"
                            />

                        @endif

                    @endforeach

                </div>

            </div>

        </x-layout.container>

    </section>


    <section class="border-t border-[var(--livora-border)]">

        <x-layout.container>

            <div class="py-16">

                <div class="mb-8 flex items-end justify-between">

                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                            FEATURED
                        </p>

                        <h2 class="mt-3 text-2xl font-semibold">
                            محصولات منتخب
                        </h2>
                    </div>

                    <a
                        href="{{ route('shop.index') }}"
                        class="text-sm text-[var(--livora-accent)]"
                    >
                        مشاهده همه
                    </a>

                </div>

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

                    @foreach($featuredProducts as $product)

                        <x-product.card
                            :product="$product"
                        />

                    @endforeach

                </div>

            </div>

        </x-layout.container>

    </section>


    <section class="border-t border-[var(--livora-border)]">

        <x-layout.container>

            <div class="py-16">

                <div class="mb-8">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        NEW
                    </p>

                    <h2 class="mt-3 text-2xl font-semibold">
                        تازه‌های LIVORA
                    </h2>
                </div>

                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

                    @foreach($newProducts as $product)

                        <x-product.card
                            :product="$product"
                        />

                    @endforeach

                </div>

            </div>

        </x-layout.container>

    </section>

@endsection
