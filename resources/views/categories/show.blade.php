@extends('layouts.app')

@section('title', $category->name . ' | LIVORA')

@section('description', $category->description ?: 'محصولات ' . $category->name . ' در LIVORA')

@section('content')

    <section class="border-b border-[var(--livora-border)]">
        <x-layout.container>

            <div class="py-10 sm:py-14">

                <div class="text-sm text-[var(--livora-stone)]">
                    <a href="{{ route('home') }}">
                        خانه
                    </a>

                    <span class="mx-2">/</span>

                    <a href="{{ route('categories.index') }}">
                        دسته‌بندی‌ها
                    </a>

                    <span class="mx-2">/</span>

                    <span class="text-[var(--livora-ink)]">
                        {{ $category->name }}
                    </span>
                </div>

                <h1 class="mt-5 text-3xl font-semibold text-[var(--livora-ink)] sm:text-4xl">
                    {{ $category->name }}
                </h1>

                @if($category->description)
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-[var(--livora-stone)]">
                        {{ $category->description }}
                    </p>
                @endif

            </div>

        </x-layout.container>
    </section>


    <section>
        <x-layout.container>

            <div class="py-10 lg:py-14">

                @if($products->count())

                    <div class="mb-8 flex items-center justify-between gap-4">

                        <p class="text-sm text-[var(--livora-stone)]">
                            {{ $products->total() }} محصول
                        </p>

                        <a
                            href="{{ route('shop.index') }}"
                            class="text-sm font-medium text-[var(--livora-accent)]"
                        >
                            مشاهده فروشگاه
                        </a>

                    </div>


                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

                        @foreach($products as $product)

                            <x-product.card
                                :product="$product"
                            />

                        @endforeach

                    </div>


                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>

                @else

                    <div class="py-20 text-center">

                        <h2 class="text-xl font-semibold text-[var(--livora-ink)]">
                            محصولی در این دسته‌بندی وجود ندارد
                        </h2>

                        <a
                            href="{{ route('shop.index') }}"
                            class="mt-6 inline-flex rounded-xl bg-[var(--livora-ink)] px-6 py-3 text-sm font-medium text-white"
                        >
                            بازگشت به فروشگاه
                        </a>

                    </div>

                @endif

            </div>

        </x-layout.container>
    </section>

@endsection
