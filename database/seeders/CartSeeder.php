<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@livora.test')->firstOrFail();

        $cart = Cart::updateOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'active',
            ],
            [
                'session_id' => null,
            ]
        );

        $product = Product::where('slug', 'luna-sofa')->firstOrFail();

        $cart->items()->updateOrCreate(
            [
                'product_id' => $product->id,
            ],
            [
                'product_variant_id' => null,
                'quantity' => 1,
                'unit_price' => $product->price,
            ]
        );
    }
}
