<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Match the common storefront equality predicate (active status)
            // with price and name ordering/filtering.
            $table->index(
                ['status', 'price'],
                'products_status_price_idx'
            );

            $table->index(
                ['status', 'name'],
                'products_status_name_idx'
            );

            // Category pages combine category_id + active status and order by
            // creation time. This avoids relying on separate single-column indexes.
            $table->index(
                ['category_id', 'status', 'created_at'],
                'products_category_status_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_price_idx');
            $table->dropIndex('products_status_name_idx');
            $table->dropIndex('products_category_status_created_idx');
        });
    }
};
