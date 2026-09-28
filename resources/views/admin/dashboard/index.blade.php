@extends('admin.layouts.app')

@section('title', 'داشبورد')
@section('page_title', 'داشبورد')

@section(
    'meta_description',
    'مرکز کنترل فروشگاه SilaGallery'
)

@section('content')

    @php
        $statusLabels = [
            'pending' => 'در انتظار',
            'processing' => 'در پردازش',
            'shipped' => 'ارسال شده',
            'delivered' => 'تحویل شده',
            'cancelled' => 'لغو شده',
        ];

        $statusStyles = [
            'pending' => 'bg-amber-50 text-amber-700',
            'processing' => 'bg-sky-50 text-sky-700',
            'shipped' => 'bg-indigo-50 text-indigo-700',
            'delivered' => 'bg-emerald-50 text-emerald-700',
            'cancelled' => 'bg-red-50 text-red-700',
        ];

        $statusTotal = array_sum($orderStatus);

        $deliveredPercent = $statusTotal > 0
            ? round(
                (($orderStatus['delivered'] ?? 0) / $statusTotal) * 100
            )
            : 0;

        $monthPaidRate = $currentMonthOrders > 0
            ? round(
                ($currentMonthPaidOrders / $currentMonthOrders) * 100
            )
            : 0;

        $installmentPaidPercent = $installmentOrders > 0
            ? round(
                ($installmentPaidOrders / $installmentOrders) * 100
            )
            : 0;

        $maxMonthlyRevenue = max(
            1,
            (float) $monthlyRevenue->max('revenue')
        );

        $attentionItems = collect([
            [
                'count' => $pendingOrders,
                'label' => 'سفارش نیازمند پردازش',
                'description' => 'سفارش‌های در انتظار یا در حال پردازش',
                'route' => route('admin.orders.index'),
                'tone' => 'warning',
            ],
            [
                'count' => $pendingPaymentOrders,
                'label' => 'پرداخت در انتظار',
                'description' => 'سفارش‌هایی که هنوز پرداخت نهایی ندارند',
                'route' => route('admin.orders.index', ['payment_status' => 'pending']),
                'tone' => 'danger',
            ],
            [
                'count' => $lowStockProducts,
                'label' => 'محصول کم‌موجودی',
                'description' => 'موجودی بین ۱ تا ۵ عدد',
                'route' => route('admin.products.index', ['stock' => 'low_stock']),
                'tone' => 'warning',
            ],
            [
                'count' => $outOfStockProducts,
                'label' => 'محصول ناموجود',
                'description' => 'محصولاتی که موجودی صفر یا کمتر دارند',
                'route' => route('admin.products.index', ['stock' => 'out_of_stock']),
                'tone' => 'danger',
            ],
            [
                'count' => $outOfStockVariants,
                'label' => 'تنوع ناموجود',
                'description' => 'تنوع‌هایی که موجودی صفر یا کمتر دارند',
                'route' => route('admin.product-variants.index', ['stock' => 'out_of_stock']),
                'tone' => 'danger',
            ],
            [
                'count' => $lowStockVariants,
                'label' => 'تنوع کم‌موجودی',
                'description' => 'تنوع‌هایی با موجودی ۱ تا ۳ عدد',
                'route' => route('admin.product-variants.index', ['stock' => 'low_stock']),
                'tone' => 'warning',
            ],
            [
                'count' => $unreadContactMessages,
                'label' => 'پیام خوانده‌نشده',
                'description' => 'پیام‌هایی که هنوز بررسی نشده‌اند',
                'route' => route('admin.contact-messages.index', ['status' => 'unread']),
                'tone' => 'info',
            ],
        ])->filter(
            fn ($item) => (int) $item['count'] > 0
        );

        $toneClasses = [
            'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
            'danger' => 'border-red-200 bg-red-50 text-red-800',
            'info' => 'border-sky-200 bg-sky-50 text-sky-800',
        ];
    @endphp


    <div class="space-y-6 lg:space-y-8">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

            <div class="min-w-0">

                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-[var(--admin-surface)] px-3 py-1 text-[9px] font-bold uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        SILAGALLERY / CONTROL CENTER
                    </span>

                    <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[9px] font-semibold text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        داده زنده
                    </span>
                </div>

                <h1 class="admin-title mt-3">
                    مرکز کنترل فروشگاه
                </h1>

                <p class="admin-subtitle mt-2 max-w-3xl">
                    تصویر عملیاتی امروز، درآمد، سفارش‌ها، موجودی و نقاطی که
                    همین حالا نیاز به اقدام دارند.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-3">

                <span class="rounded-full border border-[var(--admin-border)] bg-[var(--admin-surface)] px-4 py-2 text-xs text-[var(--admin-muted)]">
                    {{ $todayLabel }}
                </span>

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="admin-btn admin-btn-secondary"
                >
                    سفارش‌ها
                </a>

                <a
                    href="{{ route('admin.products.create') }}"
                    class="admin-btn admin-btn-primary"
                >
                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.7"
                         stroke="currentColor"
                         class="h-4 w-4">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 5.25v13.5M5.25 12h13.5"
                        />
                    </svg>

                    محصول جدید
                </a>

            </div>

        </header>


        {{-- =========================================================
             KPI STRIP
        ========================================================== --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <article class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="admin-stat-label">
                            فروش امروز
                        </p>

                        <p class="mt-2 text-2xl font-bold text-[var(--admin-text)]">
                            {{ number_format($todayRevenue) }}
                        </p>

                        <p class="mt-1 text-[11px] text-[var(--admin-muted)]">
                            تومان
                        </p>
                    </div>

                    <div class="admin-stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="1.5"
                             stroke="currentColor"
                             class="h-5 w-5">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6v12m4.5-9.75c0-1.243-1.007-2.25-2.25-2.25h-4.5A2.25 2.25 0 0 0 7.5 8.25v.75a2.25 2.25 0 0 0 2.25 2.25h4.5A2.25 2.25 0 0 1 16.5 13.5v.75a2.25 2.25 0 0 1-2.25 2.25h-4.5a2.25 2.25 0 0 1-2.25-2.25"
                            />
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between gap-3 text-[11px]">

                    <span class="text-[var(--admin-muted)]">
                        {{ number_format($todayOrders) }}
                        سفارش امروز
                    </span>

                    <span class="font-semibold text-[var(--admin-accent)]">
                        میانگین:
                        {{ number_format($averageOrderValue) }}
                    </span>

                </div>

            </article>


            <article class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="admin-stat-label">
                            فروش این ماه
                        </p>

                        <p class="mt-2 text-2xl font-bold text-[var(--admin-text)]">
                            {{ number_format($currentMonthRevenue) }}
                        </p>

                        <p class="mt-1 text-[11px] text-[var(--admin-muted)]">
                            تومان
                        </p>
                    </div>

                    <div class="admin-stat-icon">
                        %
                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between gap-3">

                    <span class="text-[11px] text-[var(--admin-muted)]">
                        {{ number_format($currentMonthOrders) }}
                        سفارش
                    </span>

                    <span class="{{ $revenueGrowthPercent >= 0 ? 'text-emerald-600' : 'text-red-600' }} text-[11px] font-bold">
                        {{ $revenueGrowthPercent > 0 ? '+' : '' }}{{ number_format($revenueGrowthPercent, 1) }}٪
                    </span>

                </div>

            </article>


            <article class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="admin-stat-label">
                            سفارش نیازمند اقدام
                        </p>

                        <p class="mt-2 text-2xl font-bold text-[var(--admin-text)]">
                            {{ number_format($pendingOrders) }}
                        </p>

                        <p class="mt-1 text-[11px] text-[var(--admin-muted)]">
                            در انتظار / در پردازش
                        </p>
                    </div>

                    <div class="admin-stat-icon">
                        !
                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between gap-3 text-[11px]">

                    <span class="text-[var(--admin-muted)]">
                        پرداخت‌های معلق
                    </span>

                    <span class="font-bold text-amber-700">
                        {{ number_format($pendingPaymentOrders) }}
                    </span>

                </div>

            </article>


            <article class="admin-stat admin-card-hover p-5">

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <p class="admin-stat-label">
                            کاتالوگ و تنوع
                        </p>

                        <p class="mt-2 text-2xl font-bold text-[var(--admin-text)]">
                            {{ number_format($totalProducts) }}
                        </p>

                        <p class="mt-1 text-[11px] text-[var(--admin-muted)]">
                            محصول · {{ number_format($totalVariants) }} تنوع
                        </p>
                    </div>

                    <div class="admin-stat-icon">
                        #
                    </div>

                </div>

                <div class="mt-4 flex items-center justify-between gap-3 text-[11px]">

                    <span class="text-[var(--admin-muted)]">
                        فعال:
                        {{ number_format($activeProducts) }}
                    </span>

                    <div class="flex items-center gap-3">
                        <span class="{{ $outOfStockProducts > 0 ? 'text-red-600' : 'text-emerald-600' }} font-bold">
                            محصول ناموجود:
                            {{ number_format($outOfStockProducts) }}
                        </span>

                        <span class="{{ $outOfStockVariants > 0 ? 'text-red-600' : 'text-emerald-600' }} font-bold">
                            تنوع ناموجود:
                            {{ number_format($outOfStockVariants) }}
                        </span>
                    </div>

                </div>

            </article>

        </section>


        {{-- =========================================================
             ATTENTION QUEUE
        ========================================================== --}}
        @if($attentionItems->isNotEmpty())

            <section class="admin-card overflow-hidden">

                <div class="flex flex-col gap-2 border-b border-[var(--admin-border)] p-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                            ATTENTION QUEUE
                        </p>

                        <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                            اولویت‌های امروز
                        </h2>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            مواردی که بهتر است قبل از پایان روز بررسی شوند.
                        </p>
                    </div>

                    <span class="text-xs text-[var(--admin-muted)]">
                        {{ number_format($attentionItems->count()) }}
                        مورد
                    </span>

                </div>


                <div class="grid grid-cols-1 divide-y divide-[var(--admin-border)] sm:grid-cols-2 sm:divide-x sm:divide-y-0 xl:grid-cols-5 xl:divide-x xl:divide-y-0">

                    @foreach($attentionItems as $item)

                        <a
                            href="{{ $item['route'] }}"
                            class="group p-5 transition hover:bg-[var(--admin-surface)]"
                        >

                            <div class="flex items-start justify-between gap-3">

                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border {{ $toneClasses[$item['tone']] }}">
                                    {{ number_format($item['count']) }}
                                </span>

                                <span class="text-xs text-[var(--admin-muted)] transition group-hover:text-[var(--admin-accent)]">
                                    مشاهده ←
                                </span>

                            </div>

                            <h3 class="mt-4 text-sm font-semibold text-[var(--admin-text)]">
                                {{ $item['label'] }}
                            </h3>

                            <p class="mt-1 text-[10px] leading-6 text-[var(--admin-muted)]">
                                {{ $item['description'] }}
                            </p>

                        </a>

                    @endforeach

                </div>

            </section>

        @endif


        {{-- =========================================================
             REVENUE + ORDER PIPELINE
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(20rem,.8fr)]">

            <section class="admin-card overflow-hidden">

                <div class="flex flex-col gap-4 border-b border-[var(--admin-border)] p-6 sm:flex-row sm:items-end sm:justify-between">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                            REVENUE
                        </p>

                        <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                            روند فروش ۶ ماه اخیر
                        </h2>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            فقط سفارش‌هایی که پرداخت موفق داشته‌اند.
                        </p>

                    </div>

                    <div class="text-left">

                        <p class="text-[10px] text-[var(--admin-muted)]">
                            میانگین ارزش سفارش
                        </p>

                        <p class="mt-1 text-sm font-bold text-[var(--admin-text)]">
                            {{ number_format($averageOrderValue) }}
                            تومان
                        </p>

                    </div>

                </div>


                <div class="p-6">

                    <div class="relative h-[290px]">

                        <div class="pointer-events-none absolute inset-0 flex flex-col justify-between">
                            @for($i = 0; $i < 5; $i++)
                                <div class="border-t border-dashed border-[var(--admin-border)]"></div>
                            @endfor
                        </div>

                        <div class="absolute inset-0 flex items-end gap-2 sm:gap-4">

                            @foreach($monthlyRevenue as $month)

                                @php
                                    $height = $month['revenue'] > 0
                                        ? max(
                                            4,
                                            ($month['revenue'] / $maxMonthlyRevenue) * 100
                                        )
                                        : 2;
                                @endphp

                                <div class="group relative flex h-full min-w-0 flex-1 flex-col justify-end">

                                    <div
                                        class="pointer-events-none absolute bottom-[calc({{ $height }}%+14px)] left-1/2 z-20 hidden -translate-x-1/2 whitespace-nowrap rounded-xl bg-[var(--admin-text)] px-3 py-2 text-[9px] font-medium text-white shadow-xl group-hover:block"
                                    >
                                        <div>
                                            {{ number_format($month['revenue']) }}
                                            تومان
                                        </div>

                                        <div class="mt-1 text-white/60">
                                            {{ number_format($month['orders']) }}
                                            سفارش
                                        </div>
                                    </div>

                                    <div
                                        class="mx-auto w-full max-w-12 rounded-t-2xl bg-gradient-to-t from-[var(--admin-accent-dark)] to-[var(--admin-accent)] transition duration-500 group-hover:from-[var(--admin-text)] group-hover:to-[var(--admin-accent)]"
                                        style="height: {{ $height }}%;"
                                    ></div>

                                    <p class="mt-3 truncate text-center text-[9px] font-semibold text-[var(--admin-text-soft)]">
                                        {{ $month['label'] }}
                                    </p>

                                    <p class="mt-1 text-center text-[8px] text-[var(--admin-muted)]">
                                        {{ number_format($month['orders']) }}
                                        سفارش
                                    </p>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            </section>


            <section class="admin-card">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        ORDER PIPELINE
                    </p>

                    <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                        قیف سفارش‌ها
                    </h2>

                    <p class="mt-1 text-xs text-[var(--admin-muted)]">
                        وضعیت فعلی تمام سفارش‌های ثبت‌شده.
                    </p>

                </div>

                <div class="space-y-4 p-6">

                    @foreach($statusLabels as $status => $label)

                        @php
                            $count = (int) ($orderStatus[$status] ?? 0);

                            $percent = $statusTotal > 0
                                ? round(
                                    ($count / $statusTotal) * 100
                                )
                                : 0;
                        @endphp

                        <div>

                            <div class="flex items-center justify-between gap-3">

                                <span class="inline-flex items-center gap-2 text-xs font-semibold text-[var(--admin-text-soft)]">
                                    <span class="h-2 w-2 rounded-full {{ str_contains($statusStyles[$status], 'amber') ? 'bg-amber-500' : (str_contains($statusStyles[$status], 'sky') ? 'bg-sky-500' : (str_contains($statusStyles[$status], 'indigo') ? 'bg-indigo-500' : (str_contains($statusStyles[$status], 'emerald') ? 'bg-emerald-500' : 'bg-red-500'))) }}"></span>
                                    {{ $label }}
                                </span>

                                <span class="text-xs font-bold text-[var(--admin-text)]">
                                    {{ number_format($count) }}
                                </span>

                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-[var(--admin-surface)]">

                                <div
                                    class="h-full rounded-full bg-[var(--admin-accent)] transition-all duration-700"
                                    style="width: {{ min(100, $percent) }}%;"
                                ></div>

                            </div>

                        </div>

                    @endforeach


                    <div class="mt-6 rounded-2xl bg-[var(--admin-surface)] p-4">

                        <div class="flex items-center justify-between gap-3">

                            <span class="text-xs text-[var(--admin-muted)]">
                                نرخ تحویل
                            </span>

                            <span class="text-sm font-bold text-[var(--admin-text)]">
                                {{ number_format($deliveredPercent) }}٪
                            </span>

                        </div>

                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">

                            <div
                                class="h-full rounded-full bg-emerald-600"
                                style="width: {{ min(100, $deliveredPercent) }}%;"
                            ></div>

                        </div>

                    </div>

                </div>

            </section>

        </div>


        {{-- =========================================================
             SELLERS + BUSINESS SNAPSHOT
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(20rem,.65fr)]">

            <section class="admin-card overflow-hidden">

                <div class="flex items-center justify-between gap-4 border-b border-[var(--admin-border)] p-6">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                            BEST SELLERS
                        </p>

                        <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                            پرفروش‌ترین محصولات
                        </h2>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            بر اساس تعداد اقلام فروخته‌شده در سفارش‌های پرداخت‌شده.
                        </p>

                    </div>

                    <a
                        href="{{ route('admin.products.index') }}"
                        class="text-xs font-medium text-[var(--admin-accent)]"
                    >
                        همه محصولات ←
                    </a>

                </div>


                <div class="p-6">

                    @if($topProducts->isNotEmpty())

                        <div class="space-y-4">

                            @foreach($topProducts as $index => $product)

                                <div class="flex items-center gap-4">

                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-xs font-bold text-[var(--admin-text)]">
                                        {{ number_format($index + 1) }}
                                    </div>

                                    <div class="admin-image-thumb shrink-0">
                                        @if($product['image'])
                                            <img
                                                src="{{ $product['image'] }}"
                                                alt="{{ $product['name'] }}"
                                                class="admin-image"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-[9px] tracking-widest text-[var(--admin-muted)]">
                                                LV
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">

                                        <div class="flex items-start justify-between gap-4">

                                            <div class="min-w-0">

                                                <p class="truncate text-sm font-semibold text-[var(--admin-text)]">
                                                    {{ $product['name'] }}
                                                </p>

                                                <p class="mt-1 text-[10px] text-[var(--admin-muted)]">
                                                    {{ number_format($product['quantity']) }}
                                                    عدد فروش
                                                </p>

                                            </div>

                                            <span class="shrink-0 text-xs font-bold text-[var(--admin-text)]">
                                                {{ number_format($product['revenue']) }}
                                                تومان
                                            </span>

                                        </div>

                                        <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-[var(--admin-surface)]">

                                            <div
                                                class="h-full rounded-full bg-gradient-to-l from-[var(--admin-accent)] to-[#d5b28f]"
                                                style="width: {{ max(4, ($product['quantity'] / max(1, $topProducts->max('quantity'))) * 100) }}%;"
                                            ></div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="py-12 text-center">
                            <p class="text-sm font-semibold text-[var(--admin-text)]">
                                هنوز فروش پرداخت‌شده‌ای ثبت نشده است.
                            </p>

                            <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                                بعد از اولین تراکنش موفق، این بخش به‌صورت خودکار پر می‌شود.
                            </p>
                        </div>

                    @endif

                </div>

            </section>


            <section class="admin-card">

                <div class="border-b border-[var(--admin-border)] p-6">

                    <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                        BUSINESS SNAPSHOT
                    </p>

                    <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                        نبض کسب‌وکار
                    </h2>

                </div>

                <div class="space-y-3 p-6">

                    <div class="rounded-2xl bg-[var(--admin-surface)] p-4">

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <p class="text-[10px] text-[var(--admin-muted)]">
                                    محصولات فعال
                                </p>

                                <p class="mt-2 text-xl font-bold text-[var(--admin-text)]">
                                    {{ number_format($activeProducts) }}
                                </p>
                            </div>

                            <span class="rounded-xl bg-white px-3 py-2 text-[10px] font-semibold text-[var(--admin-accent)]">
                                از {{ number_format($totalProducts) }}
                            </span>

                        </div>

                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-white">

                            <div
                                class="h-full rounded-full bg-[var(--admin-accent)]"
                                style="width: {{ $totalProducts > 0 ? min(100, ($activeProducts / $totalProducts) * 100) : 0 }}%;"
                            ></div>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                        <a
                            href="{{ route('admin.products.index', ['stock' => 'low_stock']) }}"
                            class="rounded-2xl border border-[var(--admin-border)] p-4 transition hover:border-amber-300 hover:bg-amber-50/40"
                        >
                            <p class="text-[10px] text-[var(--admin-muted)]">
                                کم‌موجودی
                            </p>

                            <p class="mt-2 text-xl font-bold text-amber-700">
                                {{ number_format($lowStockProducts) }}
                            </p>
                        </a>

                        <a
                            href="{{ route('admin.products.index', ['status' => 'draft']) }}"
                            class="rounded-2xl border border-[var(--admin-border)] p-4 transition hover:border-sky-300 hover:bg-sky-50/40"
                        >
                            <p class="text-[10px] text-[var(--admin-muted)]">
                                پیش‌نویس
                            </p>

                            <p class="mt-2 text-xl font-bold text-sky-700">
                                {{ number_format($draftProductsCount) }}
                            </p>
                        </a>

                        <a
                            href="{{ route('admin.product-variants.index') }}"
                            class="rounded-2xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]"
                        >
                            <p class="text-[10px] text-[var(--admin-muted)]">
                                تنوع‌های فعال
                            </p>

                            <p class="mt-2 text-xl font-bold text-[var(--admin-text)]">
                                {{ number_format($activeVariants) }}
                            </p>

                            <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                از {{ number_format($totalVariants) }} تنوع · {{ number_format($lowStockVariants) }} کم‌موجودی
                            </p>
                        </a>

                    </div>


                    <div class="rounded-2xl border border-[var(--admin-border)] p-4">

                        <div class="flex items-center justify-between gap-3">

                            <span class="text-xs font-semibold text-[var(--admin-text)]">
                                پرداخت‌های این ماه
                            </span>

                            <span class="text-xs font-bold text-[var(--admin-accent)]">
                                {{ number_format($monthPaidRate) }}٪
                            </span>

                        </div>

                        <p class="mt-2 text-[10px] leading-6 text-[var(--admin-muted)]">
                            {{ number_format($currentMonthPaidOrders) }}
                            پرداخت موفق از
                            {{ number_format($currentMonthOrders) }}
                            سفارش این ماه.
                        </p>

                    </div>


                    <div class="rounded-2xl border border-[var(--admin-border)] p-4">

                        <div class="flex items-center justify-between gap-3">

                            <span class="text-xs font-semibold text-[var(--admin-text)]">
                                فروش اقساطی
                            </span>

                            <span class="text-xs font-bold text-[var(--admin-accent)]">
                                {{ number_format($installmentPaidPercent) }}٪
                            </span>

                        </div>

                        <p class="mt-2 text-[10px] leading-6 text-[var(--admin-muted)]">
                            {{ number_format($installmentPaidOrders) }}
                            پرداخت شده از
                            {{ number_format($installmentOrders) }}
                            سفارش اقساطی.
                        </p>

                    </div>

                </div>

            </section>

        </div>


        {{-- =========================================================
             RECENT ORDERS + CUSTOMERS
        ========================================================== --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(20rem,.7fr)]">

            <section class="admin-card overflow-hidden">

                <div class="flex items-center justify-between gap-4 border-b border-[var(--admin-border)] p-6">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                            RECENT ORDERS
                        </p>

                        <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                            آخرین سفارش‌ها
                        </h2>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            آخرین عملیات ثبت‌شده در فروشگاه.
                        </p>

                    </div>

                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="text-xs font-medium text-[var(--admin-accent)]"
                    >
                        همه ←
                    </a>

                </div>


                <div class="overflow-x-auto">

                    <table class="admin-table min-w-[720px]">

                        <thead>
                        <tr>
                            <th>سفارش</th>
                            <th>مشتری</th>
                            <th>مبلغ</th>
                            <th>پرداخت</th>
                            <th>وضعیت</th>
                        </tr>
                        </thead>

                        <tbody>

                        @forelse($recentOrders as $order)

                            <tr>

                                <td>
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="font-mono text-xs font-bold text-[var(--admin-accent)]"
                                    >
                                        {{ $order->order_number }}
                                    </a>

                                    <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                        {{ $order->created_at?->format('Y/m/d H:i') }}
                                    </p>
                                </td>

                                <td>

                                    <p class="max-w-[14rem] truncate text-xs font-semibold text-[var(--admin-text)]">
                                        {{ $order->full_name ?: ($order->user?->name ?? 'بدون نام') }}
                                    </p>

                                    <p class="mt-1 max-w-[14rem] truncate text-[9px] text-[var(--admin-muted)]">
                                        {{ $order->email ?: ($order->user?->email ?? '—') }}
                                    </p>

                                </td>

                                <td>
                                    <span class="whitespace-nowrap text-xs font-bold text-[var(--admin-text)]">
                                        {{ number_format((float) $order->total) }}
                                    </span>

                                    <span class="mr-1 text-[9px] text-[var(--admin-muted)]">
                                        تومان
                                    </span>
                                </td>

                                <td>

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

                                </td>

                                <td>

                                    <a
                                        href="{{ route('admin.orders.show', $order) }}"
                                        class="inline-flex rounded-full px-3 py-1.5 text-[9px] font-semibold {{ $statusStyles[$order->status] ?? 'bg-gray-50 text-gray-700' }}"
                                    >
                                        {{ $statusLabels[$order->status] ?? $order->status }}
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5">
                                    <div class="admin-empty py-14">
                                        <p class="text-sm font-semibold text-[var(--admin-text)]">
                                            هنوز سفارشی ثبت نشده است.
                                        </p>

                                        <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                            با اولین سفارش، عملیات فروش اینجا نمایش داده می‌شود.
                                        </p>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </section>


            <section class="admin-card overflow-hidden">

                <div class="flex items-center justify-between gap-4 border-b border-[var(--admin-border)] p-6">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                            CUSTOMERS
                        </p>

                        <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                            مشتریان اخیر
                        </h2>

                    </div>

                    <a
                        href="{{ route('admin.customers.index') }}"
                        class="text-xs font-medium text-[var(--admin-accent)]"
                    >
                        همه ←
                    </a>

                </div>


                <div class="divide-y divide-[var(--admin-border)]">

                    @forelse($recentCustomers as $customer)

                        <a
                            href="{{ route('admin.customers.show', $customer) }}"
                            class="flex items-center gap-4 p-5 transition hover:bg-[var(--admin-surface)]"
                        >

                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[var(--admin-surface)] text-xs font-bold text-[var(--admin-text)]">
                                {{ mb_substr($customer->name ?: 'U', 0, 1) }}
                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="truncate text-sm font-semibold text-[var(--admin-text)]">
                                    {{ $customer->name ?: 'بدون نام' }}
                                </p>

                                <p class="mt-1 truncate text-[10px] text-[var(--admin-muted)]">
                                    {{ $customer->email ?: 'ایمیل ثبت نشده' }}
                                </p>

                            </div>

                            <div class="shrink-0 text-left">

                                <p class="text-[9px] text-[var(--admin-muted)]">
                                    ثبت‌نام
                                </p>

                                <p class="mt-1 text-[10px] text-[var(--admin-text-soft)]">
                                    {{ optional($customer->created_at)->format('Y/m/d') }}
                                </p>

                            </div>

                        </a>

                    @empty

                        <div class="px-6 py-14 text-center">
                            <p class="text-sm font-semibold text-[var(--admin-text)]">
                                هنوز مشتری جدیدی ثبت نشده است.
                            </p>
                        </div>

                    @endforelse

                </div>

            </section>

        </div>


        {{-- =========================================================
             QUICK ACTIONS
        ========================================================== --}}
        <section>

            <div class="mb-4">

                <p class="text-[10px] font-medium uppercase tracking-[0.18em] text-[var(--admin-accent)]">
                    QUICK ACTIONS
                </p>

                <h2 class="mt-2 text-lg font-bold text-[var(--admin-text)]">
                    عملیات سریع
                </h2>

            </div>


            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <a
                    href="{{ route('admin.products.create') }}"
                    class="admin-card admin-card-hover p-5"
                >
                    <div class="flex items-center gap-4">
                        <div class="admin-stat-icon">+</div>

                        <div>
                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                افزودن محصول
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                ثبت کالا و قیمت جدید
                            </p>
                        </div>
                    </div>
                </a>


                <a
                    href="{{ route('admin.product-images.create') }}"
                    class="admin-card admin-card-hover p-5"
                >
                    <div class="flex items-center gap-4">
                        <div class="admin-stat-icon">◈</div>

                        <div>
                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                افزودن تصویر
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                اتصال تصویر به محصول
                            </p>
                        </div>
                    </div>
                </a>


                <a
                    href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
                    class="admin-card admin-card-hover p-5"
                >
                    <div class="flex items-center gap-4">
                        <div class="admin-stat-icon">↗</div>

                        <div>
                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                پردازش سفارش
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                {{ number_format($pendingOrders) }} مورد در صف
                            </p>
                        </div>
                    </div>
                </a>


                <a
                    href="{{ route('admin.contact-messages.index', ['status' => 'unread']) }}"
                    class="admin-card admin-card-hover p-5"
                >
                    <div class="flex items-center gap-4">
                        <div class="admin-stat-icon">@</div>

                        <div>
                            <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                پیام‌های جدید
                            </h3>

                            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                                {{ number_format($unreadContactMessages) }} پیام خوانده‌نشده
                            </p>
                        </div>
                    </div>
                </a>

            </div>

        </section>

    </div>

@endsection
