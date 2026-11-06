<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            InitialCategorySeeder::class,
            InitialProductSeeder::class,
            InitialStoreContentSeeder::class,
            InitialBlogSeeder::class,
        ]);
    }
}
