@extends('layouts.app')

@section('title', 'سفارش‌های من | LIVORA')

@section('content')

    <x-layout.container>

        <div class="py-10">

            <h1 class="text-3xl font-semibold">
                سفارش‌های من
            </h1>

            <div class="mt-8 space-y-5">

                @forelse($orders as $order)

                    <article class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)]">

                        <div class="flex flex-col gap-4 border-b border-[var(--livora-border)] p-5 sm:flex-row sm:items-center sm:justify-between">

                            <div>
                                <p class="text-xs text-[var(--livora-stone)]">
                                    شماره سفارش
                                </p>

                                <p class="mt-1 font-semibold">
                                    {{ $order->order_number }}
                                </p>
                            </div>

                            <x-ui.badge
                                :variant="$order->status === 'delivered' ? 'success' : 'warning'"
                            >
                                {{ $order->status }}
                            </x-ui.badge>

                        </div>


                        <div class="grid gap-5 p-5 sm:grid-cols-3">

                            <div>
                                <p class="text-xs text-[var(--livora-stone)]">
                                    تاریخ
                                </p>

                                <p class="mt-2 text-sm">
                                    {{ $order->created_at->format('Y/m/d') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-[var(--livora-stone)]">
                                    محصولات
                                </p>

                                <p class="mt-2 text-sm">
                                    {{ $order->items->sum('quantity') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-[var(--livora-stone)]">
                                    مبلغ
                                </p>

                                <p class="mt-2 text-sm font-semibold">
                                    {{ number_format((float) $order->total) }}
                                    تومان
                                </p>
                            </div>

                        </div>


                        <div class="border-t border-[var(--livora-border)] p-5">

                            <a
                                href="{{ route('account.orders.show', $order) }}"
                                class="text-sm font-medium text-[var(--livora-accent)]"
                            >
                                مشاهده جزئیات
                            </a>

                        </div>

                    </article>

                @empty

                    <div class="py-20 text-center text-sm text-[var(--livora-stone)]">
                        سفارشی پیدا نشد.
                    </div>

                @endforelse

            </div>


            <div class="mt-10">
                {{ $orders->links() }}
            </div>

        </div>

    </x-layout.container>

@endsection
