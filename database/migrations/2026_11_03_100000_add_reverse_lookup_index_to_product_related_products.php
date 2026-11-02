<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('product_related_products'))
            ->pluck('name')
            ->all();

        if (! in_array('product_related_products_reverse_index', $indexes, true)) {
            Schema::table('product_related_products', function (Blueprint $table): void {
                $table->index(
                    ['related_product_id', 'product_id'],
                    'product_related_products_reverse_index'
                );
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('product_related_products'))
            ->pluck('name')
            ->all();

        if (in_array('product_related_products_reverse_index', $indexes, true)) {
            Schema::table('product_related_products', function (Blueprint $table): void {
                $table->dropIndex('product_related_products_reverse_index');
            });
        }
    }
};
