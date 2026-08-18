<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $order = Order::where('order_number', 'LV-10001')->firstOrFail();

        $products = [
            [
                'slug' => 'luna-sofa',
                'quantity' => 1,
            ],
            [
                'slug' => 'milo-accent-chair',
                'quantity' => 1,
            ],
        ];

        foreach ($products as $item) {
            $product = Product::where('slug', $item['slug'])->firstOrFail();

            OrderItem::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                ],
                [
                    'product_variant_id' => null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total' => $product->price * $item['quantity'],
                ]
            );
        }
    }
}
