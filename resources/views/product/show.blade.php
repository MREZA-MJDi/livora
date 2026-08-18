@extends('layouts.app')

@section('title', $product->name . ' | LIVORA')

@section('description', $product->meta_description ?: $product->short_description)

@section('content')

    <section class="border-b border-[var(--livora-border)]">

        <x-layout.container>

            <div class="py-5 text-sm text-[var(--livora-stone)]">

                <a href="{{ route('home') }}">
                    خانه
                </a>

                <span class="mx-2">/</span>

                <a href="{{ route('shop.index') }}">
                    فروشگاه
                </a>

                <span class="mx-2">/</span>

                <span class="text-[var(--livora-ink)]">
                    {{ $product->name }}
                </span>

            </div>

        </x-layout.container>

    </section>


    <section>

        <x-layout.container>

            <div class="grid gap-10 py-10 lg:grid-cols-2 lg:gap-16 lg:py-16">

                <x-product.gallery :product="$product" />


                <div>

                    @if($product->is_new)
                        <x-ui.badge
                            variant="accent"
                            class="mb-5"
                        >
                            جدید
                        </x-ui.badge>
                    @endif

                    <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                        {{ $product->category?->name }}
                    </p>

                    <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)] sm:text-4xl">
                        {{ $product->name }}
                    </h1>

                    <div class="mt-6">
                        <x-product.price :product="$product" />
                    </div>

                    <div class="my-8 h-px bg-[var(--livora-border)]"></div>

                    @if($product->short_description)
                        <p class="text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $product->short_description }}
                        </p>
                    @endif


                    <form
                        action="{{ route('cart.add', $product) }}"
                        method="POST"
                        class="mt-8 space-y-7"
                    >
                        @csrf

                        @foreach($product->variants->groupBy('type') as $type => $variants)

                            <x-product.variant-selector
                                :title="$variants->first()->name"
                                :options="$variants"
                                :name="$type"
                            />

                        @endforeach


                        <div class="flex items-center justify-between">

                            <span class="text-sm font-medium">
                                تعداد
                            </span>

                            <x-shop.quantity
                                name="quantity"
                                min="1"
                                :max="max(1, $product->stock)"
                            />

                        </div>


                        <button
                            type="submit"
                            @if($product->stock < 1) disabled @endif
                            class="w-full rounded-xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{ $product->stock > 0 ? 'افزودن به سبد' : 'ناموجود' }}
                        </button>

                    </form>


                    <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3">

                        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">
                            <p class="text-xs text-[var(--livora-stone)]">
                                موجودی
                            </p>

                            <p class="mt-2 text-sm font-medium">
                                {{ $product->stock }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">
                            <p class="text-xs text-[var(--livora-stone)]">
                                وضعیت
                            </p>

                            <p class="mt-2 text-sm font-medium">
                                {{ $product->status === 'active' ? 'موجود' : 'غیرفعال' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4">
                            <p class="text-xs text-[var(--livora-stone)]">
                                SKU
                            </p>

                            <p class="mt-2 text-sm font-medium">
                                {{ $product->sku }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>

    </section>


    <section class="border-t border-[var(--livora-border)]">

        <x-layout.container>

            <div class="grid gap-10 py-14 lg:grid-cols-3">

                <div>

                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                        DETAILS
                    </p>

                    <h2 class="mt-3 text-2xl font-semibold">
                        توضیحات محصول
                    </h2>

                </div>

                <div class="lg:col-span-2">

                    <div class="text-sm leading-8 text-[var(--livora-stone)]">
                        {!! nl2br(e($product->description)) !!}
                    </div>

                </div>

            </div>

        </x-layout.container>

    </section>


    @if($relatedProducts->count())

        <section class="border-t border-[var(--livora-border)]">

            <x-layout.container>

                <div class="py-14">

                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold">
                            محصولات مرتبط
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

                        @foreach($relatedProducts as $relatedProduct)

                            <x-product.card
                                :product="$relatedProduct"
                            />

                        @endforeach

                    </div>

                </div>

            </x-layout.container>

        </section>

    @endif

@endsection
