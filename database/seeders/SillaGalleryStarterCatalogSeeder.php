<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SillaGalleryStarterCatalogSeeder extends Seeder
{
    /**
     * Creates editable, unpublished starter records only.
     *
     * No demo orders, customer data, media, inventory or sale prices are added.
     * firstOrCreate makes repeated production runs non-destructive.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'مبلمان و نشیمن', 'slug' => 'living-room-furniture', 'description' => 'مجموعه مبلمان و عناصر اصلی فضای نشیمن SillaGallery.', 'sort_order' => 1],
            ['name' => 'میز و جلو مبلی', 'slug' => 'tables-and-coffee-tables', 'description' => 'میزهای جلو مبلی، عسلی و ناهارخوری برای فضاهای مختلف خانه.', 'sort_order' => 2],
            ['name' => 'صندلی', 'slug' => 'chairs', 'description' => 'صندلی‌های ناهارخوری، راحتی و دکوراتیو.', 'sort_order' => 3],
            ['name' => 'اتاق خواب', 'slug' => 'bedroom', 'description' => 'تخت و عناصر کاربردی و دکوراتیو اتاق خواب.', 'sort_order' => 4],
            ['name' => 'روشنایی و دکور', 'slug' => 'lighting-and-decor', 'description' => 'چراغ‌ها و عناصر دکوراتیو برای تکمیل فضای زندگی.', 'sort_order' => 5],
        ];

        $categoryIds = [];
        foreach ($categories as $data) {
            $category = Category::query()->firstOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'image' => null,
                    'is_active' => false,
                    'seo_index' => false,
                    'seo_follow' => false,
                ]
            );
            $categoryIds[$data['slug']] = $category->id;
        }

        $products = [
            [
                'category_slug' => 'living-room-furniture',
                'name' => 'مبل راحتی آوان سه‌نفره',
                'slug' => 'avan-three-seat-sofa',
                'sku' => 'SG-FUR-001',
                'short_description' => 'مبل سه‌نفره با طراحی ساده و مناسب فضای نشیمن.',
                'description' => 'پیش‌نویس اولیه محصول برای تکمیل مشخصات واقعی، ابعاد، نوع پارچه، رنگ‌بندی و تصاویر توسط تیم SillaGallery.',
            ],
            [
                'category_slug' => 'tables-and-coffee-tables',
                'name' => 'میز جلو مبلی آرتا',
                'slug' => 'arta-coffee-table',
                'sku' => 'SG-TBL-001',
                'short_description' => 'میز جلو مبلی با فرم مینیمال برای فضای پذیرایی.',
                'description' => 'پیش‌نویس اولیه محصول؛ جنس صفحه، ابعاد، رنگ، قیمت و تصاویر باید با کالای واقعی تطبیق داده شوند.',
            ],
            [
                'category_slug' => 'chairs',
                'name' => 'صندلی ناهارخوری وندا',
                'slug' => 'vanda-dining-chair',
                'sku' => 'SG-CHR-001',
                'short_description' => 'صندلی ناهارخوری با طراحی کاربردی و امروزی.',
                'description' => 'پیش‌نویس اولیه محصول؛ نوع چوب یا فلز، پوشش، ابعاد و رنگ‌بندی پس از تأیید محصول واقعی تکمیل شوند.',
            ],
            [
                'category_slug' => 'bedroom',
                'name' => 'تخت خواب دونفره نوا',
                'slug' => 'nova-double-bed',
                'sku' => 'SG-BED-001',
                'short_description' => 'تخت دونفره با طراحی ساده برای اتاق خواب مدرن.',
                'description' => 'پیش‌نویس اولیه محصول؛ ابعاد، متریال، تشک سازگار، رنگ و قیمت واقعی باید پیش از انتشار ثبت شوند.',
            ],
            [
                'category_slug' => 'lighting-and-decor',
                'name' => 'چراغ ایستاده آریا',
                'slug' => 'arya-floor-lamp',
                'sku' => 'SG-LGT-001',
                'short_description' => 'چراغ ایستاده دکوراتیو برای نورپردازی فضای نشیمن.',
                'description' => 'پیش‌نویس اولیه محصول؛ توان، نوع لامپ، جنس بدنه، ابعاد و تصویر محصول واقعی باید تکمیل شوند.',
            ],
        ];

        foreach ($products as $data) {
            $categoryId = $categoryIds[$data['category_slug']];
            unset($data['category_slug']);

            Product::query()->firstOrCreate(
                ['sku' => $data['sku']],
                [
                    ...$data,
                    'category_id' => $categoryId,
                    'price' => 0,
                    'compare_at_price' => null,
                    'stock' => 0,
                    'status' => 'draft',
                    'is_featured' => false,
                    'is_new' => false,
                    'meta_title' => $data['name'] . ' | SillaGallery',
                    'meta_description' => $data['short_description'],
                    'seo_index' => false,
                    'seo_follow' => false,
                ]
            );
        }
    }
}
