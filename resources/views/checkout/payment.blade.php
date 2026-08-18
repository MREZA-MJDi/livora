@extends('layouts.app')

@section('title', 'پرداخت ' . $order->order_number . ' | LIVORA')

@section('content')

    <x-layout.container>

        <div class="mx-auto max-w-2xl py-16">

            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                <div class="flex items-center justify-between">

                    <div>
                        <p class="text-xs text-[var(--livora-stone)]">
                            سفارش
                        </p>

                        <p class="mt-1 font-semibold">
                            {{ $order->order_number }}
                        </p>
                    </div>

                    <x-ui.badge variant="warning">
                        {{ $order->payment_status }}
                    </x-ui.badge>

                </div>

                <div class="my-8 h-px bg-[var(--livora-border)]"></div>

                <div class="flex items-center justify-between">

                    <span class="text-sm text-[var(--livora-stone)]">
                        مبلغ قابل پرداخت
                    </span>

                    <span class="text-xl font-semibold">
                        {{ number_format((float) $order->total) }}
                        تومان
                    </span>

                </div>

                <form
                    action="#"
                    method="POST"
                    class="mt-8"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white hover:bg-[var(--livora-accent)]"
                    >
                        ادامه به درگاه
                    </button>

                </form>

            </div>

        </div>

    </x-layout.container>

@endsection
