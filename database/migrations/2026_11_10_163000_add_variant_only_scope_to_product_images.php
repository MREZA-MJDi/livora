<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('product_images', 'is_variant_only')) {
            Schema::table('product_images', function (Blueprint $table) {
                $table->boolean('is_variant_only')->default(false)->after('is_primary');
                $table->index(['product_id', 'is_variant_only', 'sort_order'], 'product_images_gallery_scope_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_images', 'is_variant_only')) {
            return;
        }

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_gallery_scope_idx');
            $table->dropColumn('is_variant_only');
        });
    }
};
