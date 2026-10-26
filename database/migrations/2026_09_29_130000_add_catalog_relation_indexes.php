<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicate = DB::table('product_variants')
            ->select([
                'product_id',
                'type',
                'value',
            ])
            ->groupBy([
                'product_id',
                'type',
                'value',
            ])
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate) {
            throw new \RuntimeException(
                'Duplicate product variant values exist for the same product/type. Resolve them before adding the unique constraint.'
            );
        }

        $categoryIndexes = collect(
            Schema::getIndexes('categories')
        )->pluck('name')->all();

        if (! in_array('categories_active_position_name_index', $categoryIndexes, true)) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index(
                    ['is_active', 'sort_order', 'name'],
                    'categories_active_position_name_index'
                );
            });
        }

        $variantIndexes = collect(
            Schema::getIndexes('product_variants')
        )->pluck('name')->all();

        if (! in_array('product_variants_product_active_type_index', $variantIndexes, true)) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->index(
                    ['product_id', 'is_active', 'type'],
                    'product_variants_product_active_type_index'
                );
            });
        }

        if (! in_array('product_variants_stock_active_index', $variantIndexes, true)) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->index(
                    ['stock', 'is_active'],
                    'product_variants_stock_active_index'
                );
            });
        }

        if (! in_array('product_variants_product_type_value_unique', $variantIndexes, true)) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->unique(
                    ['product_id', 'type', 'value'],
                    'product_variants_product_type_value_unique'
                );
            });
        }

        $pivotIndexes = collect(
            Schema::getIndexes('product_variant_images')
        )->pluck('name')->all();

        if (! in_array('pvi_variant_sort_index', $pivotIndexes, true)) {
            Schema::table('product_variant_images', function (Blueprint $table) {
                $table->index(
                    ['product_variant_id', 'sort_order'],
                    'pvi_variant_sort_index'
                );
            });
        }
    }

    public function down(): void
    {
        $categoryIndexes = collect(
            Schema::getIndexes('categories')
        )->pluck('name')->all();

        if (in_array('categories_active_position_name_index', $categoryIndexes, true)) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_active_position_name_index');
            });
        }

        $variantIndexes = collect(
            Schema::getIndexes('product_variants')
        )->pluck('name')->all();

        if (in_array('product_variants_product_active_type_index', $variantIndexes, true)) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropIndex('product_variants_product_active_type_index');
            });
        }

        if (in_array('product_variants_stock_active_index', $variantIndexes, true)) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropIndex('product_variants_stock_active_index');
            });
        }

        if (in_array('product_variants_product_type_value_unique', $variantIndexes, true)) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropUnique('product_variants_product_type_value_unique');
            });
        }

        $pivotIndexes = collect(
            Schema::getIndexes('product_variant_images')
        )->pluck('name')->all();

        if (in_array('pvi_variant_sort_index', $pivotIndexes, true)) {
            Schema::table('product_variant_images', function (Blueprint $table) {
                $table->dropIndex('pvi_variant_sort_index');
            });
        }
    }
};
