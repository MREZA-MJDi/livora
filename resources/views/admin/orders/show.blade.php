@extends('admin.layouts.app')

@section('title', 'سفارش ' . $order->order_number)
@section('page_title', 'جزئیات سفارش')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <span class="text-xs font-medium text-[var(--admin-accent)]">
                SALES / ORDERS / VIEW
            </span>

            <h2 class="admin-title mt-2">
                سفارش {{ $order->order_number }}
            </h2>

            <p class="admin-subtitle mt-2">
                جزئیات کامل سفارش و وضعیت پرداخت
            </p>

        </div>


        <a
            href="{{ route('admin.orders.index') }}"
            class="admin-btn admin-btn-secondary"
        >
            بازگشت به سفارش‌ها
        </a>

    </div>


    @if(in_array($order->inventory_status, ['stock_conflict', 'needs_reconciliation'], true))
        <div role="alert" class="mb-6 rounded-2xl border border-amber-300 bg-amber-50 p-5 text-amber-950">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 font-bold">!</span>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold">
                        @if($order->inventory_status === 'stock_conflict')
                            موجودی این سفارش نیاز به بررسی فوری دارد
                        @else
                            موجودی سفارش قدیمی هنوز تطبیق داده نشده است
                        @endif
                    </h3>
                    <p class="mt-2 text-xs leading-6">
                        پرداخت این سفارش ثبت شده، اما تخصیص موجودی به‌صورت خودکار تکمیل نشده است.
                        قبل از آماده‌سازی یا ارسال، موجودی واقعی را بررسی و وضعیت سفارش را با تصمیم مشتری/مدیر تطبیق دهید.
                    </p>
                    @if(is_array($order->inventory_metadata) && !empty($order->inventory_metadata['issues']))
                        <ul class="mt-3 list-inside list-disc space-y-1 text-xs">
                            @foreach($order->inventory_metadata['issues'] as $issue)
                                <li>{{ $issue['reason'] ?? 'نیاز به بررسی موجودی' }}@if(!empty($issue['product_name'])) — {{ $issue['product_name'] }}@endif</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($order->inventory_metadata['reason']))
                        <p class="mt-3 text-xs">{{ $order->inventory_metadata['reason'] }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(($order->latestPayment?->metadata['manual_review_required'] ?? false) === true)
        <div role="alert" class="mb-6 rounded-2xl border border-red-300 bg-red-50 p-5 text-red-950">
            <h3 class="text-sm font-bold">تلاش پرداخت نیازمند تطبیق دستی است</h3>
            <p class="mt-2 text-xs leading-6">
                درگاه پرداخت تأیید کرده که این تلاش هم موفق بوده، در حالی که سفارش قبلاً پرداخت شده است.
                شناسه تراکنش را با گزارش درگاه تطبیق دهید و در صورت نیاز بازپرداخت را از مسیر رسمی درگاه انجام دهید.
            </p>
        </div>
    @endif

    {{-- Order Summary --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                شماره سفارش
            </p>

            <p class="mt-3 font-mono text-sm font-bold text-[var(--admin-text)]">
                {{ $order->order_number }}
            </p>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                مبلغ نهایی
            </p>

            <p class="admin-stat-value">
                {{ number_format((float) $order->total) }}

                <span class="text-xs font-normal text-[var(--admin-muted)]">
                    تومان
                </span>
            </p>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                وضعیت سفارش
            </p>

            <div class="mt-3">

                @switch($order->status)

                    @case('pending')
                    <span class="admin-badge admin-badge-warning">
                            در انتظار
                        </span>
                    @break

                    @case('processing')
                    <span class="admin-badge admin-badge-info">
                            در حال پردازش
                        </span>
                    @break

                    @case('shipped')
                    <span class="admin-badge admin-badge-info">
                            ارسال شده
                        </span>
                    @break

                    @case('delivered')
                    <span class="admin-badge admin-badge-success">
                            تحویل شده
                        </span>
                    @break

                    @case('cancelled')
                    <span class="admin-badge admin-badge-danger">
                            لغو شده
                        </span>
                    @break

                    @default
                    <span class="admin-badge admin-badge-neutral">
                            {{ $order->status }}
                        </span>

                @endswitch

            </div>

        </div>


        <div class="admin-stat p-5">

            <p class="admin-stat-label">
                وضعیت پرداخت
            </p>

            <div class="mt-3">

                @switch($order->payment_status)

                    @case('paid')
                    <span class="admin-badge admin-badge-success">
                            پرداخت شده
                        </span>
                    @break

                    @case('pending')
                    <span class="admin-badge admin-badge-warning">
                            در انتظار
                        </span>
                    @break

                    @case('failed')
                    <span class="admin-badge admin-badge-danger">
                            ناموفق
                        </span>
                    @break

                    @case('refunded')
                    <span class="admin-badge admin-badge-info">
                            بازپرداخت
                        </span>
                    @break

                    @default
                    <span class="admin-badge admin-badge-neutral">
                            {{ $order->payment_status ?: '—' }}
                        </span>

                @endswitch

            </div>

        </div>

    </div>


    {{-- Main Content --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            @include('admin.orders.partials.customer', [
                'order' => $order,
            ])

            @include('admin.orders.partials.items', [
                'order' => $order,
            ])

            @include('admin.orders.partials.payment', [
                'order' => $order,
            ])

        </div>


        <div class="space-y-6">

            @include('admin.orders.partials.status', [
                'order' => $order,
            ])


            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    خلاصه مالی
                </h3>

                <dl class="mt-6 space-y-4">

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-xs text-[var(--admin-muted)]">
                            مبلغ اقلام
                        </dt>

                        <dd class="text-sm text-[var(--admin-text-soft)]">
                            {{ number_format((float) $order->subtotal) }}
                            تومان
                        </dd>
                    </div>


                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-xs text-[var(--admin-muted)]">
                            هزینه ارسال
                        </dt>

                        <dd class="text-sm text-[var(--admin-text-soft)]">
                            {{ number_format((float) $order->shipping_cost) }}
                            تومان
                        </dd>
                    </div>


                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-xs text-[var(--admin-muted)]">
                            تخفیف
                        </dt>

                        <dd class="text-sm text-[var(--admin-text-soft)]">
                            {{ number_format((float) $order->discount) }}
                            تومان
                        </dd>
                    </div>


                    <div class="border-t border-[var(--admin-border-soft)] pt-4">

                        <div class="flex items-center justify-between gap-4">

                            <dt class="text-sm font-semibold text-[var(--admin-text)]">
                                مبلغ نهایی
                            </dt>

                            <dd class="text-base font-bold text-[var(--admin-accent)]">
                                {{ number_format((float) $order->total) }}
                                تومان
                            </dd>

                        </div>

                    </div>

                </dl>

            </div>


            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات سفارش
                </h3>

                <dl class="mt-6 space-y-5">

                    <div>

                        <dt class="admin-stat-label">
                            تاریخ ثبت
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $order->created_at?->format('Y/m/d H:i') ?? '—' }}
                        </dd>

                    </div>


                    <div>

                        <dt class="admin-stat-label">
                            آخرین بروزرسانی
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $order->updated_at?->format('Y/m/d H:i') ?? '—' }}
                        </dd>

                    </div>

                </dl>

            </div>

        </div>

    </div>

@endsection
