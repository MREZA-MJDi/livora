<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_images', 'is_variant_only')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->boolean('is_variant_only')->default(false)->after('is_primary');
            });
        }

        // Older variants used the shared product gallery table for their own photos.
        // Preserve the primary/default product cover, but scope non-primary photos
        // already linked to variants to those variants' galleries.
        if (Schema::hasTable('product_variant_images')) {
            DB::table('product_images')
                ->where('is_primary', false)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('product_variant_images')
                        ->whereColumn('product_variant_images.product_image_id', 'product_images.id');
                })
                ->update(['is_variant_only' => true]);
        }

        $indexes = collect(Schema::getIndexes('product_images'))->pluck('name')->all();
        if (! in_array('product_images_gallery_scope_idx', $indexes, true)) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->index(
                    ['product_id', 'is_variant_only', 'sort_order'],
                    'product_images_gallery_scope_idx'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_images', 'is_variant_only')) {
            return;
        }

        $indexes = collect(Schema::getIndexes('product_images'))->pluck('name')->all();
        if (in_array('product_images_gallery_scope_idx', $indexes, true)) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->dropIndex('product_images_gallery_scope_idx');
            });
        }

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('is_variant_only');
        });
    }
};
