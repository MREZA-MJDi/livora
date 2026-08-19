@extends('layouts.app')

@section('title', 'پرداخت سفارش ' . $order->order_number . ' | LIVORA')

@section(
    'description',
    'انتخاب روش پرداخت و بررسی شرایط خرید سفارش ' . $order->order_number . ' در LIVORA.'
)

@section('content')

    @php
        /*
         |--------------------------------------------------------------------------
         | Order / Installment Data
         |--------------------------------------------------------------------------
         */

        $order->loadMissing([
            'items.product.images',
            'installments',
        ]);

        $installmentAvailable = $order->items->isNotEmpty()
            && $order->items->every(
                fn ($item) =>
                    $item->product
                    && $item->product->installment_enabled
            );

        $installmentProduct = $order->items
            ->first()?->product;

        $cashPercent = $installmentAvailable
            ? (int) ($installmentProduct?->installment_cash_percent ?? 0)
            : 0;

        $chequeCount = $installmentAvailable
            ? (int) ($installmentProduct?->installment_cheque_count ?? 0)
            : 0;

        $intervalMonths = $installmentAvailable
            ? (int) ($installmentProduct?->installment_interval_months ?? 0)
            : 0;

        $total = (float) $order->total;

        $cashAmount = $installmentAvailable && $cashPercent > 0
            ? round(
                $total * ($cashPercent / 100),
                2
            )
            : 0;

        $deferredAmount = $installmentAvailable
            ? round(
                $total - $cashAmount,
                2
            )
            : 0;

        $chequeAmounts = [];

        if (
            $installmentAvailable
            && $chequeCount > 0
            && $deferredAmount > 0
        ) {
            $baseAmount = round(
                $deferredAmount / $chequeCount,
                2
            );

            $distributed = 0;

            for ($i = 1; $i <= $chequeCount; $i++) {
                if ($i === $chequeCount) {
                    $amount = round(
                        $deferredAmount - $distributed,
                        2
                    );
                } else {
                    $amount = $baseAmount;
                }

                $chequeAmounts[] = $amount;
                $distributed = round(
                    $distributed + $amount,
                    2
                );
            }
        }

        $hasInternalPlan = $order->installments->isNotEmpty();

        $paymentStatusLabel = match ($order->payment_status) {
            'paid' => 'پرداخت شده',
            'failed' => 'ناموفق',
            'refunded' => 'مرجوع شده',
            default => 'در انتظار پرداخت',
        };
    @endphp

    <div class="bg-[var(--livora-surface)]">

        {{-- Checkout Header --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
            <x-layout.container>

                <div class="py-8 sm:py-10">

                    <div class="flex flex-wrap items-center gap-2 text-xs text-[var(--livora-stone)]">

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('cart.index') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            سبد خرید
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('checkout.index') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            اطلاعات سفارش
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        پرداخت
                    </span>

                    </div>

                    <div class="mt-6 max-w-2xl">

                        <p class="text-[11px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            CHECKOUT
                        </p>

                        <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">
                            انتخاب روش پرداخت
                        </h1>

                        <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                            سفارش شما آماده پرداخت است. روش مناسب را انتخاب کنید
                            و قبل از ادامه، مبلغ و شرایط را بررسی کنید.
                        </p>

                    </div>

                </div>

            </x-layout.container>
        </section>

        {{-- Main --}}
        <section>
            <x-layout.container>

                <div class="grid grid-cols-1 gap-6 py-8 lg:grid-cols-[minmax(0,1fr)_380px] lg:py-10">

                    {{-- LEFT --}}
                    <div class="space-y-6">

                        {{-- Flash Messages --}}
                        @if(session('success'))
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm leading-7 text-red-800">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Order Info --}}
                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                                <div>

                                    <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--livora-stone)]">
                                        ORDER
                                    </p>

                                    <h2 class="mt-2 text-lg font-semibold">
                                        {{ $order->order_number }}
                                    </h2>

                                    <p class="mt-1 text-xs text-[var(--livora-stone)]">
                                        ثبت سفارش با موفقیت انجام شده است.
                                    </p>

                                </div>

                                <div class="inline-flex w-fit rounded-full border border-[var(--livora-border)] px-4 py-2 text-xs text-[var(--livora-stone)]">
                                    {{ $paymentStatusLabel }}
                                </div>

                            </div>

                        </div>

                        {{-- Products --}}
                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                            <div class="mb-6">

                                <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    ORDER ITEMS
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    محصولات سفارش
                                </h2>

                            </div>

                            <div class="divide-y divide-[var(--livora-border)]">

                                @foreach($order->items as $item)

                                    <div class="flex gap-4 py-5 first:pt-0 last:pb-0">

                                        {{-- Image --}}
                                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-2xl bg-[var(--livora-surface)] sm:h-24 sm:w-24">

                                            @if($item->product?->images?->first()?->url)

                                                <img
                                                    src="{{ $item->product->images->first()->url }}"
                                                    alt="{{ $item->product_name }}"
                                                    class="h-full w-full object-cover"
                                                >

                                            @else

                                                <div class="flex h-full w-full items-center justify-center text-[10px] tracking-wider text-[var(--livora-stone)]">
                                                    LIVORA
                                                </div>

                                            @endif

                                        </div>

                                        {{-- Info --}}
                                        <div class="min-w-0 flex-1">

                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">

                                                <div>

                                                    <h3 class="text-sm font-semibold">
                                                        {{ $item->product_name }}
                                                    </h3>

                                                    <p class="mt-1 text-xs text-[var(--livora-stone)]">
                                                        تعداد:
                                                        {{ number_format($item->quantity) }}
                                                    </p>

                                                    @if($item->sku)

                                                        <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                            SKU:
                                                            {{ $item->sku }}
                                                        </p>

                                                    @endif

                                                </div>

                                                <div class="text-right">

                                                    <p class="text-sm font-semibold">
                                                        {{ number_format((float) $item->total) }}
                                                    </p>

                                                    <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                        تومان
                                                    </p>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </div>

                        {{-- Payment Method --}}
                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                            <div class="mb-6">

                                <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    PAYMENT METHOD
                                </p>

                                <h2 class="mt-2 text-xl font-semibold">
                                    نحوه پرداخت را انتخاب کنید
                                </h2>

                                <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                    شرایط خرید اقساطی دقیقاً بر اساس تنظیمات محصول محاسبه می‌شود.
                                </p>

                            </div>

                            <div
                                x-data="{
                                method: '{{ $installmentAvailable ? 'installment' : 'online' }}',
                                gateway: ''
                            }"
                                class="space-y-4"
                            >

                                {{-- ONLINE --}}
                                <label class="block cursor-pointer">

                                    <input
                                        type="radio"
                                        name="payment_method_preview"
                                        value="online"
                                        x-model="method"
                                        class="peer sr-only"
                                    >

                                    <div class="rounded-3xl border border-[var(--livora-border)] p-5 transition peer-checked:border-[var(--livora-ink)] peer-checked:bg-[var(--livora-surface)]">

                                        <div class="flex items-start gap-4">

                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-ink)] text-sm font-semibold text-white">
                                                01
                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                                                    <div>

                                                        <h3 class="text-sm font-semibold">
                                                            پرداخت کامل آنلاین
                                                        </h3>

                                                        <p class="mt-1 text-xs leading-6 text-[var(--livora-stone)]">
                                                            کل مبلغ سفارش را به‌صورت آنلاین پرداخت کنید.
                                                        </p>

                                                    </div>

                                                    <p class="text-sm font-semibold">
                                                        {{ number_format($total) }}
                                                        تومان
                                                    </p>

                                                </div>

                                                <div class="mt-4 flex flex-wrap gap-2 text-[11px] text-[var(--livora-stone)]">

                                                <span class="rounded-full border border-[var(--livora-border)] px-3 py-1.5">
                                                    پرداخت یکجا
                                                </span>

                                                    <span class="rounded-full border border-[var(--livora-border)] px-3 py-1.5">
                                                    بدون چک
                                                </span>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </label>

                                {{-- INSTALLMENT --}}
                                @if($installmentAvailable)

                                    <label class="block cursor-pointer">

                                        <input
                                            type="radio"
                                            name="payment_method_preview"
                                            value="installment"
                                            x-model="method"
                                            class="peer sr-only"
                                        >

                                        <div class="rounded-3xl border border-[var(--livora-border)] p-5 transition peer-checked:border-[var(--livora-ink)] peer-checked:bg-[var(--livora-surface)]">

                                            <div class="flex items-start gap-4">

                                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--livora-accent)] text-sm font-semibold text-white">
                                                    02
                                                </div>

                                                <div class="min-w-0 flex-1">

                                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                                                        <div>

                                                            <h3 class="text-sm font-semibold">
                                                                خرید اقساطی
                                                            </h3>

                                                            <p class="mt-1 text-xs leading-6 text-[var(--livora-stone)]">
                                                                بخشی از مبلغ را امروز پرداخت کنید و باقی‌مانده را طبق برنامه تسویه کنید.
                                                            </p>

                                                        </div>

                                                        <span class="rounded-full border border-[var(--livora-border)] px-3 py-1.5 text-[11px] text-[var(--livora-stone)]">
                                                        {{ $cashPercent }}٪ پیش‌پرداخت
                                                    </span>

                                                    </div>

                                                    {{-- Installment Summary --}}
                                                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">

                                                        <div class="rounded-2xl bg-[var(--livora-surface)] p-4">

                                                            <p class="text-[11px] text-[var(--livora-stone)]">
                                                                امروز
                                                            </p>

                                                            <p class="mt-2 text-base font-bold">
                                                                {{ number_format($cashAmount) }}
                                                                تومان
                                                            </p>

                                                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                                پیش‌پرداخت
                                                            </p>

                                                        </div>

                                                        <div class="rounded-2xl bg-[var(--livora-surface)] p-4">

                                                            <p class="text-[11px] text-[var(--livora-stone)]">
                                                                باقی‌مانده
                                                            </p>

                                                            <p class="mt-2 text-base font-bold">
                                                                {{ number_format($deferredAmount) }}
                                                                تومان
                                                            </p>

                                                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                                {{ number_format($chequeCount) }} فقره چک
                                                            </p>

                                                        </div>

                                                        <div class="rounded-2xl bg-[var(--livora-surface)] p-4">

                                                            <p class="text-[11px] text-[var(--livora-stone)]">
                                                                فاصله سررسید
                                                            </p>

                                                            <p class="mt-2 text-base font-bold">
                                                                {{ number_format($intervalMonths) }}
                                                                ماه
                                                            </p>

                                                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                                بین چک‌ها
                                                            </p>

                                                        </div>

                                                    </div>

                                                    {{-- Cheque Schedule --}}
                                                    @if(count($chequeAmounts))

                                                        <div class="mt-5 rounded-2xl border border-[var(--livora-border)] p-4">

                                                            <div class="flex items-center justify-between">

                                                                <div>

                                                                    <p class="text-sm font-semibold">
                                                                        برنامه تسویه
                                                                    </p>

                                                                    <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                                        سررسیدها بر اساس تنظیمات محصول محاسبه شده‌اند.
                                                                    </p>

                                                                </div>

                                                                <span class="text-[11px] text-[var(--livora-stone)]">
                                                                {{ number_format(count($chequeAmounts)) }} چک
                                                            </span>

                                                            </div>

                                                            <div class="mt-4 space-y-2">

                                                                @foreach($chequeAmounts as $index => $amount)

                                                                    <div class="flex items-center justify-between gap-4 rounded-xl bg-[var(--livora-surface)] px-4 py-3">

                                                                        <div>

                                                                            <p class="text-xs font-medium">
                                                                                چک {{ number_format($index + 1) }}
                                                                            </p>

                                                                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                                                                {{ number_format(($index + 1) * $intervalMonths) }}
                                                                                ماه بعد
                                                                            </p>

                                                                        </div>

                                                                        <p class="text-sm font-semibold">
                                                                            {{ number_format($amount) }}
                                                                            تومان
                                                                        </p>

                                                                    </div>

                                                                @endforeach

                                                            </div>

                                                        </div>

                                                    @endif

                                                </div>

                                            </div>

                                        </div>

                                    </label>

                                @endif

                                {{-- EXTERNAL GATEWAYS --}}
                                @if($installmentAvailable)

                                    <div
                                        x-show="method === 'installment'"
                                        x-cloak
                                        class="mt-5 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5"
                                    >

                                        <div class="mb-5">

                                            <p class="text-sm font-semibold">
                                                سرویس پرداخت اقساطی
                                            </p>

                                            <p class="mt-1 text-xs leading-6 text-[var(--livora-stone)]">
                                                یکی از سرویس‌های موجود را انتخاب کنید.
                                                در محیط تست ممکن است هنوز به API واقعی متصل نباشد.
                                            </p>

                                        </div>

                                        <form
                                            action="{{ route('checkout.payment.installment', $order) }}"
                                            method="POST"
                                            class="space-y-4"
                                        >

                                            @csrf

                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">

                                                <label class="cursor-pointer">

                                                    <input
                                                        type="radio"
                                                        name="gateway"
                                                        value="digipay"
                                                        x-model="gateway"
                                                        class="peer sr-only"
                                                        required
                                                    >

                                                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition peer-checked:border-[var(--livora-ink)] peer-checked:bg-[var(--livora-ink)] peer-checked:text-white">

                                                        <p class="text-sm font-semibold">
                                                            DigiPay
                                                        </p>

                                                        <p class="mt-1 text-[11px] opacity-60">
                                                            اقساطی
                                                        </p>

                                                    </div>

                                                </label>

                                                <label class="cursor-pointer">

                                                    <input
                                                        type="radio"
                                                        name="gateway"
                                                        value="snappay"
                                                        x-model="gateway"
                                                        class="peer sr-only"
                                                    >

                                                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition peer-checked:border-[var(--livora-ink)] peer-checked:bg-[var(--livora-ink)] peer-checked:text-white">

                                                        <p class="text-sm font-semibold">
                                                            SnapPay
                                                        </p>

                                                        <p class="mt-1 text-[11px] opacity-60">
                                                            اقساطی
                                                        </p>

                                                    </div>

                                                </label>

                                                <label class="cursor-pointer">

                                                    <input
                                                        type="radio"
                                                        name="gateway"
                                                        value="torobpay"
                                                        x-model="gateway"
                                                        class="peer sr-only"
                                                    >

                                                    <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition peer-checked:border-[var(--livora-ink)] peer-checked:bg-[var(--livora-ink)] peer-checked:text-white">

                                                        <p class="text-sm font-semibold">
                                                            TorobPay
                                                        </p>

                                                        <p class="mt-1 text-[11px] opacity-60">
                                                            اقساطی
                                                        </p>

                                                    </div>

                                                </label>

                                            </div>

                                            <button
                                                type="submit"
                                                class="w-full rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                            >
                                                انتخاب سرویس و ادامه
                                            </button>

                                        </form>

                                    </div>

                                @endif

                            </div>

                        </div>

                        {{-- Internal Livora --}}
                        @if($installmentAvailable)

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-8">

                                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                                    <div>

                                        <p class="text-[11px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                            LIVORA PLAN
                                        </p>

                                        <h2 class="mt-2 text-xl font-semibold">
                                            ثبت برنامه اقساط داخلی
                                        </h2>

                                        <p class="mt-2 max-w-2xl text-xs leading-7 text-[var(--livora-stone)]">
                                            برای محیط تست می‌توان برنامه اقساط داخلی Livora را بدون اتصال به Provider خارجی ثبت کرد.
                                        </p>

                                    </div>

                                    <form
                                        action="{{ route('checkout.payment.livora-installment', $order) }}"
                                        method="POST"
                                    >

                                        @csrf

                                        <button
                                            type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-5 py-3.5 text-sm font-medium transition hover:border-[var(--livora-ink)] hover:bg-[var(--livora-surface)] sm:w-auto"
                                        >
                                            ثبت برنامه داخلی
                                        </button>

                                    </form>

                                </div>

                                @if($hasInternalPlan)

                                    <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-7 text-emerald-800">
                                        برنامه اقساط داخلی این سفارش قبلاً ثبت شده است.
                                    </div>

                                @endif

                            </div>

                        @endif

                    </div>

                    {{-- RIGHT SIDEBAR --}}
                    <aside class="lg:sticky lg:top-8 lg:self-start">

                        <div class="overflow-hidden rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">

                            <div class="p-6 sm:p-7">

                                <p class="text-[11px] uppercase tracking-[0.18em] text-white/45">
                                    ORDER TOTAL
                                </p>

                                <p class="mt-3 text-3xl font-semibold tracking-tight">
                                    {{ number_format($total) }}
                                    <span class="text-sm font-normal text-white/50">
                                    تومان
                                </span>
                                </p>

                                <div class="my-6 h-px bg-white/10"></div>

                                <div class="space-y-4 text-sm">

                                    <div class="flex items-center justify-between gap-4">

                                    <span class="text-white/55">
                                        جمع محصولات
                                    </span>

                                        <span>
                                        {{ number_format((float) $order->subtotal) }}
                                    </span>

                                    </div>

                                    <div class="flex items-center justify-between gap-4">

                                    <span class="text-white/55">
                                        هزینه ارسال
                                    </span>

                                        <span>
                                        {{ number_format((float) $order->shipping_cost) }}
                                    </span>

                                    </div>

                                    <div class="flex items-center justify-between gap-4">

                                    <span class="text-white/55">
                                        تخفیف
                                    </span>

                                        <span>
                                        {{ number_format((float) $order->discount) }}
                                    </span>

                                    </div>

                                </div>

                            </div>

                            @if($installmentAvailable)

                                <div class="border-t border-white/10 bg-white/5 p-6 sm:p-7">

                                    <p class="text-[11px] uppercase tracking-[0.18em] text-white/45">
                                        INSTALLMENT
                                    </p>

                                    <p class="mt-2 text-sm text-white/70">
                                        {{ $cashPercent }}٪ پیش‌پرداخت
                                    </p>

                                    <p class="mt-3 text-xl font-semibold">
                                        {{ number_format($cashAmount) }}
                                        تومان
                                    </p>

                                    <p class="mt-2 text-xs leading-6 text-white/45">
                                        سپس
                                        {{ number_format($chequeCount) }}
                                        فقره چک
                                        @if($intervalMonths)
                                            با فاصله {{ number_format($intervalMonths) }} ماه
                                        @endif
                                    </p>

                                </div>

                            @endif

                        </div>

                        {{-- Trust / Info --}}
                        <div class="mt-4 rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                            <div class="space-y-4">

                                <div class="flex gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                    01
                                </span>

                                    <div>
                                        <p class="text-xs font-semibold">
                                            مبلغ نهایی سفارش
                                        </p>

                                        <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                            مبلغ پرداختی از اطلاعات داخلی سفارش محاسبه می‌شود.
                                        </p>
                                    </div>

                                </div>

                                <div class="flex gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                    02
                                </span>

                                    <div>
                                        <p class="text-xs font-semibold">
                                            شرایط اقساط
                                        </p>

                                        <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                            شرایط اقساط از تنظیمات ثبت‌شده برای محصولات سفارش گرفته می‌شود.
                                        </p>
                                    </div>

                                </div>

                                <div class="flex gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                    03
                                </span>

                                    <div>
                                        <p class="text-xs font-semibold">
                                            امنیت پرداخت
                                        </p>

                                        <p class="mt-1 text-[11px] leading-6 text-[var(--livora-stone)]">
                                            درگاه نهایی از طریق سرویس پرداخت انتخاب‌شده مدیریت می‌شود.
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </aside>

                </div>

            </x-layout.container>
        </section>

    </div>

@endsection
