<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'مبلمان',
                'slug' => 'furniture',
                'description' => 'مجموعه مبلمان مدرن و مینیمال SilaGallery',
                'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=85',
                'is_active' => true,
                'sort_order' => 1,
            ],

            [
                'name' => 'صندلی',
                'slug' => 'chairs',
                'description' => 'صندلی‌های راحتی، دکوراتیو و ناهارخوری',
                'image' => 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=1200&q=85',
                'is_active' => true,
                'sort_order' => 2,
            ],

            [
                'name' => 'میز',
                'slug' => 'tables',
                'description' => 'میزهای پذیرایی، عسلی و ناهارخوری',
                'image' => 'https://images.unsplash.com/photo-1532372320572-cda25653a26d?auto=format&fit=crop&w=1200&q=85',
                'is_active' => true,
                'sort_order' => 3,
            ],

            [
                'name' => 'دکوراسیون',
                'slug' => 'decor',
                'description' => 'محصولات دکوراتیو برای فضای زندگی',
                'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1200&q=85',
                'is_active' => true,
                'sort_order' => 4,
            ],

            [
                'name' => 'اکسسوری',
                'slug' => 'accessories',
                'description' => 'اکسسوری‌های خاص برای تکمیل فضای شما',
                'image' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1200&q=85',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
