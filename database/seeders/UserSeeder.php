<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminName = trim((string) env('ADMIN_NAME', ''));
        $adminEmail = strtolower(trim((string) env('ADMIN_EMAIL', '')));
        $adminPassword = (string) env('ADMIN_PASSWORD', '');

        if (app()->environment('production')) {
            if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    'Set a valid ADMIN_EMAIL to identify the production administrator.'
                );
            }

            $existingAdmin = User::query()
                ->where('email', $adminEmail)
                ->first();

            if ($existingAdmin) {
                if ($existingAdmin->role !== 'admin') {
                    throw new \RuntimeException(
                        'ADMIN_EMAIL belongs to a non-admin account. Choose a dedicated administrator email.'
                    );
                }

                // Never reset a live administrator password or role via db:seed.
                return;
            }

            if (
                $adminName === ''
                || mb_strlen($adminPassword) < 16
                || in_array(strtolower($adminPassword), [
                    'password',
                    'change-this-before-production',
                    'change-me',
                    'secret',
                ], true)
            ) {
                throw new \RuntimeException(
                    'For a new production administrator, set ADMIN_NAME and a unique ADMIN_PASSWORD of at least 16 characters.'
                );
            }

            User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]);

            return;
        }

        // Local and test fixtures may be reset to predictable credentials.
        $adminPassword = $adminPassword !== '' ? $adminPassword : 'password';
        $adminEmail = $adminEmail !== '' ? $adminEmail : 'admin@livora.test';
        $adminName = $adminName !== '' ? $adminName : 'Livora Admin';

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        if (app()->environment(['local', 'testing'])) {
            User::updateOrCreate(
                ['email' => 'demo@livora.test'],
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
