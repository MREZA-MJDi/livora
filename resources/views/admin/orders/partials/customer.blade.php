<div class="admin-card p-6">

    <div class="mb-6">

        <h3 class="text-base font-bold text-[var(--admin-text)]">
            اطلاعات مشتری
        </h3>

        <p class="mt-1 text-xs text-[var(--admin-muted)]">
            اطلاعات ثبت‌شده هنگام ایجاد سفارش
        </p>

    </div>


    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        <div>
            <dt class="admin-stat-label">
                نام و نام خانوادگی
            </dt>

            <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                {{ $order->full_name ?: '—' }}
            </dd>
        </div>


        <div>
            <dt class="admin-stat-label">
                تلفن
            </dt>

            <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                {{ $order->phone ?: '—' }}
            </dd>
        </div>


        <div>
            <dt class="admin-stat-label">
                ایمیل
            </dt>

            <dd class="mt-2 break-all text-sm text-[var(--admin-text-soft)]">
                {{ $order->email ?: ($order->user?->email ?? '—') }}
            </dd>
        </div>


        <div>
            <dt class="admin-stat-label">
                کاربر حساب
            </dt>

            <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                {{ $order->user?->name ?? '—' }}
            </dd>
        </div>


        <div class="sm:col-span-2">

            <dt class="admin-stat-label">
                آدرس
            </dt>

            <dd class="mt-2 text-sm leading-7 text-[var(--admin-text-soft)]">
                {{ $order->full_address ?: '—' }}
            </dd>

        </div>


        <div>
            <dt class="admin-stat-label">
                کد پستی
            </dt>

            <dd class="mt-2 font-mono text-sm text-[var(--admin-text-soft)]">
                {{ $order->postal_code ?: '—' }}
            </dd>
        </div>


        <div>
            <dt class="admin-stat-label">
                واحد
            </dt>

            <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                {{ $order->unit ?: '—' }}
            </dd>
        </div>

    </dl>


    @if($order->notes)

        <div class="mt-6 border-t border-[var(--admin-border-soft)] pt-6">

            <dt class="admin-stat-label">
                یادداشت مشتری
            </dt>

            <dd class="mt-2 text-sm leading-7 text-[var(--admin-text-soft)]">
                {!! nl2br(e($order->notes)) !!}
            </dd>

        </div>

    @endif

</div>
