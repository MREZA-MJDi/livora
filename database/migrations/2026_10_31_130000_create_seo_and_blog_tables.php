<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('seo_index')->default(false);
            $table->boolean('seo_follow')->default(false);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->boolean('seo_index')->default(false);
            $table->boolean('seo_follow')->default(false);
        });

        Schema::create('seo_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('indexing_enabled')->default(false);
            $table->boolean('following_enabled')->default(false);
            $table->string('site_name')->default('SillaGallery');
            $table->string('default_title')->nullable();
            $table->text('default_description')->nullable();
            $table->timestamps();
        });

        DB::table('seo_settings')->insert([
            'id' => 1,
            'indexing_enabled' => false,
            'following_enabled' => false,
            'site_name' => 'SillaGallery',
            'default_title' => 'SillaGallery | مبلمان و لوازم خانه',
            'default_description' => 'فروشگاه SillaGallery؛ انتخاب مبلمان و لوازم خانه با طراحی و کیفیت ماندگار.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('seo_index')->default(false);
            $table->boolean('seo_follow')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
            $table->index(['seo_index', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('seo_settings');

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['seo_index', 'seo_follow']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['seo_index', 'seo_follow']);
        });
    }
};
