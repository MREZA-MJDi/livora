@extends('layouts.app')

@section('title', 'فروشگاه | LIVORA')

@section('description', 'فروشگاه LIVORA — مجموعه مبلمان و لوازم دکوراسیون')

@section('content')

    <section class="border-b border-[var(--livora-border)]">

        <x-layout.container>

            <div class="py-10 sm:py-14">

                <p class="mb-3 text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    LIVORA COLLECTION
                </p>

                <h1 class="text-3xl font-semibold text-[var(--livora-ink)] sm:text-4xl">
                    فروشگاه
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-[var(--livora-stone)]">
                    مجموعه‌ای منتخب از مبلمان و اکسسوری‌های LIVORA برای ساختن فضایی متفاوت.
                </p>

            </div>

        </x-layout.container>

    </section>


    <section>

        <x-layout.container>

            <form
                method="GET"
                action="{{ route('shop.index') }}"
            >

                <div class="grid gap-10 py-10 lg:grid-cols-[260px_minmax(0,1fr)] lg:py-14">

                    <aside class="hidden lg:block">

                        <div class="sticky top-28">

                            <div class="mb-6 flex items-center justify-between">

                                <h2 class="text-sm font-semibold">
                                    فیلترها
                                </h2>

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="text-xs text-[var(--livora-stone)] hover:text-[var(--livora-accent)]"
                                >
                                    پاک کردن
                                </a>

                            </div>

                            <x-shop.filters
                                :categories="$categories"
                                :selected-category="request('category')"
                            />

                            <button
                                type="submit"
                                class="mt-6 w-full rounded-xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-medium text-white hover:bg-[var(--livora-accent)]"
                            >
                                اعمال فیلتر
                            </button>

                        </div>

                    </aside>


                    <div class="min-w-0">

                        <div class="mb-6 flex items-center justify-between lg:hidden">

                            <button
                                type="button"
                                @click="filterOpen = true"
                                class="rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-2.5 text-sm"
                            >
                                فیلترها
                            </button>

                            <span class="text-sm text-[var(--livora-stone)]">
                                {{ $products->total() }} محصول
                            </span>

                        </div>


                        <div class="mb-8">

                            <x-shop.sorting
                                :products="$products"
                            />

                        </div>


                        @if($products->count())

                            <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">

                                @foreach($products as $product)

                                    <x-product.card
                                        :product="$product"
                                    />

                                @endforeach

                            </div>

                            <div class="mt-14">
                                {{ $products->links() }}
                            </div>

                        @else

                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] py-20 text-center">

                                <h2 class="text-xl font-semibold text-[var(--livora-ink)]">
                                    محصولی پیدا نشد
                                </h2>

                                <p class="mt-3 text-sm text-[var(--livora-stone)]">
                                    فیلترها یا عبارت جستجو را تغییر دهید.
                                </p>

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="mt-6 inline-flex rounded-xl bg-[var(--livora-ink)] px-6 py-3 text-sm font-medium text-white"
                                >
                                    مشاهده همه محصولات
                                </a>

                            </div>

                        @endif

                    </div>

                </div>

            </form>

        </x-layout.container>

    </section>

@endsection
