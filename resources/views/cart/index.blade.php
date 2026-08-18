@extends('layouts.app')

@section('title', 'سبد خرید | LIVORA')

@section('content')

    <x-layout.container>

        <div class="py-10">

            <h1 class="text-3xl font-semibold">
                سبد خرید
            </h1>

            @if($cart->items->count())

                <div class="mt-10 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">

                    <div class="space-y-6">

                        @foreach($cart->items as $item)

                            <article class="flex flex-col gap-5 border-b border-[var(--livora-border)] pb-6 sm:flex-row">

                                <div class="h-32 w-full shrink-0 overflow-hidden rounded-2xl sm:w-28">

                                    @if($item->product->images->first())

                                        <img
                                            src="{{ $item->product->images->first()->url }}"
                                            alt="{{ $item->product->name }}"
                                            class="h-full w-full object-cover"
                                        >

                                    @endif

                                </div>


                                <div class="flex-1">

                                    <div class="flex items-start justify-between gap-4">

                                        <div>

                                            <p class="text-xs text-[var(--livora-stone)]">
                                                {{ $item->product->category?->name }}
                                            </p>

                                            <a
                                                href="{{ route('product.show', $item->product->slug) }}"
                                                class="mt-1 block font-medium"
                                            >
                                                {{ $item->product->name }}
                                            </a>

                                            @if($item->variant)
                                                <p class="mt-2 text-xs text-[var(--livora-stone)]">
                                                    {{ $item->variant->name }}:
                                                    {{ $item->variant->value }}
                                                </p>
                                            @endif

                                        </div>


                                        <form
                                            action="{{ route('cart.remove', $item) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="text-sm text-red-700"
                                            >
                                                حذف
                                            </button>

                                        </form>

                                    </div>


                                    <div class="mt-6 flex items-center justify-between gap-4">

                                        <form
                                            action="{{ route('cart.update', $item) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <x-shop.quantity
                                                name="quantity"
                                                :value="$item->quantity"
                                                :max="max(1, $item->variant?->stock ?? $item->product->stock)"
                                            />
                                        </form>


                                        <span class="text-sm font-semibold">
                                            {{ number_format((float) $item->unit_price * $item->quantity) }}
                                            تومان
                                        </span>

                                    </div>

                                </div>

                            </article>

                        @endforeach

                    </div>


                    <aside>

                        <div class="sticky top-28 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                            <h2 class="font-semibold">
                                خلاصه سفارش
                            </h2>

                            <div class="mt-6 flex items-center justify-between">

                                <span class="text-sm text-[var(--livora-stone)]">
                                    مبلغ محصولات
                                </span>

                                <span class="font-semibold">
                                    {{ number_format($cart->subtotal()) }}
                                    تومان
                                </span>

                            </div>

                            <div class="my-5 h-px bg-[var(--livora-border)]"></div>

                            <div class="flex items-center justify-between">

                                <span class="font-medium">
                                    مجموع
                                </span>

                                <span class="text-xl font-semibold">
                                    {{ number_format($cart->subtotal()) }}
                                    تومان
                                </span>

                            </div>

                            <a
                                href="{{ route('checkout.index') }}"
                                class="mt-6 block rounded-xl bg-[var(--livora-ink)] px-6 py-3.5 text-center text-sm font-medium text-white hover:bg-[var(--livora-accent)]"
                            >
                                ادامه و پرداخت
                            </a>

                        </div>

                    </aside>

                </div>

            @else

                <div class="py-20 text-center">
                    <h2 class="text-xl font-semibold">
                        سبد خرید شما خالی است
                    </h2>

                    <a
                        href="{{ route('shop.index') }}"
                        class="mt-6 inline-flex rounded-xl bg-[var(--livora-ink)] px-6 py-3 text-sm text-white"
                    >
                        مشاهده فروشگاه
                    </a>
                </div>

            @endif

        </div>

    </x-layout.container>

@endsection
