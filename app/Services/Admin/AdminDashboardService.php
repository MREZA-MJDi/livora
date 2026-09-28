<?php

namespace App\Services\Admin;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminDashboardService
{
    public function build(): array
    {
        $now = now();

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $previousMonthStart = $now
            ->copy()
            ->subMonth()
            ->startOfMonth();

        $previousMonthEnd = $now
            ->copy()
            ->subMonth()
            ->endOfMonth();

        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Recent operational data
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with([
                'user:id,name,email',
                'latestPayment',
            ])
            ->latest('id')
            ->limit(8)
            ->get();

        $recentCustomers = User::query()
            ->where('role', 'customer')
            ->latest('id')
            ->limit(6)
            ->get([
                'id',
                'name',
                'email',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Catalog health
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::query()->count();

        $activeProducts = Product::query()
            ->where('status', 'active')
            ->count();

        $draftProductsCount = Product::query()
            ->where('status', 'draft')
            ->count();

        $lowStockProducts = Product::query()
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();

        $outOfStockProducts = Product::query()
            ->where('stock', '<=', 0)
            ->count();

        $featuredProductsCount = Product::query()
            ->where('is_featured', true)
            ->count();

        $installmentProductsCount = Product::query()
            ->where('installment_enabled', true)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        $totalCustomers = User::query()
            ->where('role', 'customer')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Order / revenue KPIs
        |--------------------------------------------------------------------------
        */

        $totalOrders = Order::query()->count();

        $pendingOrders = Order::query()
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $paidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->count();

        $pendingPaymentOrders = Order::query()
            ->where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->count();

        $totalRevenue = (float) (
            Order::query()
                ->where('payment_status', 'paid')
                ->sum('total') ?? 0
        );

        $todayRevenue = (float) (
            Order::query()
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [
                    $todayStart,
                    $todayEnd,
                ])
                ->sum('total') ?? 0
        );

        $todayOrders = Order::query()
            ->whereBetween('created_at', [
                $todayStart,
                $todayEnd,
            ])
            ->count();

        $currentMonthRevenue = (float) (
            Order::query()
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [
                    $monthStart,
                    $monthEnd,
                ])
                ->sum('total') ?? 0
        );

        $currentMonthOrders = Order::query()
            ->whereBetween('created_at', [
                $monthStart,
                $monthEnd,
            ])
            ->count();

        $currentMonthPaidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [
                $monthStart,
                $monthEnd,
            ])
            ->count();

        $previousMonthRevenue = (float) (
            Order::query()
                ->where('payment_status', 'paid')
                ->whereBetween('created_at', [
                    $previousMonthStart,
                    $previousMonthEnd,
                ])
                ->sum('total') ?? 0
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

        $averageOrderValue = (float) (
            Order::query()
                ->where('payment_status', 'paid')
                ->avg('total') ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Status aggregation
        |--------------------------------------------------------------------------
        */

        $statusCounts = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate_count')
            ->groupBy('status')
            ->pluck('aggregate_count', 'status');

        $orderStatus = [
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'processing' => (int) ($statusCounts['processing'] ?? 0),
            'shipped' => (int) ($statusCounts['shipped'] ?? 0),
            'delivered' => (int) ($statusCounts['delivered'] ?? 0),
            'cancelled' => (int) ($statusCounts['cancelled'] ?? 0),
        ];

        /*
        |--------------------------------------------------------------------------
        | Installments / communications
        |--------------------------------------------------------------------------
        */

        $installmentOrders = Order::query()
            ->where('payment_method', 'installment')
            ->count();

        $installmentPaidOrders = Order::query()
            ->where('payment_method', 'installment')
            ->where('payment_status', 'paid')
            ->count();

        $unreadContactMessages = ContactMessage::query()
            ->where('status', 'unread')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Business analytics
        |--------------------------------------------------------------------------
        */

        $monthlyRevenue = $this->monthlyRevenue($now);

        $topProducts = $this->topProducts();

        return [
            'todayLabel' => $now
                ->locale('fa')
                ->translatedFormat('l، d F Y'),

            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'draftProductsCount' => $draftProductsCount,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,
            'featuredProductsCount' => $featuredProductsCount,
            'installmentProductsCount' => $installmentProductsCount,

            'totalCustomers' => $totalCustomers,

            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'pendingPaymentOrders' => $pendingPaymentOrders,
            'paidOrders' => $paidOrders,

            'totalRevenue' => $totalRevenue,
            'todayRevenue' => $todayRevenue,
            'todayOrders' => $todayOrders,

            'currentMonthRevenue' => $currentMonthRevenue,
            'currentMonthOrders' => $currentMonthOrders,
            'currentMonthPaidOrders' => $currentMonthPaidOrders,
            'previousMonthRevenue' => $previousMonthRevenue,
            'revenueGrowthPercent' => $revenueGrowthPercent,
            'averageOrderValue' => $averageOrderValue,

            'monthlyRevenue' => $monthlyRevenue,
            'orderStatus' => $orderStatus,
            'topProducts' => $topProducts,

            'installmentOrders' => $installmentOrders,
            'installmentPaidOrders' => $installmentPaidOrders,

            'unreadContactMessages' => $unreadContactMessages,

            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,
        ];
    }

    protected function monthlyRevenue(Carbon $now): Collection
    {
        $start = $now
            ->copy()
            ->subMonths(5)
            ->startOfMonth();

        $end = $now
            ->copy()
            ->endOfMonth();

        $rows = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as month_key,
                 SUM(total) as revenue,
                 COUNT(*) as orders"
            )
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');

        return collect(range(5, 0))
            ->map(function (int $offset) use ($now, $rows) {
                $date = $now
                    ->copy()
                    ->subMonths($offset);

                $key = $date->format('Y-m');
                $row = $rows->get($key);

                return [
                    'key' => $key,
                    'label' => $date
                        ->locale('fa')
                        ->translatedFormat('M'),

                    'full_label' => $date
                        ->locale('fa')
                        ->translatedFormat('F Y'),

                    'revenue' => (float) (
                        $row?->revenue ?? 0
                    ),

                    'orders' => (int) (
                        $row?->orders ?? 0
                    ),
                ];
            });
    }

    protected function topProducts(): Collection
    {
        return OrderItem::query()
            ->with('product.primaryImage')
            ->whereHas(
                'order',
                fn ($query) => $query
                    ->where('payment_status', 'paid')
            )
            ->selectRaw(
                'product_id,
                 product_name,
                 SUM(quantity) as sold_quantity,
                 SUM(total) as revenue'
            )
            ->groupBy(
                'product_id',
                'product_name'
            )
            ->orderByDesc('sold_quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name' => $item->product_name,
                    'quantity' => (int) $item->sold_quantity,
                    'revenue' => (float) $item->revenue,
                    'image' => $item
                        ->product
                        ?->primaryImage
                        ?->url,
                ];
            });
    }
}
