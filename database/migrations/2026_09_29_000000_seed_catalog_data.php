<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::transaction(function () use ($now) {
            $categories = [
                [
                    'name' => 'مبلمان',
                    'slug' => 'furniture',
                    'description' => 'مبلمان مدرن و مینیمال',
                    'image' => null,
                    'is_active' => true,
                    'sort_order' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'صندلی',
                    'slug' => 'chairs',
                    'description' => 'صندلی‌های راحتی، دکوراتیو و ناهارخوری',
                    'image' => null,
                    'is_active' => true,
                    'sort_order' => 2,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'میز',
                    'slug' => 'tables',
                    'description' => 'میزهای پذیرایی، عسلی و ناهارخوری',
                    'image' => null,
                    'is_active' => true,
                    'sort_order' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'دکوراسیون',
                    'slug' => 'decor',
                    'description' => 'محصولات دکوراتیو برای فضای زندگی',
                    'image' => null,
                    'is_active' => true,
                    'sort_order' => 4,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'اکسسوری',
                    'slug' => 'accessories',
                    'description' => 'اکسسوری‌های کاربردی و دکوراتیو',
                    'image' => null,
                    'is_active' => true,
                    'sort_order' => 5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            DB::table('categories')->upsert(
                $categories,
                ['slug'],
                ['name', 'description', 'image', 'is_active', 'sort_order', 'updated_at']
            );

            $categoryIds = DB::table('categories')
                ->whereIn('slug', collect($categories)->pluck('slug'))
                ->pluck('id', 'slug');

            $products = [
                [
                    'category_id' => $categoryIds['furniture'],
                    'name' => 'Luna Sofa',
                    'slug' => 'luna-sofa',
                    'sku' => 'LIV-SOF-001',
                    'short_description' => 'مبل مینیمال با فرم نرم و مدرن',
                    'description' => 'مبل مدرن با طراحی نرم و مینیمال برای فضای نشیمن.',
                    'price' => 65000000,
                    'compare_at_price' => 72000000,
                    'stock' => 12,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => true,
                    'meta_title' => 'Luna Sofa | سیلاگالری',
                    'meta_description' => 'خرید Luna Sofa از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['chairs'],
                    'name' => 'Siena Lounge Chair',
                    'slug' => 'siena-lounge-chair',
                    'sku' => 'LIV-CHR-001',
                    'short_description' => 'صندلی راحتی با طراحی مدرن',
                    'description' => 'صندلی راحتی مدرن برای فضاهای مینیمال و لوکس.',
                    'price' => 28500000,
                    'compare_at_price' => 32000000,
                    'stock' => 18,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => true,
                    'meta_title' => 'Siena Lounge Chair | سیلاگالری',
                    'meta_description' => 'خرید Siena Lounge Chair از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['chairs'],
                    'name' => 'Milo Accent Chair',
                    'slug' => 'milo-accent-chair',
                    'sku' => 'LIV-CHR-002',
                    'short_description' => 'صندلی اکسنت با طراحی نرم و لوکس',
                    'description' => 'صندلی اکسنت برای تکمیل فضای نشیمن.',
                    'price' => 22400000,
                    'compare_at_price' => null,
                    'stock' => 10,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => false,
                    'meta_title' => 'Milo Accent Chair | سیلاگالری',
                    'meta_description' => 'خرید Milo Accent Chair از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['chairs'],
                    'name' => 'Oak Dining Chair',
                    'slug' => 'oak-dining-chair',
                    'sku' => 'LIV-CHR-003',
                    'short_description' => 'صندلی ناهارخوری از چوب بلوط',
                    'description' => 'صندلی ناهارخوری با ساختار چوبی مقاوم.',
                    'price' => 12800000,
                    'compare_at_price' => null,
                    'stock' => 24,
                    'status' => 'active',
                    'is_featured' => false,
                    'is_new' => true,
                    'meta_title' => 'Oak Dining Chair | سیلاگالری',
                    'meta_description' => 'خرید Oak Dining Chair از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['tables'],
                    'name' => 'Nordic Side Table',
                    'slug' => 'nordic-side-table',
                    'sku' => 'LIV-TBL-001',
                    'short_description' => 'میز عسلی با سبک اسکاندیناوی',
                    'description' => 'میز عسلی ساده و کاربردی برای کنار مبل.',
                    'price' => 8900000,
                    'compare_at_price' => 10500000,
                    'stock' => 15,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => true,
                    'meta_title' => 'Nordic Side Table | سیلاگالری',
                    'meta_description' => 'خرید Nordic Side Table از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['furniture'],
                    'name' => 'Mora Lounge Sofa',
                    'slug' => 'mora-lounge-sofa',
                    'sku' => 'LIV-SOF-002',
                    'short_description' => 'مبل بزرگ و راحت برای نشیمن',
                    'description' => 'مبل با فضای نشیمن عمیق و طراحی مدرن.',
                    'price' => 58700000,
                    'compare_at_price' => null,
                    'stock' => 8,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => false,
                    'meta_title' => 'Mora Lounge Sofa | سیلاگالری',
                    'meta_description' => 'خرید Mora Lounge Sofa از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['tables'],
                    'name' => 'Linea Coffee Table',
                    'slug' => 'linea-coffee-table',
                    'sku' => 'LIV-TBL-002',
                    'short_description' => 'میز جلو مبلی مینیمال',
                    'description' => 'میز جلو مبلی برای فضاهای مدرن و مینیمال.',
                    'price' => 16900000,
                    'compare_at_price' => null,
                    'stock' => 11,
                    'status' => 'active',
                    'is_featured' => true,
                    'is_new' => false,
                    'meta_title' => 'Linea Coffee Table | سیلاگالری',
                    'meta_description' => 'خرید Linea Coffee Table از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['decor'],
                    'name' => 'Arc Floor Lamp',
                    'slug' => 'arc-floor-lamp',
                    'sku' => 'LIV-DEC-001',
                    'short_description' => 'چراغ ایستاده دکوراتیو',
                    'description' => 'چراغ ایستاده برای نورپردازی گرم و مینیمال.',
                    'price' => 11900000,
                    'compare_at_price' => null,
                    'stock' => 20,
                    'status' => 'active',
                    'is_featured' => false,
                    'is_new' => true,
                    'meta_title' => 'Arc Floor Lamp | سیلاگالری',
                    'meta_description' => 'خرید Arc Floor Lamp از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'category_id' => $categoryIds['accessories'],
                    'name' => 'Stone Vase',
                    'slug' => 'stone-vase',
                    'sku' => 'LIV-ACC-001',
                    'short_description' => 'گلدان سنگی دست‌ساز',
                    'description' => 'گلدان دکوراتیو برای فضاهای آرام و مینیمال.',
                    'price' => 4200000,
                    'compare_at_price' => 4900000,
                    'stock' => 30,
                    'status' => 'active',
                    'is_featured' => false,
                    'is_new' => true,
                    'meta_title' => 'Stone Vase | سیلاگالری',
                    'meta_description' => 'خرید Stone Vase از سیلاگالری',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            DB::table('products')->upsert(
                $products,
                ['sku'],
                [
                    'category_id',
                    'name',
                    'slug',
                    'short_description',
                    'description',
                    'price',
                    'compare_at_price',
                    'stock',
                    'status',
                    'is_featured',
                    'is_new',
                    'meta_title',
                    'meta_description',
                    'updated_at',
                ]
            );

            $productIds = DB::table('products')
                ->whereIn('sku', collect($products)->pluck('sku'))
                ->pluck('id', 'sku');

            $variants = [
                ['product_id' => $productIds['LIV-SOF-001'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'cream', 'sku' => 'LIV-SOF-001-CREAM', 'price_adjustment' => 0, 'stock' => 5],
                ['product_id' => $productIds['LIV-SOF-001'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'charcoal', 'sku' => 'LIV-SOF-001-CHARCOAL', 'price_adjustment' => 0, 'stock' => 4],
                ['product_id' => $productIds['LIV-SOF-001'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'brown', 'sku' => 'LIV-SOF-001-BROWN', 'price_adjustment' => 1500000, 'stock' => 3],
                ['product_id' => $productIds['LIV-SOF-001'], 'type' => 'size', 'name' => 'سایز', 'value' => 'small', 'sku' => 'LIV-SOF-001-S', 'price_adjustment' => -5000000, 'stock' => 4],
                ['product_id' => $productIds['LIV-SOF-001'], 'type' => 'size', 'name' => 'سایز', 'value' => 'medium', 'sku' => 'LIV-SOF-001-M', 'price_adjustment' => 0, 'stock' => 5],
                ['product_id' => $productIds['LIV-SOF-001'], 'type' => 'size', 'name' => 'سایز', 'value' => 'large', 'sku' => 'LIV-SOF-001-L', 'price_adjustment' => 7000000, 'stock' => 3],
                ['product_id' => $productIds['LIV-CHR-001'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'beige', 'sku' => 'LIV-CHR-001-BEIGE', 'price_adjustment' => 0, 'stock' => 8],
                ['product_id' => $productIds['LIV-CHR-001'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'brown', 'sku' => 'LIV-CHR-001-BROWN', 'price_adjustment' => 1200000, 'stock' => 10],
                ['product_id' => $productIds['LIV-CHR-002'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'brown', 'sku' => 'LIV-CHR-002-BROWN', 'price_adjustment' => 0, 'stock' => 6],
                ['product_id' => $productIds['LIV-CHR-002'], 'type' => 'color', 'name' => 'رنگ', 'value' => 'cream', 'sku' => 'LIV-CHR-002-CREAM', 'price_adjustment' => 700000, 'stock' => 4],
            ];

            foreach ($variants as $variant) {
                $variant['is_active'] = true;
                $variant['created_at'] = $now;
                $variant['updated_at'] = $now;
            }

            DB::table('product_variants')->upsert(
                $variants,
                ['sku'],
                ['product_id', 'type', 'name', 'value', 'price_adjustment', 'stock', 'is_active', 'updated_at']
            );

            // Product/category images are intentionally empty here.
            // They are uploaded from the admin panel and stored in the media layer.
            DB::table('categories')->update(['image' => null]);
            DB::table('product_images')->delete();
            if (DB::getSchemaBuilder()->hasTable('product_variant_images')) {
                DB::table('product_variant_images')->delete();
            }
        });
    }

    public function down(): void
    {
        $skus = [
            'LIV-SOF-001',
            'LIV-CHR-001',
            'LIV-CHR-002',
            'LIV-CHR-003',
            'LIV-TBL-001',
            'LIV-SOF-002',
            'LIV-TBL-002',
            'LIV-DEC-001',
            'LIV-ACC-001',
        ];

        DB::transaction(function () use ($skus) {
            $productIds = DB::table('products')
                ->whereIn('sku', $skus)
                ->pluck('id');

            if ($productIds->isNotEmpty()) {
                DB::table('product_variant_images')
                    ->whereIn('product_image_id', function ($query) use ($productIds) {
                        $query->select('id')
                            ->from('product_images')
                            ->whereIn('product_id', $productIds);
                    })
                    ->delete();

                DB::table('product_images')
                    ->whereIn('product_id', $productIds)
                    ->delete();

                DB::table('product_variants')
                    ->whereIn('product_id', $productIds)
                    ->delete();

                DB::table('products')
                    ->whereIn('id', $productIds)
                    ->delete();
            }

            DB::table('categories')
                ->whereIn('slug', ['furniture', 'chairs', 'tables', 'decor', 'accessories'])
                ->delete();
        });
    }
};
