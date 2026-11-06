<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use App\Models\HomeSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class HomepageContentSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'key' => 'hero',
                'type' => 'hero',
                'sort_order' => 10,
                'eyebrow' => 'سیلا گالری | مبلمان و دکوراسیون',
                'title' => 'برای خانه‌تان انتخابی ماندگار پیدا کنید.',
                'subtitle' => 'مجموعه‌ای از مبلمان و عناصر خانه؛ تصاویر، مشخصات و شرایط هر محصول را بررسی کنید و متناسب با فضای زندگی‌تان انتخاب کنید.',
                'cta_label' => 'ورود به فروشگاه',
                'cta_url' => '/shop',
                'payload' => ['product_source' => 'latest', 'limit' => 6],
            ],
            [
                'key' => 'categories',
                'type' => 'categories',
                'sort_order' => 20,
                'eyebrow' => 'خرید بر اساس فضای خانه',
                'title' => 'دسته‌بندی‌های محبوب سیلا',
                'subtitle' => 'چهار دسته‌بندی پرمحصول را ببینید؛ برای مرور همه گروه‌ها وارد صفحه دسته‌بندی‌ها شوید.',
                'cta_label' => 'مشاهده همه دسته‌بندی‌ها',
                'cta_url' => '/categories',
                'payload' => ['limit' => 4],
            ],
            [
                'key' => 'featured',
                'type' => 'products',
                'sort_order' => 30,
                'eyebrow' => 'انتخاب‌های سیلا',
                'title' => 'محصولات منتخب',
                'subtitle' => 'محصولات فعال و منتخب فروشگاه؛ همراه با قیمت و وضعیت موجودی ثبت‌شده.',
                'cta_label' => 'مشاهده همه محصولات',
                'cta_url' => '/shop',
                'payload' => ['product_source' => 'featured', 'limit' => 4],
            ],
            [
                'key' => 'new-arrivals',
                'type' => 'products',
                'sort_order' => 40,
                'eyebrow' => 'تازه‌های فروشگاه',
                'title' => 'تازه‌رسیده‌ها',
                'subtitle' => 'جدیدترین محصولاتی که در کاتالوگ فروشگاه ثبت شده‌اند.',
                'cta_label' => 'دیدن تازه‌ها',
                'cta_url' => '/shop?sort=newest',
                'payload' => ['product_source' => 'new', 'limit' => 4],
            ],
            [
                'key' => 'trust',
                'type' => 'trust',
                'sort_order' => 50,
                'eyebrow' => 'پیش از انتخاب',
                'title' => 'خرید آگاهانه با اطلاعات روشن',
                'subtitle' => 'پیش از ثبت سفارش، جزئیات محصول و شرایط نهایی را بررسی کنید.',
                'payload' => [
                    'items' => [
                        ['title' => 'مشخصات محصول', 'body' => 'ابعاد، جنس، رنگ‌بندی و ویژگی‌های ثبت‌شده را در صفحه هر محصول بررسی کنید.'],
                        ['title' => 'قیمت و موجودی', 'body' => 'قیمت و وضعیت موجودی نمایش‌داده‌شده را پیش از ثبت سفارش دوباره بررسی کنید.'],
                        ['title' => 'هماهنگی سفارش', 'body' => 'برای زمان تحویل، ارسال و جزئیات سفارشی، پیش از پرداخت با فروشگاه هماهنگ کنید.'],
                    ],
                ],
            ],
            [
                'key' => 'final-cta',
                'type' => 'cta',
                'sort_order' => 70,
                'eyebrow' => 'قدم بعدی',
                'title' => 'فضای خانه‌تان را با انتخابی درست کامل کنید.',
                'subtitle' => 'محصولات را ببینید، مشخصات را مقایسه کنید و برای جزئیات سفارش با سیلا گالری در ارتباط باشید.',
                'cta_label' => 'مشاهده فروشگاه',
                'cta_url' => '/shop',
            ],
        ];

        foreach ($sections as $attributes) {
            $key = $attributes['key'];
            $payload = $attributes['payload'] ?? null;
            unset($attributes['key'], $attributes['payload']);

            $section = HomeSection::query()->firstOrCreate(
                ['key' => $key],
                [...$attributes, 'payload' => $payload, 'enabled' => true],
            );

            $section->forceFill([
                'enabled' => true,
                'sort_order' => $attributes['sort_order'],
            ]);

            // Keep the hero tied to six real, latest catalogue products.
            if ($key === 'hero') {
                $section->payload = ['product_source' => 'latest', 'limit' => 6];
            } elseif ($key === 'featured') {
                $section->payload = ['product_source' => 'featured', 'limit' => 4];
            } elseif ($key === 'new-arrivals') {
                $section->payload = ['product_source' => 'new', 'limit' => 4];
            } elseif ($key === 'categories' && ! is_array($section->payload)) {
                $section->payload = ['limit' => 8];
            }

            $section->save();
        }

        $settings = ContactSetting::query()->first();

        if (! $settings) {
            $settings = ContactSetting::query()->create([
                'brand_name' => 'سیلا گالری',
                'is_active' => true,
                'floating_actions_enabled' => false,
            ]);
        }

        $settings->forceFill([
            'brand_description' => $settings->brand_description ?: 'سیلا گالری؛ مجموعه‌ای برای بررسی و انتخاب مبلمان و عناصر خانه متناسب با فضای زندگی شما.',
            'footer_menu' => $settings->footer_menu ?: [
                'shop' => [
                    ['label' => 'همه محصولات', 'url' => '/shop'],
                    ['label' => 'دسته‌بندی‌ها', 'url' => '/categories'],
                    ['label' => 'تازه‌های فروشگاه', 'url' => '/shop?sort=newest'],
                    ['label' => 'سبد خرید', 'url' => '/cart'],
                ],
                'guide' => [
                    ['label' => 'راهنمای انتخاب مبلمان', 'url' => '/buying-guide'],
                    ['label' => 'قوانین و شرایط', 'url' => '/terms'],
                    ['label' => 'پیگیری سفارش', 'url' => '/login'],
                ],
                'contact' => [
                    ['label' => 'درباره سیلا گالری', 'url' => '/about'],
                    ['label' => 'مجله سیلا', 'url' => '/blog'],
                    ['label' => 'تماس با ما', 'url' => '/contact'],
                ],
            ],
            'payment_methods' => $settings->payment_methods ?? [],
            'copyright_text' => $settings->copyright_text ?: 'تمام حقوق برای سیلا گالری محفوظ است.',
        ])->save();

        Cache::forget('storefront.settings');
    }
}
