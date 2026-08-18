<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@livora.test')->firstOrFail();

        $products = Product::whereIn('slug', [
            'luna-sofa',
            'siena-lounge-chair',
            'nordic-side-table',
        ])->get();

        foreach ($products as $product) {
            $user->wishlists()->firstOrCreate([
                'product_id' => $product->id,
            ]);
        }
    }
}
