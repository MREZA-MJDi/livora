<?php

namespace App\Services\Admin;

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

        $totalProducts = Product::query()->count();

        $activeProducts = Product::query()
            ->where('status', 'active')
            ->count();

        $lowStockProducts = Product::query()
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->count();

        $outOfStockProducts = Product::query()
            ->where('stock', 0)
            ->count();

        $totalCustomers = User::query()
            ->where('role', 'customer')
            ->count();

        $totalOrders = Order::query()->count();

        $pendingOrders = Order::query()
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $paidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->count();

        $totalRevenue = (float) Order::query()
            ->where('payment_status', 'paid')
            ->sum('total');

        $currentMonthRevenue = (float) Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->sum('total');

        $currentMonthOrders = Order::query()
            ->whereBetween('created_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])
            ->count();

        $previousMonthRevenue = (float) Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ])
            ->sum('total');

        $revenueGrowthPercent = $previousMonthRevenue > 0
            ? round(
                (
                    ($currentMonthRevenue - $previousMonthRevenue)
                    / $previousMonthRevenue
                ) * 100,
                1
            )
            : ($currentMonthRevenue > 0 ? 100 : 0);

        $monthlyRevenue = $this->monthlyRevenue($now);

        $orderStatus = [
            'pending' => Order::query()
                ->where('status', 'pending')
                ->count(),
            'processing' => Order::query()
                ->where('status', 'processing')
                ->count(),
            'shipped' => Order::query()
                ->where('status', 'shipped')
                ->count(),
            'delivered' => Order::query()
                ->where('status', 'delivered')
                ->count(),
            'cancelled' => Order::query()
                ->where('status', 'cancelled')
                ->count(),
        ];

        $topProducts = $this->topProducts();

        $installmentOrders = Order::query()
            ->where('payment_method', 'installment')
            ->count();

        $installmentPaidOrders = Order::query()
            ->where('payment_method', 'installment')
            ->where('payment_status', 'paid')
            ->count();

        $featuredProductsCount = Product::query()
            ->where('is_featured', true)
            ->count();

        $newProductsCount = Product::query()
            ->where('is_new', true)
            ->count();

        $installmentProductsCount = Product::query()
            ->where('installment_enabled', true)
            ->count();

        $averageOrderValue = (float) (
            Order::query()
                ->where('payment_status', 'paid')
                ->avg('total') ?? 0
        );

        return [
            'todayLabel' => $now->locale('fa')->translatedFormat('l، d F Y'),

            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'lowStockProducts' => $lowStockProducts,
            'outOfStockProducts' => $outOfStockProducts,

            'totalCustomers' => $totalCustomers,

            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'paidOrders' => $paidOrders,

            'totalRevenue' => $totalRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'currentMonthOrders' => $currentMonthOrders,
            'previousMonthRevenue' => $previousMonthRevenue,
            'revenueGrowthPercent' => $revenueGrowthPercent,
            'averageOrderValue' => $averageOrderValue,

            'monthlyRevenue' => $monthlyRevenue,
            'orderStatus' => $orderStatus,
            'topProducts' => $topProducts,

            'installmentOrders' => $installmentOrders,
            'installmentPaidOrders' => $installmentPaidOrders,

            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,

            'featuredProductsCount' => $featuredProductsCount,
            'newProductsCount' => $newProductsCount,
            'installmentProductsCount' => $installmentProductsCount,
        ];
    }

    protected function monthlyRevenue(Carbon $now): Collection
    {
        return collect(range(5, 0))
            ->map(function (int $offset) use ($now) {
                $date = $now->copy()->subMonths($offset);

                $revenue = Order::query()
                    ->where('payment_status', 'paid')
                    ->whereBetween('created_at', [
                        $date->copy()->startOfMonth(),
                        $date->copy()->endOfMonth(),
                    ])
                    ->sum('total');

                $orders = Order::query()
                    ->where('payment_status', 'paid')
                    ->whereBetween('created_at', [
                        $date->copy()->startOfMonth(),
                        $date->copy()->endOfMonth(),
                    ])
                    ->count();

                return [
                    'key' => $date->format('Y-m'),
                    'label' => $date->locale('fa')->translatedFormat('M'),
                    'full_label' => $date->locale('fa')->translatedFormat('F Y'),
                    'revenue' => (float) $revenue,
                    'orders' => $orders,
                ];
            });
    }

    protected function topProducts(): Collection
    {
        return OrderItem::query()
            ->with('product.images')
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
}
