@extends('layouts.app')

@section('title', 'دسته‌بندی‌ها | LIVORA')

@section('description', 'دسته‌بندی محصولات LIVORA')

@section('content')

    <section class="border-b border-[var(--livora-border)]">
        <x-layout.container>

            <div class="py-10 sm:py-14">

                <p class="text-xs font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    LIVORA COLLECTION
                </p>

                <h1 class="mt-3 text-3xl font-semibold text-[var(--livora-ink)] sm:text-4xl">
                    دسته‌بندی‌ها
                </h1>

                <p class="mt-3 max-w-xl text-sm leading-7 text-[var(--livora-stone)]">
                    مجموعه‌های LIVORA را بر اساس نوع محصول کشف کنید.
                </p>

            </div>

        </x-layout.container>
    </section>


    <section>
        <x-layout.container>

            <div class="py-10 lg:py-14">

                @if($categories->count())

                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

                        @foreach($categories as $category)

                            <a
                                href="{{ route('categories.show', $category) }}"
                                class="group overflow-hidden rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)]"
                            >

                                <div class="aspect-[4/3] overflow-hidden bg-[var(--livora-cream)]">

                                    @if($category->image)

                                        <img
                                            src="{{ asset('storage/' . ltrim($category->image, '/')) }}"
                                            alt="{{ $category->name }}"
                                            loading="lazy"
                                            class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                        >

                                    @else

                                        <div class="flex h-full items-center justify-center text-sm text-[var(--livora-stone)]">
                                            {{ $category->name }}
                                        </div>

                                    @endif

                                </div>

                                <div class="flex items-center justify-between gap-4 p-5">

                                    <div>

                                        <h2 class="text-lg font-semibold text-[var(--livora-ink)]">
                                            {{ $category->name }}
                                        </h2>

                                        <p class="mt-1 text-xs text-[var(--livora-stone)]">
                                            {{ $category->products_count }} محصول
                                        </p>

                                    </div>

                                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--livora-border)] transition-all duration-300 group-hover:border-[var(--livora-accent)] group-hover:bg-[var(--livora-accent)] group-hover:text-white">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="h-4 w-4"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
                                            />
                                        </svg>

                                    </span>

                                </div>

                            </a>

                        @endforeach

                    </div>

                @else

                    <div class="py-20 text-center text-sm text-[var(--livora-stone)]">
                        دسته‌بندی‌ای برای نمایش وجود ندارد.
                    </div>

                @endif

            </div>

        </x-layout.container>
    </section>

@endsection
