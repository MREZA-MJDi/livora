<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index(
                ['stock', 'status'],
                'products_stock_status_index'
            );

            $table->index(
                ['is_new', 'status'],
                'products_new_status_index'
            );

            $table->index(
                ['installment_enabled', 'status'],
                'products_installment_status_index'
            );

            $table->index(
                ['status', 'created_at'],
                'products_status_created_at_index'
            );
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->index(
                ['product_id', 'is_primary'],
                'product_images_product_primary_index'
            );
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(
                ['payment_status', 'created_at'],
                'orders_payment_created_at_index'
            );

            $table->index(
                ['status', 'created_at'],
                'orders_status_created_at_index'
            );

            $table->index(
                ['payment_method', 'payment_status'],
                'orders_payment_method_status_index'
            );
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->index(
                ['status', 'created_at'],
                'contact_messages_status_created_at_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_stock_status_index');
            $table->dropIndex('products_new_status_index');
            $table->dropIndex('products_installment_status_index');
            $table->dropIndex('products_status_created_at_index');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_product_primary_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_payment_created_at_index');
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_payment_method_status_index');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex('contact_messages_status_created_at_index');
        });
    }
};
