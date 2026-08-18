<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Auth::user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('account.orders.index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'items.product.images',
            'items.variant',
            'latestPayment',
        ]);

        return view('account.orders.show', [
            'order' => $order,
        ]);
    }
}
