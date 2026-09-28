<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * This migration is deliberately defensive because MySQL may leave
         * earlier DDL statements applied when a later index definition fails.
         */
        if (! Schema::hasColumn('product_variants', 'color_hex')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->string('color_hex', 20)
                    ->nullable()
                    ->after('value');
            });
        }

        if (! Schema::hasTable('product_variant_images')) {
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
            });
        }

        $variantImageIndexes = collect(
            Schema::getIndexes('product_variant_images')
        )->pluck('name')->all();

        if (! in_array('pvi_variant_image_unique', $variantImageIndexes, true)) {
            Schema::table('product_variant_images', function (Blueprint $table) {
                $table->unique(
                    ['product_variant_id', 'product_image_id'],
                    'pvi_variant_image_unique'
                );
            });
        }

        if (! in_array('pvi_image_sort_idx', $variantImageIndexes, true)) {
            Schema::table('product_variant_images', function (Blueprint $table) {
                $table->index(
                    ['product_image_id', 'sort_order'],
                    'pvi_image_sort_idx'
                );
            });
        }

        if (! Schema::hasColumn('cart_items', 'variant_key')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('variant_key')
                    ->nullable()
                    ->after('product_variant_id');
            });
        }

        if (! Schema::hasColumn('cart_items', 'variant_options')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->json('variant_options')
                    ->nullable()
                    ->after('variant_key');
            });
        }

        $cartIndexes = collect(
            Schema::getIndexes('cart_items')
        )->pluck('name')->all();

        if (! in_array('cart_product_variant_idx', $cartIndexes, true)) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index(
                    ['cart_id', 'product_id', 'variant_key'],
                    'cart_product_variant_idx'
                );
            });
        }

        if (! Schema::hasColumn('order_items', 'variant_options')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->json('variant_options')
                    ->nullable()
                    ->after('product_variant_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'variant_options')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('variant_options');
            });
        }

        if (Schema::hasTable('cart_items')) {
            $cartIndexes = collect(
                Schema::getIndexes('cart_items')
            )->pluck('name')->all();

            if (in_array('cart_product_variant_idx', $cartIndexes, true)) {
                Schema::table('cart_items', function (Blueprint $table) {
                    $table->dropIndex('cart_product_variant_idx');
                });
            }

            $columns = [];

            if (Schema::hasColumn('cart_items', 'variant_key')) {
                $columns[] = 'variant_key';
            }

            if (Schema::hasColumn('cart_items', 'variant_options')) {
                $columns[] = 'variant_options';
            }

            if ($columns !== []) {
                Schema::table('cart_items', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        Schema::dropIfExists('product_variant_images');

        if (Schema::hasColumn('product_variants', 'color_hex')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('color_hex');
            });
        }
    }
};
