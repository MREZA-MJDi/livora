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
                'image' => '/images/seed/categories/furniture.svg',
                'is_active' => true,
                'sort_order' => 1,
            ],

            [
                'name' => 'صندلی',
                'slug' => 'chairs',
                'description' => 'صندلی‌های راحتی، دکوراتیو و ناهارخوری',
                'image' => '/images/seed/categories/chairs.svg',
                'is_active' => true,
                'sort_order' => 2,
            ],

            [
                'name' => 'میز',
                'slug' => 'tables',
                'description' => 'میزهای پذیرایی، عسلی و ناهارخوری',
                'image' => '/images/seed/categories/tables.svg',
                'is_active' => true,
                'sort_order' => 3,
            ],

            [
                'name' => 'دکوراسیون',
                'slug' => 'decor',
                'description' => 'محصولات دکوراتیو برای فضای زندگی',
                'image' => '/images/seed/categories/decor.svg',
                'is_active' => true,
                'sort_order' => 4,
            ],

            [
                'name' => 'اکسسوری',
                'slug' => 'accessories',
                'description' => 'اکسسوری‌های خاص برای تکمیل فضای شما',
                'image' => '/images/seed/categories/accessories.svg',
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
