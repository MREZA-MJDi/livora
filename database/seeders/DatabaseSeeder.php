<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Core catalog data is safe to seed in every environment so a fresh
        // installation has sample products, variants, categories, and images.
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ProductImageSeeder::class,
            ProductVariantSeeder::class,
            UserSeeder::class,
        ]);

        // Demo customer/order data is intentionally limited to local/testing.
        // Production must start without fake carts, wishlists, orders, or payments.
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                AddressSeeder::class,
                CartSeeder::class,
                WishlistSeeder::class,
                OrderSeeder::class,
                OrderItemSeeder::class,
                PaymentSeeder::class,
            ]);
        }
    }
}
