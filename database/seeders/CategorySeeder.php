<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'مبلمان',
                'slug' => 'furniture',
                'description' => 'مجموعه مبلمان مدرن و مینیمال LIVORA',
                'image' => 'categories/furniture.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'صندلی',
                'slug' => 'chairs',
                'description' => 'صندلی‌های راحتی، دکوراتیو و ناهارخوری',
                'image' => 'categories/chairs.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'میز',
                'slug' => 'tables',
                'description' => 'میزهای پذیرایی، عسلی و ناهارخوری',
                'image' => 'categories/tables.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'دکوراسیون',
                'slug' => 'decor',
                'description' => 'محصولات دکوراتیو برای فضای زندگی',
                'image' => 'categories/decor.jpg',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'اکسسوری',
                'slug' => 'accessories',
                'description' => 'اکسسوری‌های خاص برای تکمیل فضای شما',
                'image' => 'categories/accessories.jpg',
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
