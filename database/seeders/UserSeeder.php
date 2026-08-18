<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@livora.test',
            ],
            [
                'name' => 'LIVORA Admin',
                'role' => 'admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            [
                'email' => 'demo@livora.test',
            ],
            [
                'name' => 'Demo User',
                'role' => 'customer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
