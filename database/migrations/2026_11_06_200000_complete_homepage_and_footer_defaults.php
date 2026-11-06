<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            [
                'key' => 'new-arrivals',
                'type' => 'products',
                'enabled' => true,
                'sort_order' => 35,
                'eyebrow' => 'تازه‌های فروشگاه',
                'title' => 'تازه رسیده به سیلا',
                'subtitle' => 'آخرین محصولاتی که به مجموعه فروشگاه اضافه شده‌اند.',
                'cta_label' => 'مشاهده همه محصولات',
                'cta_url' => '/shop',
                'image_path' => null,
                'payload' => json_encode(['product_source' => 'new', 'limit' => 6], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('home_sections')->insertOrIgnore($defaults);

        DB::table('home_sections')
            ->whereIn('key', ['hero', 'categories', 'featured', 'new-arrivals', 'trust', 'final-cta'])
            ->update(['enabled' => true, 'updated_at' => $now]);

        $footerMenu = [
            'shop' => [
                ['label' => 'همه محصولات', 'url' => '/shop'],
                ['label' => 'دسته‌بندی‌ها', 'url' => '/categories'],
                ['label' => 'تازه‌های فروشگاه', 'url' => '/shop?sort=newest'],
                ['label' => 'سبد خرید', 'url' => '/cart'],
            ],
            'guide' => [
                ['label' => 'قوانین و شرایط', 'url' => '/terms'],
                ['label' => 'راهنمای انتخاب', 'url' => '/buying-guide'],
                ['label' => 'پیگیری سفارش‌ها', 'url' => '/login'],
            ],
            'contact' => [
                ['label' => 'درباره سیلا', 'url' => '/about'],
                ['label' => 'مجله سیلا', 'url' => '/blog'],
                ['label' => 'تماس با ما', 'url' => '/contact'],
            ],
        ];

        DB::table('contact_settings')->whereNull('brand_description')->update([
            'brand_description' => 'سیلا گالری؛ مجموعه‌ای برای کشف و انتخاب آگاهانه مبلمان و عناصر خانه.',
        ]);

        DB::table('contact_settings')->whereNull('footer_menu')->update([
            'footer_menu' => json_encode($footerMenu, JSON_UNESCAPED_UNICODE),
        ]);

        DB::table('contact_settings')->whereNull('payment_methods')->update([
            'payment_methods' => json_encode([], JSON_UNESCAPED_UNICODE),
        ]);

        DB::table('contact_settings')->whereNull('copyright_text')->update([
            'copyright_text' => 'تمام حقوق برای سیلا گالری محفوظ است.',
        ]);
    }

    public function down(): void
    {
        DB::table('home_sections')->where('key', 'new-arrivals')->delete();
    }
};
