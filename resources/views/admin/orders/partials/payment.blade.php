<div class="admin-card p-6">

    <div class="mb-6">

        <h3 class="text-base font-bold text-[var(--admin-text)]">
            اطلاعات پرداخت
        </h3>

        <p class="mt-1 text-xs text-[var(--admin-muted)]">
            آخرین وضعیت پرداخت سفارش
        </p>

    </div>


    @php
        $payment = $order->latestPayment;
    @endphp


    <dl class="space-y-5">

        <div class="flex items-center justify-between gap-4">
            <dt class="text-xs text-[var(--admin-muted)]">
                وضعیت پرداخت
            </dt>

            <dd>

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
                            بازپرداخت شده
                        </span>
                    @break

                    @default
                    <span class="admin-badge admin-badge-neutral">
                            {{ $order->payment_status ?: '—' }}
                        </span>

                @endswitch

            </dd>
        </div>


        <div class="flex items-center justify-between gap-4">

            <dt class="text-xs text-[var(--admin-muted)]">
                مبلغ سفارش
            </dt>

            <dd class="text-sm font-semibold text-[var(--admin-text)]">
                {{ number_format((float) $order->total) }}
                تومان
            </dd>

        </div>


        @if($payment)

            @if(isset($payment->amount))

                <div class="flex items-center justify-between gap-4">

                    <dt class="text-xs text-[var(--admin-muted)]">
                        مبلغ پرداختی
                    </dt>

                    <dd class="text-sm text-[var(--admin-text-soft)]">
                        {{ number_format((float) $payment->amount) }}
                        تومان
                    </dd>

                </div>

            @endif


            @if(isset($payment->transaction_id))

                <div>

                    <dt class="text-xs text-[var(--admin-muted)]">
                        شناسه تراکنش
                    </dt>

                    <dd class="mt-2 break-all font-mono text-xs text-[var(--admin-text-soft)]">
                        {{ $payment->transaction_id }}
                    </dd>

                </div>

            @endif


            @if(isset($payment->reference_id))

                <div>

                    <dt class="text-xs text-[var(--admin-muted)]">
                        شناسه مرجع
                    </dt>

                    <dd class="mt-2 break-all font-mono text-xs text-[var(--admin-text-soft)]">
                        {{ $payment->reference_id }}
                    </dd>

                </div>

            @endif

        @else

            <div class="border-t border-[var(--admin-border-soft)] pt-5">

                <p class="text-xs text-[var(--admin-muted)]">
                    رکورد پرداختی برای این سفارش ثبت نشده است.
                </p>

            </div>

        @endif

    </dl>

</div>
