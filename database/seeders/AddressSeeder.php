<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@livora.test')->firstOrFail();

        Address::updateOrCreate(
            [
                'user_id' => $user->id,
                'postal_code' => '1234567890',
            ],
            [
                'title' => 'آدرس منزل',
                'first_name' => 'Demo',
                'last_name' => 'User',
                'phone' => '09120000000',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان ولیعصر، کوچه نمونه، پلاک ۱۲',
                'unit' => '4',
                'is_default' => true,
            ]
        );
    }
}
