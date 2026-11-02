<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_related_products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('related_product_id')
                ->constrained('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(
                ['product_id', 'related_product_id'],
                'product_related_products_pair_unique'
            );
            $table->index(
                ['related_product_id', 'product_id'],
                'product_related_products_reverse_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_related_products');
    }
};
