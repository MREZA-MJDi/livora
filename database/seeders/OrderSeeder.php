<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@livora.test')->firstOrFail();

        Order::updateOrCreate(
            ['order_number' => 'LV-10001'],
            [
                'user_id' => $user->id,
                'status' => 'processing',
                'payment_status' => 'paid',

                'subtotal' => 87400000,
                'shipping_cost' => 0,
                'discount' => 0,
                'total' => 87400000,

                'first_name' => 'Demo',
                'last_name' => 'User',
                'phone' => '09120000000',
                'email' => 'demo@livora.test',

                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان ولیعصر، کوچه نمونه، پلاک ۱۲',
                'postal_code' => '1234567890',
                'unit' => '4',

                'notes' => 'سفارش تستی LIVORA',
            ]
        );
    }
}
