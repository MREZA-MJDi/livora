<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\JalaliHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Collect product KPIs in one aggregate query rather than issuing
        // an independent COUNT query for every card.
        $productStats = Product::query()
            ->selectRaw("
                COUNT(*) AS total_products,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_products,
                SUM(CASE WHEN stock > 0 AND stock <= 5 THEN 1 ELSE 0 END) AS low_stock_products,
                SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS out_of_stock_products,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) AS featured_products,
                SUM(CASE WHEN is_new = 1 THEN 1 ELSE 0 END) AS new_products,
                SUM(CASE WHEN installment_enabled = 1 THEN 1 ELSE 0 END) AS installment_products
            ")
            ->first();

        $now = now();
        [$jalaliYear, $jalaliMonth] = JalaliHelper::toJalali(
            (int) $now->format('Y'),
            (int) $now->format('n'),
            (int) $now->format('j')
        );

        $monthRanges = [];
        $orderSelects = [
            'COUNT(*) AS total_orders',
            "SUM(CASE WHEN status IN ('pending', 'processing') THEN 1 ELSE 0 END) AS pending_orders",
            "SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_orders",
            "SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) AS total_revenue",
            "SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS status_pending",
            "SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) AS status_processing",
            "SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) AS status_shipped",
            "SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS status_delivered",
            "SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS status_cancelled",
            "SUM(CASE WHEN payment_method = 'installment' THEN 1 ELSE 0 END) AS installment_orders",
            "SUM(CASE WHEN payment_method = 'installment' AND payment_status = 'paid' THEN 1 ELSE 0 END) AS installment_paid_orders",
            "AVG(CASE WHEN payment_status = 'paid' THEN total ELSE NULL END) AS average_order_value",
        ];
        $orderBindings = [];

        // The six chart buckets follow Jalali month boundaries. Each bucket
        // is queried as [Gregorian start, next Gregorian start), avoiding
        // end-of-month precision and timezone edge cases.
        for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
            $monthIndex = ($jalaliYear * 12) + ($jalaliMonth - 1) - $monthsAgo;
            $year = intdiv($monthIndex, 12);
            $month = ($monthIndex % 12) + 1;

            $start = JalaliHelper::monthStart($year, $month, $now->getTimezone());
            $nextMonthIndex = $monthIndex + 1;
            $nextYear = intdiv($nextMonthIndex, 12);
            $nextMonth = ($nextMonthIndex % 12) + 1;
            $end = JalaliHelper::monthStart($nextYear, $nextMonth, $now->getTimezone());

            $key = sprintf('j%04d_%02d', $year, $month);
            $monthRanges[] = [
                'key' => $key,
                'year' => $year,
                'month' => $month,
            ];

            $orderSelects[] = "SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) AS all_orders_{$key}";
            $orderBindings[] = $start->format('Y-m-d H:i:s');
            $orderBindings[] = $end->format('Y-m-d H:i:s');

            $orderSelects[] = "SUM(CASE WHEN payment_status = 'paid' AND created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) AS orders_{$key}";
            $orderBindings[] = $start->format('Y-m-d H:i:s');
            $orderBindings[] = $end->format('Y-m-d H:i:s');

            $orderSelects[] = "SUM(CASE WHEN payment_status = 'paid' AND created_at >= ? AND created_at < ? THEN total ELSE 0 END) AS revenue_{$key}";
            $orderBindings[] = $start->format('Y-m-d H:i:s');
            $orderBindings[] = $end->format('Y-m-d H:i:s');
        }

        $orderStats = Order::query()
            ->selectRaw(implode(",\n", $orderSelects), $orderBindings)
            ->first();

        $totalCustomers = User::query()
            ->where('role', 'customer')
            ->count();

        $currentMonthRange = $monthRanges[count($monthRanges) - 1];
        $previousMonthRange = $monthRanges[count($monthRanges) - 2];

        $currentMonthRevenue = (float) (
            $orderStats->{'revenue_'.$currentMonthRange['key']} ?? 0
        );
        $previousMonthRevenue = (float) (
            $orderStats->{'revenue_'.$previousMonthRange['key']} ?? 0
        );
        $currentMonthOrders = (int) (
            $orderStats->{'all_orders_'.$currentMonthRange['key']} ?? 0
        );

        $revenueGrowthPercent = 0;

        if ($previousMonthRevenue > 0) {
            $revenueGrowthPercent = round(
                (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100,
                1
            );
        } elseif ($currentMonthRevenue > 0) {
            $revenueGrowthPercent = 100;
        }

        $monthlyRevenue = collect($monthRanges)
            ->map(function (array $range) use ($orderStats): array {
                $key = $range['key'];

                return [
                    'key' => $key,
                    'label' => JalaliHelper::monthName($range['month']),
                    'full_label' => JalaliHelper::formatMonthYear($range['year'], $range['month']),
                    'revenue' => (float) ($orderStats->{'revenue_'.$key} ?? 0),
                    'orders' => (int) ($orderStats->{'orders_'.$key} ?? 0),
                ];
            });

        $topProducts = $this->getTopProducts();

        $recentOrders = Order::query()
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $recentCustomers = User::query()
            ->where('role', 'customer')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', [
            'totalProducts' => (int) ($productStats->total_products ?? 0),
            'activeProducts' => (int) ($productStats->active_products ?? 0),
            'lowStockProducts' => (int) ($productStats->low_stock_products ?? 0),
            'outOfStockProducts' => (int) ($productStats->out_of_stock_products ?? 0),

            'totalCustomers' => $totalCustomers,

            'totalOrders' => (int) ($orderStats->total_orders ?? 0),
            'pendingOrders' => (int) ($orderStats->pending_orders ?? 0),
            'paidOrders' => (int) ($orderStats->paid_orders ?? 0),

            'totalRevenue' => (float) ($orderStats->total_revenue ?? 0),
            'currentMonthRevenue' => $currentMonthRevenue,
            'currentMonthOrders' => $currentMonthOrders,
            'previousMonthRevenue' => $previousMonthRevenue,
            'revenueGrowthPercent' => $revenueGrowthPercent,
            'averageOrderValue' => $orderStats->average_order_value,

            'monthlyRevenue' => $monthlyRevenue,

            'orderStatus' => [
                'pending' => (int) ($orderStats->status_pending ?? 0),
                'processing' => (int) ($orderStats->status_processing ?? 0),
                'shipped' => (int) ($orderStats->status_shipped ?? 0),
                'delivered' => (int) ($orderStats->status_delivered ?? 0),
                'cancelled' => (int) ($orderStats->status_cancelled ?? 0),
            ],

            'topProducts' => $topProducts,

            'installmentOrders' => (int) ($orderStats->installment_orders ?? 0),
            'installmentPaidOrders' => (int) ($orderStats->installment_paid_orders ?? 0),

            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,

            'featuredProductsCount' => (int) ($productStats->featured_products ?? 0),
            'newProductsCount' => (int) ($productStats->new_products ?? 0),
            'installmentProductsCount' => (int) ($productStats->installment_products ?? 0),
        ]);
    }

    /**
     * Get best selling products from paid orders.
     */
    protected function getTopProducts(): Collection
    {
        return OrderItem::query()
            ->with([
                'product.images' => fn ($imageQuery) => $imageQuery->limit(1),
            ])
            ->whereHas(
                'order',
                fn ($query) => $query->where('payment_status', 'paid')
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
}
