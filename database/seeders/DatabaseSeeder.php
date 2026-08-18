<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,

            ProductSeeder::class,
            ProductImageSeeder::class,
            ProductVariantSeeder::class,

            UserSeeder::class,
            AddressSeeder::class,

            CartSeeder::class,
            WishlistSeeder::class,

            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
