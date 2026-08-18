<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::where('order_number', 'LV-10001')
            ->firstOrFail();

        Payment::updateOrCreate(
            [
                'order_id' => $order->id,
                'transaction_id' => 'DEMO-TXN-10001',
            ],
            [
                'user_id' => $order->user_id,
                'gateway' => 'demo',
                'authority' => 'DEMO-AUTH-10001',
                'amount' => $order->total,
                'status' => 'paid',
                'paid_at' => now(),
                'metadata' => [
                    'mode' => 'testing',
                    'message' => 'Demo payment for LIVORA',
                ],
            ]
        );
    }
}
