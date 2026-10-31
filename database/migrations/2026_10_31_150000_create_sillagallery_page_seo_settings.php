<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_page_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('route_key', 80)->unique();
            $table->string('path', 255);
            $table->string('label', 120);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('seo_index')->default(false);
            $table->boolean('seo_follow')->default(false);
            $table->timestamps();
            $table->index(['seo_index', 'route_key']);
        });

        $now = now();
        DB::table('seo_page_settings')->insert([
            ['route_key'=>'home','path'=>'/','label'=>'صفحه اصلی','meta_title'=>'SillaGallery | مبلمان و لوازم خانه','meta_description'=>'فروشگاه SillaGallery؛ انتخاب مبلمان و عناصر خانه با طراحی و کیفیت ماندگار.','seo_index'=>false,'seo_follow'=>false,'created_at'=>$now,'updated_at'=>$now],
            ['route_key'=>'shop','path'=>'/shop','label'=>'فروشگاه','meta_title'=>'فروشگاه SillaGallery | محصولات','meta_description'=>'محصولات و مجموعه‌های SillaGallery را بررسی کنید.','seo_index'=>false,'seo_follow'=>false,'created_at'=>$now,'updated_at'=>$now],
            ['route_key'=>'categories','path'=>'/categories','label'=>'دسته‌بندی‌ها','meta_title'=>'دسته‌بندی محصولات | SillaGallery','meta_description'=>'دسته‌بندی‌های مبلمان و لوازم خانه در SillaGallery.','seo_index'=>false,'seo_follow'=>false,'created_at'=>$now,'updated_at'=>$now],
            ['route_key'=>'blog','path'=>'/blog','label'=>'مجله و وبلاگ','meta_title'=>'مجله SillaGallery','meta_description'=>'راهنماها و مطالب SillaGallery درباره مبلمان و فضای زندگی.','seo_index'=>false,'seo_follow'=>false,'created_at'=>$now,'updated_at'=>$now],
            ['route_key'=>'contact','path'=>'/contact','label'=>'تماس با ما','meta_title'=>'تماس با SillaGallery','meta_description'=>'راه‌های ارتباط با فروشگاه SillaGallery.','seo_index'=>false,'seo_follow'=>false,'created_at'=>$now,'updated_at'=>$now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_page_settings');
    }
};