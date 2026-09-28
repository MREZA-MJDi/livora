<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Order\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\Inventory\InventoryService;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with('user')
            ->latest();

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')
            );
        }

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->string('payment_status')
            );
        }

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function ($query) use ($search) {
                $query->where(
                    'order_number',
                    'like',
                    "%{$search}%"
                )
                    ->orWhere(
                        'first_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'last_name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'phone',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        $orders = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.orders.index',
            compact('orders')
        );
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items.product',
            'items.variant',
            'latestPayment',
            'installments'
        ]);

        return view(
            'admin.orders.show',
            compact('order')
        );
    }

    /**
     * Update the order status.
     */
    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        InventoryService $inventoryService
    ): RedirectResponse {
        $newStatus = $request->validated('status');

        try {
            if (
                $newStatus === 'cancelled'
                && $order->status !== 'cancelled'
            ) {
                $inventoryService->releaseOrder($order);
            }

            $order->update([
                'status' => $newStatus,
            ]);

            return redirect()
                ->route(
                    'admin.orders.show',
                    $order
                )
                ->with(
                    'success',
                    'وضعیت سفارش با موفقیت بروزرسانی شد.'
                );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with(
                    'error',
                    'تغییر وضعیت سفارش انجام نشد؛ وضعیت موجودی دست‌نخورده باقی ماند.'
                );
        }
    }
}
