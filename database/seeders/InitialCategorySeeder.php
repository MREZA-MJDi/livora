<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class InitialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'مبل راحتی', 'slug' => 'مبل-راحتی', 'description' => 'مجموعه مبل‌های راحتی برای نشیمن روزمره و دورهمی‌های خانوادگی.', 'sort_order' => 1],
            ['name' => 'مبل چستر', 'slug' => 'مبل-چستر', 'description' => 'مبلمان با فرم کلاسیک چستر و جزئیات دکمه‌دوزی.', 'sort_order' => 2],
            ['name' => 'مبل مدرن', 'slug' => 'مبل-مدرن', 'description' => 'مبلمان با خطوط ساده و تناسبات امروزی برای خانه‌های مدرن.', 'sort_order' => 3],
            ['name' => 'میز ناهارخوری', 'slug' => 'میز-ناهارخوری', 'description' => 'میزهای غذاخوری برای فضاهای کوچک و بزرگ خانه.', 'sort_order' => 4],
            ['name' => 'تخت و اتاق خواب', 'slug' => 'تخت-و-اتاق-خواب', 'description' => 'تخت خواب و عناصر کاربردی برای چیدمان اتاق خواب.', 'sort_order' => 5],
            ['name' => 'میز عسلی و جلو مبلی', 'slug' => 'میز-عسلی-و-جلو-مبلی', 'description' => 'میزهای مکمل برای نظم و کاربرد بهتر فضای نشیمن.', 'sort_order' => 6],
            ['name' => 'آینه و دکور', 'slug' => 'آینه-و-دکور', 'description' => 'عناصر دکوراتیو برای تکمیل چیدمان خانه.', 'sort_order' => 7],
            ['name' => 'روشنایی', 'slug' => 'روشنایی', 'description' => 'چراغ‌ها و عناصر روشنایی برای نورپردازی لایه‌ای خانه.', 'sort_order' => 8],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['slug' => $category['slug']],
                [
                    ...$category,
                    'is_active' => true,
                    'seo_index' => true,
                    'seo_follow' => true,
                    'image' => null,
                ]
            );
        }
    }
}
