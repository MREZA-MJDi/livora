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
                'email' => env('ADMIN_EMAIL', 'admin@silagallery.test'),
            ],
            [
                'name' => env('ADMIN_NAME', 'SilaGallery Admin'),
                'role' => 'admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'change-this-before-production')),
                'email_verified_at' => now(),
            ]
        );

        if (app()->environment(['local', 'testing'])) {
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
}
