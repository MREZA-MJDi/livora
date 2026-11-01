<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('focus_keyword', 120)->nullable()->after('meta_description');
            $table->json('content_sections')->nullable()->after('focus_keyword');
            $table->json('key_features')->nullable()->after('content_sections');
            $table->json('specifications')->nullable()->after('key_features');
            $table->text('care_guide')->nullable()->after('specifications');
            $table->json('faq_items')->nullable()->after('care_guide');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('image_alt', 255)->nullable()->after('image');
            $table->string('focus_keyword', 120)->nullable()->after('meta_description');
            $table->json('content_sections')->nullable()->after('focus_keyword');
            $table->json('comparison_rows')->nullable()->after('content_sections');
            $table->json('faq_items')->nullable()->after('comparison_rows');
        });

        Schema::create('category_related_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('related_category_id')->constrained('categories')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'related_category_id'], 'category_related_categories_unique');
            $table->index(['category_id', 'sort_order'], 'category_related_categories_order_index');
        });

        Schema::create('category_recommended_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category_id', 'product_id'], 'category_recommended_products_unique');
            $table->index(['category_id', 'sort_order'], 'category_recommended_products_order_index');
        });

        Schema::create('product_related_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'related_product_id'], 'product_related_products_unique');
            $table->index(['product_id', 'sort_order'], 'product_related_products_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related_products');
        Schema::dropIfExists('category_recommended_products');
        Schema::dropIfExists('category_related_categories');

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn(['image_alt', 'focus_keyword', 'content_sections', 'comparison_rows', 'faq_items']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['focus_keyword', 'content_sections', 'key_features', 'specifications', 'care_guide', 'faq_items']);
        });
    }
};
