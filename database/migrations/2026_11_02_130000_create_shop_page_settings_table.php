<?php

use Illuminate\Database\\Migrations\\Migration;
use Illuminate\Database\Schema\\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_page_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('eyebrow', 120)->default('SILLAGALLERY COLLECTION');
            $table->string('title', 255)->default('برای خانه‌ای با امضای شما');
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_image_alt', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('shop_page_settings')->insert([
            'id' => 1,
            'eyebrow' => 'SILLAGALLERY COLLECTION',
            'title' => 'برای خانه‌ای با امضای شما',
            'description' => 'مجموعه‌ای از مبلمان و عناصر خانه را ببینید، مقایسه کنید و انتخابی متناسب با فضای زندگی خود داشته باشید.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_page_settings');
    }
};
