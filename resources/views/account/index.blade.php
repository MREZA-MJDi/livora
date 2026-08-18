@extends('layouts.app')

@section('title', 'حساب کاربری | LIVORA')

@section('content')

    <x-layout.container>

        <div class="py-10">

            <h1 class="text-3xl font-semibold">
                حساب کاربری
            </h1>

            <p class="mt-3 text-sm text-[var(--livora-stone)]">
                خوش آمدید، {{ $user->name }}
            </p>


            <div class="mt-10 grid gap-4 sm:grid-cols-3">

                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">
                    <p class="text-xs text-[var(--livora-stone)]">
                        کل سفارش‌ها
                    </p>
                    <p class="mt-3 text-2xl font-semibold">
                        {{ $stats['orders'] }}
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">
                    <p class="text-xs text-[var(--livora-stone)]">
                        در حال پردازش
                    </p>
                    <p class="mt-3 text-2xl font-semibold">
                        {{ $stats['processing_orders'] }}
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">
                    <p class="text-xs text-[var(--livora-stone)]">
                        علاقه‌مندی‌ها
                    </p>
                    <p class="mt-3 text-2xl font-semibold">
                        {{ $stats['wishlist'] }}
                    </p>
                </div>

            </div>


            <div class="mt-10 rounded-2xl border border-[var(--livora-border)]">

                <div class="border-b border-[var(--livora-border)] p-5">
                    <h2 class="font-semibold">
                        سفارش‌های اخیر
                    </h2>
                </div>

                @forelse($orders as $order)

                    <a
                        href="{{ route('account.orders.show', $order) }}"
                        class="flex items-center justify-between gap-4 border-b border-[var(--livora-border)] p-5 last:border-0 hover:bg-[var(--livora-cream)]"
                    >

                        <div>
                            <p class="text-sm font-medium">
                                {{ $order->order_number }}
                            </p>

                            <p class="mt-1 text-xs text-[var(--livora-stone)]">
                                {{ $order->items->count() }} محصول
                            </p>
                        </div>

                        <div class="text-left">

                            <x-ui.badge
                                :variant="$order->status === 'delivered' ? 'success' : 'warning'"
                            >
                                {{ $order->status }}
                            </x-ui.badge>

                            <p class="mt-2 text-sm font-semibold">
                                {{ number_format((float) $order->total) }}
                                تومان
                            </p>

                        </div>

                    </a>

                @empty

                    <div class="p-10 text-center text-sm text-[var(--livora-stone)]">
                        هنوز سفارشی ندارید.
                    </div>

                @endforelse

            </div>

        </div>

    </x-layout.container>

@endsection
