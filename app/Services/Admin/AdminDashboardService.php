<?php

namespace App\Services\Admin;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderInstallment;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function build(int $months = 6): array
    {
        $months = in_array($months, [3, 6, 12], true) ? $months : 6;
        $now = now();

        $sales = $this->salesSummary($now);
        $monthlyRevenue = $this->monthlyRevenue($months, $now);
        $orderStatus = $this->orderStatus();
        $inventory = $this->inventorySummary();
        $installments = $this->installmentSummary();

        $recentOrders = Order::query()
            ->with(['user', 'latestPayment'])
            ->latest()
            ->limit(6)
            ->get();

        $recentCustomers = User::query()
            ->where('role', 'customer')
            ->latest()
            ->limit(5)
            ->get();

        $unreadMessages = ContactMessage::query()
            ->where('status', 'unread')
            ->count();

        $pendingOrders = $orderStatus['pending'] + $orderStatus['processing'];

        $deliveredBase =
            $orderStatus['pending']
            + $orderStatus['processing']
            + $orderStatus['shipped']
            + $orderStatus['delivered'];

        $deliveredPercent = $deliveredBase > 0
            ? round(
                ($orderStatus['delivered'] / $deliveredBase) * 100
            )
            : 0;

        return [
            'todayLabel' => $this->persianDate(
                $now,
                'EEEE، d MMMM yyyy'
            ),
            'rangeMonths' => $months,
            'sales' => $sales,
            'monthlyRevenue' => $monthlyRevenue,

            // Backward-compatible view keys.
            'totalProducts' => $inventory['totalProducts'],
            'activeProducts' => $inventory['activeProducts'],
            'lowStockProducts' => $inventory['lowStockProducts'],
            'outOfStockProducts' => $inventory['outOfStockProducts'],
            'totalCustomers' => User::query()
                ->where('role', 'customer')
                ->count(),
            'totalOrders' => Order::query()->count(),
            'pendingOrders' => $pendingOrders,
            'paidOrders' => $sales['paidOrderCount'],
            'totalRevenue' => $sales['netRevenue'],
            'currentMonthRevenue' => $sales['currentMonthRevenue'],
            'currentMonthOrders' => $sales['currentMonthOrders'],
            'previousMonthRevenue' => $sales['previousMonthRevenue'],
            'revenueGrowthPercent' => $sales['revenueGrowthPercent'],
            'averageOrderValue' => $sales['averageOrderValue'],
            'orderStatus' => $orderStatus,
            'deliveredPercent' => $deliveredPercent,
            'inventory' => $inventory,
            'installments' => $installments,
            'installmentOrders' => $installments['orders'],
            'installmentPaidOrders' => Order::query()
                ->where('payment_method', 'installment')
                ->where('payment_status', 'paid')
                ->count(),
            'totalCustomers' => User::query()
                ->where('role', 'customer')
                ->count(),
            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,
            'topProducts' => $this->topProducts(),
            'featuredProductsCount' => Product::query()
                ->where('is_featured', true)
                ->count(),
            'newProductsCount' => Product::query()
                ->where('is_new', true)
                ->count(),
            'installmentProductsCount' => Product::query()
                ->where('installment_enabled', true)
                ->count(),
            'unreadMessages' => $unreadMessages,
            'pendingOrders' => $pendingOrders,
            'attentionItems' => [
                [
                    'key' => 'orders',
                    'label' => 'سفارش نیازمند اقدام',
                    'count' => $pendingOrders,
                    'description' => $pendingOrders > 0
                        ? 'سفارش‌های در انتظار یا در حال پردازش را بررسی کنید.'
                        : 'در حال حاضر سفارشی در صف اقدام نیست.',
                    'href' => route(
                        'admin.orders.index',
                        ['status' => 'pending']
                    ),
                    'tone' => 'warning',
                ],
                [
                    'key' => 'low-stock',
                    'label' => 'کم‌موجودی',
                    'count' => $inventory['lowStockProducts'],
                    'description' => $inventory['lowStockProducts'] > 0
                        ? 'قبل از فروش بیشتر، موجودی این محصولات را بررسی کنید.'
                        : 'هیچ محصولی در محدوده کم‌موجودی نیست.',
                    'href' => route(
                        'admin.products.index',
                        ['stock' => 'low']
                    ),
                    'tone' => 'warning',
                ],
                [
                    'key' => 'out-of-stock',
                    'label' => 'ناموجود',
                    'count' => $inventory['outOfStockProducts'],
                    'description' => $inventory['outOfStockProducts'] > 0
                        ? 'این محصولات فعلاً قابل فروش نیستند.'
                        : 'همه محصولات دارای موجودی هستند.',
                    'href' => route(
                        'admin.products.index',
                        ['stock' => 'out']
                    ),
                    'tone' => 'danger',
                ],
                [
                    'key' => 'messages',
                    'label' => 'پیام خوانده‌نشده',
                    'count' => $unreadMessages,
                    'description' => $unreadMessages > 0
                        ? 'پیام‌های مشتریان را پاسخ دهید.'
                        : 'پیام خوانده‌نشده‌ای ندارید.',
                    'href' => route('admin.contact-messages.index'),
                    'tone' => 'info',
                ],
            ],
        ];
    }

    protected function salesSummary(Carbon $now): array
    {
        /*
         * A refunded payment leaves the paid state in this schema.
         * Gross sales therefore includes both paid and refunded rows;
         * refunds are then subtracted exactly once.
         */
        $grossRevenue = (float) Payment::query()
            ->whereIn('status', [
                'paid',
                'refunded',
            ])
            ->sum('amount');

        $refundedRevenue = (float) Payment::query()
            ->where('status', 'refunded')
            ->sum('amount');

        $netRevenue = round(
            $grossRevenue - $refundedRevenue,
            2
        );

        $currentMonthRevenue = $this->netRevenueBetween(
            $now->copy()->startOfMonth(),
            $now->copy()->endOfMonth()
        );

        $previousMonthRevenue = $this->netRevenueBetween(
            $now->copy()->subMonth()->startOfMonth(),
            $now->copy()->subMonth()->endOfMonth()
        );

        $revenueGrowthPercent = $previousMonthRevenue > 0
            ? round(
                (
                    ($currentMonthRevenue - $previousMonthRevenue)
                    / $previousMonthRevenue
                ) * 100,
                1
            )
            : ($currentMonthRevenue > 0 ? 100 : 0);

        $paidOrderCount = Payment::query()
            ->where('status', 'paid')
            ->select('order_id')
            ->distinct()
            ->count('order_id');

        $averageOrderValue = $paidOrderCount > 0
            ? round($netRevenue / $paidOrderCount, 2)
            : 0;

        $currentMonthOrders = Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->select('order_id')
            ->distinct()
            ->count('order_id');

        return [
            'grossRevenue' => $grossRevenue,
            'refundedRevenue' => $refundedRevenue,
            'netRevenue' => $netRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'previousMonthRevenue' => $previousMonthRevenue,
            'revenueGrowthPercent' => $revenueGrowthPercent,
            'paidOrderCount' => $paidOrderCount,
            'currentMonthOrders' => $currentMonthOrders,
            'averageOrderValue' => $averageOrderValue,
        ];
    }

    protected function netRevenueBetween(Carbon $from, Carbon $to): float
    {
        $paid = (float) Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        $refunded = (float) Payment::query()
            ->where('status', 'refunded')
            ->whereBetween('refunded_at', [$from, $to])
            ->sum('amount');

        return round($paid - $refunded, 2);
    }

    protected function monthlyRevenue(int $months, Carbon $end): Collection
    {
        $start = $end->copy()
            ->startOfMonth()
            ->subMonths($months - 1);

        $rows = Payment::query()
            ->whereIn('status', ['paid', 'refunded'])
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereBetween('paid_at', [$start, $end])
                    ->orWhereBetween('refunded_at', [$start, $end]);
            })
            ->selectRaw(
                "DATE_FORMAT(
                    CASE
                        WHEN status = 'refunded' THEN refunded_at
                        ELSE paid_at
                    END,
                    '%Y-%m'
                ) as period,
                SUM(
                    CASE
                        WHEN status = 'refunded' THEN -amount
                        ELSE amount
                    END
                ) as revenue,
                COUNT(
                    DISTINCT order_id
                ) as orders"
            )
            ->groupBy('period')
            ->get()
            ->keyBy('period');

        return collect(range(0, $months - 1))
            ->map(function (int $offset) use ($start, $rows) {
                $date = $start->copy()->addMonths($offset);
                $key = $date->format('Y-m');

                return [
                    'key' => $key,
                    'label' => $this->persianDate($date, 'LLLL'),
                    'full_label' => $this->persianDate($date, 'MMMM yyyy'),
                    'revenue' => (float) data_get($rows->get($key), 'revenue', 0),
                    'orders' => (int) data_get($rows->get($key), 'orders', 0),
                ];
            });
    }

    protected function orderStatus(): array
    {
        return [
            'pending' => Order::query()->where('status', 'pending')->count(),
            'processing' => Order::query()->where('status', 'processing')->count(),
            'shipped' => Order::query()->where('status', 'shipped')->count(),
            'delivered' => Order::query()->where('status', 'delivered')->count(),
            'cancelled' => Order::query()->where('status', 'cancelled')->count(),
        ];
    }

    protected function inventorySummary(): array
    {
        $inventoryRetailValue = (float) Product::query()
            ->sum(DB::raw('price * stock'));

        $variantStockUnits = (int) ProductVariant::query()
            ->where('is_active', true)
            ->sum('stock');

        return [
            'totalProducts' => Product::query()->count(),
            'activeProducts' => Product::query()
                ->where('status', 'active')
                ->count(),
            'lowStockProducts' => Product::query()
                ->where('stock', '>', 0)
                ->where('stock', '<=', 5)
                ->count(),
            'outOfStockProducts' => Product::query()
                ->where('stock', 0)
                ->count(),
            'stockUnits' => (int) Product::query()->sum('stock'),
            'inventoryRetailValue' => $inventoryRetailValue,
            'variantStockUnits' => $variantStockUnits,
            'featuredProducts' => Product::query()
                ->where('is_featured', true)
                ->count(),
            'newProducts' => Product::query()
                ->where('is_new', true)
                ->count(),
        ];
    }

    protected function installmentSummary(): array
    {
        $orders = Order::query()->where('payment_method', 'installment');

        return [
            'orders' => (clone $orders)->count(),
            'activePlans' => OrderInstallment::query()
                ->where('status', 'pending')
                ->distinct('order_id')
                ->count('order_id'),
            'overdue' => OrderInstallment::query()
                ->where('status', 'pending')
                ->whereDate('due_date', '<', today())
                ->count(),
            'paidInstallments' => OrderInstallment::query()
                ->where('status', 'paid')
                ->count(),
        ];
    }

    protected function topProducts(): Collection
    {
        return OrderItem::query()
            ->with(['product.images'])
            ->whereHas(
                'order',
                fn ($query) => $query->where(
                    'payment_status',
                    'paid'
                )
            )
            ->selectRaw(
                'product_id,
                 product_name,
                 SUM(quantity) as sold_quantity,
                 SUM(total) as revenue'
            )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,
                    'quantity' => (int) $item->sold_quantity,
                    'revenue' => (float) $item->revenue,
                    'image' => $item->product?->images?->first()?->url,
                ];
            });
    }

    protected function persianDate(?Carbon $date, string $pattern): string
    {
        if (! $date) {
            return '—';
        }

        if (class_exists(\IntlDateFormatter::class)) {
            $formatter = new \IntlDateFormatter(
                'fa_IR@calendar=persian',
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE,
                $date->getTimezone()->getName(),
                \IntlDateFormatter::TRADITIONAL,
                $pattern,
            );

            $formatted = $formatter->format($date->getTimestamp());

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return $date->locale('fa')->translatedFormat('Y/m/d');
    }
}
