@extends('layouts.app')

@section('title', 'جزئیات سفارش | LIVORA')

@section('content')

    <x-layout.container>

        <div class="py-10">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs text-[var(--livora-stone)]">
                        شماره سفارش
                    </p>

                    <h1 class="mt-1 text-3xl font-semibold">
                        {{ $order->order_number }}
                    </h1>

                </div>

                <x-ui.badge
                    :variant="$order->status === 'delivered' ? 'success' : 'warning'"
                >
                    {{ $order->status }}
                </x-ui.badge>

            </div>


            <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">

                <div class="space-y-6">

                    @foreach($order->items as $item)

                        <article class="flex gap-4 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                            <div class="h-24 w-20 shrink-0 overflow-hidden rounded-xl">

                                @if($item->product?->images->first())

                                    <img
                                        src="{{ $item->product->images->first()->url }}"
                                        alt="{{ $item->product_name }}"
                                        class="h-full w-full object-cover"
                                    >

                                @endif

                            </div>

                            <div class="flex-1">

                                <h2 class="font-medium">
                                    {{ $item->product_name }}
                                </h2>

                                <p class="mt-2 text-xs text-[var(--livora-stone)]">
                                    تعداد: {{ $item->quantity }}
                                </p>

                                <p class="mt-3 text-sm font-semibold">
                                    {{ number_format((float) $item->total) }}
                                    تومان
                                </p>

                            </div>

                        </article>

                    @endforeach


                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                        <h2 class="font-semibold">
                            آدرس تحویل
                        </h2>

                        <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                            {{ $order->province }}،
                            {{ $order->city }}،
                            {{ $order->address }}
                            @if($order->unit)
                                ، واحد {{ $order->unit }}
                            @endif
                        </p>

                        <p class="mt-2 text-xs text-[var(--livora-stone)]">
                            {{ $order->postal_code }}
                        </p>

                    </div>

                </div>


                <aside>

                    <div class="sticky top-28 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6">

                        <h2 class="font-semibold">
                            خلاصه سفارش
                        </h2>

                        <div class="mt-6 space-y-4 text-sm">

                            <div class="flex justify-between">
                                <span class="text-[var(--livora-stone)]">
                                    مبلغ محصولات
                                </span>

                                <span>
                                    {{ number_format((float) $order->subtotal) }}
                                    تومان
                                </span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-[var(--livora-stone)]">
                                    ارسال
                                </span>

                                <span>
                                    {{ number_format((float) $order->shipping_cost) }}
                                    تومان
                                </span>
                            </div>

                            <div class="border-t border-[var(--livora-border)] pt-4 flex justify-between font-semibold">
                                <span>
                                    مجموع
                                </span>

                                <span>
                                    {{ number_format((float) $order->total) }}
                                    تومان
                                </span>
                            </div>

                        </div>

                        @if($order->latestPayment)

                            <div class="mt-6 rounded-xl bg-[var(--livora-cream)] p-4">

                                <p class="text-xs text-[var(--livora-stone)]">
                                    وضعیت پرداخت
                                </p>

                                <p class="mt-2 text-sm font-medium">
                                    {{ $order->latestPayment->status }}
                                </p>

                            </div>

                        @endif

                    </div>

                </aside>

            </div>

        </div>

    </x-layout.container>

@endsection
