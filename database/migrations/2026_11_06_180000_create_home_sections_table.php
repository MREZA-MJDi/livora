<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('type', 40);
            $table->boolean('enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('eyebrow', 160)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('subtitle')->nullable();
            $table->string('cta_label', 120)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->string('image_path', 2048)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('home_sections')->insert([
            ['key' => 'hero', 'type' => 'hero', 'enabled' => true, 'sort_order' => 10, 'eyebrow' => 'مبلمان و دکوراسیون', 'title' => 'خانه‌ای که شبیه توست.', 'subtitle' => 'مجموعه‌ای منتخب از مبلمان و عناصر خانه برای ساختن فضایی گرم و ماندگار.', 'cta_label' => 'کشف مجموعه', 'cta_url' => '/shop', 'image_path' => null, 'payload' => json_encode(['product_source' => 'latest', 'limit' => 5]), 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'categories', 'type' => 'categories', 'enabled' => true, 'sort_order' => 20, 'eyebrow' => 'خرید بر اساس فضای خانه', 'title' => 'برای هر گوشه، یک انتخاب دقیق.', 'subtitle' => 'دسته‌بندی مناسب فضای خودتان را پیدا کنید.', 'cta_label' => null, 'cta_url' => null, 'image_path' => null, 'payload' => json_encode(['limit' => 8]), 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'featured', 'type' => 'products', 'enabled' => true, 'sort_order' => 30, 'eyebrow' => 'انتخاب‌های سیلا', 'title' => 'محصولات منتخب', 'subtitle' => 'محصولاتی که برای بررسی بیشتر انتخاب شده‌اند.', 'cta_label' => 'مشاهده همه', 'cta_url' => '/shop', 'image_path' => null, 'payload' => json_encode(['product_source' => 'latest', 'limit' => 8]), 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'trust', 'type' => 'trust', 'enabled' => true, 'sort_order' => 40, 'eyebrow' => 'تجربه خرید', 'title' => 'اطلاعات روشن، انتخاب آگاهانه', 'subtitle' => 'مشخصات، موجودی و شرایط سفارش هر محصول را پیش از خرید بررسی کنید.', 'cta_label' => null, 'cta_url' => null, 'image_path' => null, 'payload' => json_encode(['items' => [['title' => 'انتخاب دقیق', 'body' => 'بررسی مشخصات و تصاویر هر محصول'], ['title' => 'اطلاعات شفاف', 'body' => 'مشاهده قیمت و وضعیت موجودی'], ['title' => 'پشتیبانی', 'body' => 'ثبت پرسش از مسیر تماس با فروشگاه']]]), 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'final-cta', 'type' => 'cta', 'enabled' => true, 'sort_order' => 50, 'eyebrow' => 'سیلا گالری', 'title' => 'برای خانه‌تان انتخابی ماندگار پیدا کنید.', 'subtitle' => 'مجموعه محصولات را ببینید و جزئیات هر انتخاب را بررسی کنید.', 'cta_label' => 'ورود به فروشگاه', 'cta_url' => '/shop', 'image_path' => null, 'payload' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};
