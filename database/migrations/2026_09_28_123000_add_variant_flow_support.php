<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('color_hex', 20)
                ->nullable()
                ->after('value');
        });

        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('product_image_id')
                ->constrained('product_images')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->unique([
                'product_variant_id',
                'product_image_id',
            ]);

            $table->index([
                'product_image_id',
                'sort_order',
            ]);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('variant_key')
                ->nullable()
                ->after('product_variant_id');

            $table->json('variant_options')
                ->nullable()
                ->after('variant_key');

            $table->index([
                'cart_id',
                'product_id',
                'variant_key',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('variant_options')
                ->nullable()
                ->after('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('variant_options');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex([
                'cart_id',
                'product_id',
                'variant_key',
            ]);

            $table->dropColumn([
                'variant_key',
                'variant_options',
            ]);
        });

        Schema::dropIfExists('product_variant_images');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('color_hex');
        });
    }
};
