<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $furniture = Category::where('slug', 'furniture')->firstOrFail();
        $chairs = Category::where('slug', 'chairs')->firstOrFail();
        $tables = Category::where('slug', 'tables')->firstOrFail();
        $decor = Category::where('slug', 'decor')->firstOrFail();
        $accessories = Category::where('slug', 'accessories')->firstOrFail();

        $products = [
            [
                'category_id' => $furniture->id,
                'name' => 'Luna Sofa',
                'slug' => 'luna-sofa',
                'sku' => 'LIV-SOF-001',
                'short_description' => 'مبل مینیمال با فرم نرم و مدرن',
                'description' => 'Luna Sofa یکی از مدل‌های شاخص LIVORA با طراحی نرم و مینیمال است.',
                'price' => 65000000,
                'compare_at_price' => 72000000,
                'stock' => 12,
                'status' => 'active',
                'is_featured' => true,
                'is_new' => true,
                'meta_title' => 'Luna Sofa | LIVORA',
                'meta_description' => 'خرید Luna Sofa از LIVORA',
            ],
            [
                'category_id' => $chairs->id,
                'name' => 'Siena Lounge Chair',
                'slug' => 'siena-lounge-chair',
                'sku' => 'LIV-CHR-001',
                'short_description' => 'صندلی راحتی با طراحی مدرن',
                'description' => 'Siena Lounge Chair برای فضاهای لوکس و مینیمال طراحی شده است.',
                'price' => 28500000,
                'compare_at_price' => 32000000,
                'stock' => 18,
                'status' => 'active',
                'is_featured' => true,
                'is_new' => true,
                'meta_title' => 'Siena Lounge Chair | LIVORA',
                'meta_description' => 'خرید Siena Lounge Chair از LIVORA',
            ],
            [
                'category_id' => $chairs->id,
                'name' => 'Milo Accent Chair',
                'slug' => 'milo-accent-chair',
                'sku' => 'LIV-CHR-002',
                'short_description' => 'صندلی اکسنت با طراحی نرم و لوکس',
                'description' => 'Milo Accent Chair برای تکمیل فضای نشیمن طراحی شده است.',
                'price' => 22400000,
                'compare_at_price' => null,
                'stock' => 10,
                'status' => 'active',
                'is_featured' => true,
                'is_new' => false,
                'meta_title' => 'Milo Accent Chair | LIVORA',
                'meta_description' => 'خرید Milo Accent Chair از LIVORA',
            ],
            [
                'category_id' => $chairs->id,
                'name' => 'Oak Dining Chair',
                'slug' => 'oak-dining-chair',
                'sku' => 'LIV-CHR-003',
                'short_description' => 'صندلی ناهارخوری از چوب بلوط',
                'description' => 'Oak Dining Chair با ساختار چوبی مقاوم برای میزهای ناهارخوری.',
                'price' => 12800000,
                'compare_at_price' => null,
                'stock' => 24,
                'status' => 'active',
                'is_featured' => false,
                'is_new' => true,
                'meta_title' => 'Oak Dining Chair | LIVORA',
                'meta_description' => 'خرید Oak Dining Chair از LIVORA',
            ],
            [
                'category_id' => $tables->id,
                'name' => 'Nordic Side Table',
                'slug' => 'nordic-side-table',
                'sku' => 'LIV-TBL-001',
                'short_description' => 'میز عسلی با سبک اسکاندیناوی',
                'description' => 'میز Nordic با فرم ساده و کاربردی برای کنار مبل.',
                'price' => 8900000,
                'compare_at_price' => 10500000,
                'stock' => 15,
                'status' => 'active',
                'is_featured' => true,
                'is_new' => true,
                'meta_title' => 'Nordic Side Table | LIVORA',
                'meta_description' => 'خرید Nordic Side Table از LIVORA',
            ],
            [
                'category_id' => $furniture->id,
                'name' => 'Mora Lounge Sofa',
                'slug' => 'mora-lounge-sofa',
                'sku' => 'LIV-SOF-002',
                'short_description' => 'مبل بزرگ و راحت برای نشیمن',
                'description' => 'Mora Lounge Sofa با فضای نشیمن عمیق و طراحی مدرن.',
                'price' => 58700000,
                'compare_at_price' => null,
                'stock' => 8,
                'status' => 'active',
                'is_featured' => true,
                'is_new' => false,
                'meta_title' => 'Mora Lounge Sofa | LIVORA',
                'meta_description' => 'خرید Mora Lounge Sofa از LIVORA',
            ],
            [
                'category_id' => $tables->id,
                'name' => 'Linea Coffee Table',
                'slug' => 'linea-coffee-table',
                'sku' => 'LIV-TBL-002',
                'short_description' => 'میز جلو مبلی مینیمال',
                'description' => 'Linea Coffee Table برای فضاهای مدرن و مینیمال.',
                'price' => 16900000,
                'compare_at_price' => null,
                'stock' => 11,
                'status' => 'active',
                'is_featured' => true,
                'is_new' => false,
                'meta_title' => 'Linea Coffee Table | LIVORA',
                'meta_description' => 'خرید Linea Coffee Table از LIVORA',
            ],
            [
                'category_id' => $decor->id,
                'name' => 'Arc Floor Lamp',
                'slug' => 'arc-floor-lamp',
                'sku' => 'LIV-DEC-001',
                'short_description' => 'چراغ ایستاده دکوراتیو',
                'description' => 'Arc Floor Lamp برای نورپردازی گرم و مینیمال.',
                'price' => 11900000,
                'compare_at_price' => null,
                'stock' => 20,
                'status' => 'active',
                'is_featured' => false,
                'is_new' => true,
                'meta_title' => 'Arc Floor Lamp | LIVORA',
                'meta_description' => 'خرید Arc Floor Lamp از LIVORA',
            ],
            [
                'category_id' => $accessories->id,
                'name' => 'Stone Vase',
                'slug' => 'stone-vase',
                'sku' => 'LIV-ACC-001',
                'short_description' => 'گلدان سنگی دست‌ساز',
                'description' => 'Stone Vase برای فضاهای آرام و مینیمال.',
                'price' => 4200000,
                'compare_at_price' => 4900000,
                'stock' => 30,
                'status' => 'active',
                'is_featured' => false,
                'is_new' => true,
                'meta_title' => 'Stone Vase | LIVORA',
                'meta_description' => 'خرید Stone Vase از LIVORA',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
