<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Demo catalogue images, prices, stock, customers, orders, and payments
         * are fixtures, not customer-approved production data. They must never
         * be inserted or overwrite live catalogue records during a production
         * seed run.
         */
        if (app()->environment(['local', 'testing'])) {
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

            return;
        }

        if (app()->environment('production')) {
            // Production seeding is limited to safe administrator bootstrap.
            // UserSeeder preserves an existing admin and rejects unsafe defaults.
            $this->call([
                UserSeeder::class,
            ]);

            return;
        }

        throw new \\RuntimeException(
            'Database seeding is disabled for this environment. Use local/testing fixtures or the guarded production administrator bootstrap.'
        );
    }
}
