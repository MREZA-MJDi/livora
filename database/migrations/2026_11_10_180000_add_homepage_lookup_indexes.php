<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->index('name', 'products_name_lookup_index');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->index('name', 'categories_name_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_name_lookup_index');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_name_lookup_index');
        });
    }
};
