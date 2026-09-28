@extends('admin.layouts.app')

@section('title', 'اقساط و وصول')
@section('page_title', 'اقساط و وصول')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">
                SALES / INSTALLMENTS
            </p>

            <h1 class="admin-title mt-2">
                اقساط و وصول
            </h1>

            <p class="admin-subtitle mt-2 max-w-2xl">
                وضعیت واقعی اقساط ثبت‌شده سفارش‌ها را ببینید و فقط مبالغی را که واقعاً دریافت شده‌اند در دفتر مالی ثبت کنید.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.installments.index') }}"
                class="admin-btn {{ $status === '' ? 'admin-btn-primary' : 'admin-btn-secondary' }}"
            >
                همه
            </a>

            <a
                href="{{ route('admin.installments.index', ['status' => 'overdue']) }}"
                class="admin-btn {{ $status === 'overdue' ? 'admin-btn-primary' : 'admin-btn-secondary' }}"
            >
                سررسید گذشته
            </a>

            <a
                href="{{ route('admin.installments.index', ['status' => 'pending']) }}"
                class="admin-btn {{ $status === 'pending' ? 'admin-btn-primary' : 'admin-btn-secondary' }}"
            >
                در انتظار
            </a>

            <a
                href="{{ route('admin.installments.index', ['status' => 'paid']) }}"
                class="admin-btn {{ $status === 'paid' ? 'admin-btn-primary' : 'admin-btn-secondary' }}"
            >
                پرداخت‌شده
            </a>
        </div>

    </div>


    <div class="admin-card overflow-hidden">

        <div class="hidden overflow-x-auto lg:block">

            <table class="admin-table">
                <thead>
                <tr>
                    <th>سفارش</th>
                    <th>نوع</th>
                    <th>مبلغ</th>
                    <th>سررسید</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($installments as $installment)

                    @php
                        $overdue =
                            $installment->status === 'pending'
                            && $installment->isOverdue();
                    @endphp

                    <tr>

                        <td>
                            <a
                                href="{{ route('admin.orders.show', $installment->order) }}"
                                class="font-semibold text-[var(--admin-accent)] hover:underline"
                            >
                                {{ $installment->order->order_number }}
                            </a>

                            <p class="mt-1 text-[10px] text-[var(--admin-muted)]">
                                {{ $installment->order->fullName }}
                            </p>
                        </td>

                        <td>
                            <span class="text-xs text-[var(--admin-text-soft)]">
                                {{ $installment->typeLabel }}
                            </span>
                        </td>

                        <td>
                            <span class="font-semibold text-[var(--admin-text)]">
                                {{ number_format((float) $installment->amount) }}
                            </span>
                            <span class="text-[10px] text-[var(--admin-muted)]">
                                تومان
                            </span>
                        </td>

                        <td>
                            <span class="{{ $overdue ? 'font-bold text-[var(--admin-danger)]' : 'text-[var(--admin-text-soft)]' }} text-xs">
                                {{ $installment->due_date?->format('Y/m/d') ?? '—' }}
                            </span>
                        </td>

                        <td>
                            @if($installment->status === 'paid')
                                <span class="admin-badge admin-badge-success">
                                    پرداخت شده
                                </span>
                            @elseif($overdue)
                                <span class="admin-badge admin-badge-danger">
                                    سررسید گذشته
                                </span>
                            @elseif($installment->status === 'pending')
                                <span class="admin-badge admin-badge-warning">
                                    در انتظار
                                </span>
                            @else
                                <span class="admin-badge admin-badge-neutral">
                                    {{ $installment->statusLabel }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($installment->status === 'pending')

                                <form
                                    action="{{ route('admin.installments.paid', $installment) }}"
                                    method="POST"
                                    class="flex items-center gap-2"
                                    data-confirm="تأیید می‌کنید این مبلغ واقعاً دریافت شده و در دفتر پرداخت ثبت شود؟"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="admin-btn admin-btn-primary px-3"
                                    >
                                        ثبت وصول
                                    </button>
                                </form>

                            @elseif($installment->paidBy)

                                <span class="text-[10px] text-[var(--admin-muted)]">
                                    دریافت توسط {{ $installment->paidBy->name }}
                                </span>

                            @else

                                <span class="text-[10px] text-[var(--admin-muted)]">
                                    —
                                </span>

                            @endif
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6">
                            <div class="py-16 text-center">
                                <p class="text-sm font-semibold text-[var(--admin-text)]">
                                    قسطی برای نمایش وجود ندارد.
                                </p>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    با ایجاد سفارش‌های اقساطی، برنامه وصول در اینجا ثبت می‌شود.
                                </p>
                            </div>
                        </td>
                    </tr>

                @endforelse

                </tbody>
            </table>

        </div>


        <div class="divide-y divide-[var(--admin-border)] lg:hidden">

            @forelse($installments as $installment)

                @php
                    $overdue =
                        $installment->status === 'pending'
                        && $installment->isOverdue();
                @endphp

                <article class="p-4">

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">
                            <a
                                href="{{ route('admin.orders.show', $installment->order) }}"
                                class="text-sm font-bold text-[var(--admin-accent)]"
                            >
                                {{ $installment->order->order_number }}
                            </a>

                            <p class="mt-1 truncate text-[10px] text-[var(--admin-muted)]">
                                {{ $installment->order->fullName }}
                            </p>
                        </div>

                        @if($installment->status === 'paid')
                            <span class="admin-badge admin-badge-success shrink-0">
                                پرداخت شده
                            </span>
                        @elseif($overdue)
                            <span class="admin-badge admin-badge-danger shrink-0">
                                سررسید گذشته
                            </span>
                        @else
                            <span class="admin-badge admin-badge-warning shrink-0">
                                در انتظار
                            </span>
                        @endif

                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3">

                        <div class="rounded-2xl bg-[var(--admin-surface)] p-3">
                            <p class="text-[10px] text-[var(--admin-muted)]">
                                مبلغ
                            </p>
                            <p class="mt-1 text-sm font-bold text-[var(--admin-text)]">
                                {{ number_format((float) $installment->amount) }}
                                <span class="text-[9px] font-normal text-[var(--admin-muted)]">
                                    تومان
                                </span>
                            </p>
                        </div>

                        <div class="rounded-2xl bg-[var(--admin-surface)] p-3">
                            <p class="text-[10px] text-[var(--admin-muted)]">
                                سررسید
                            </p>
                            <p class="{{ $overdue ? 'text-[var(--admin-danger)]' : 'text-[var(--admin-text)]' }} mt-1 text-sm font-bold">
                                {{ $installment->due_date?->format('Y/m/d') ?? '—' }}
                            </p>
                        </div>

                    </div>

                    @if($installment->status === 'pending')

                        <form
                            action="{{ route('admin.installments.paid', $installment) }}"
                            method="POST"
                            class="mt-4"
                            data-confirm="تأیید می‌کنید این مبلغ واقعاً دریافت شده و در دفتر پرداخت ثبت شود؟"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="admin-btn admin-btn-primary w-full"
                            >
                                ثبت وصول واقعی
                            </button>
                        </form>

                    @endif

                </article>

            @empty

                <div class="p-10 text-center">
                    <p class="text-sm font-semibold text-[var(--admin-text)]">
                        قسطی برای نمایش وجود ندارد.
                    </p>
                </div>

            @endforelse

        </div>


        @if($installments->hasPages())
            <div class="border-t border-[var(--admin-border)] p-5">
                {{ $installments->links() }}
            </div>
        @endif

    </div>

@endsection
