<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_home_hero')) {
                $table->boolean('is_home_hero')->default(false)->after('is_new');
            }
            if (! Schema::hasColumn('products', 'home_hero_sort_order')) {
                $table->unsignedSmallInteger('home_hero_sort_order')->nullable()->after('is_home_hero');
            }
        });

        $indexes = collect(Schema::getIndexes('products'))->pluck('name')->all();
        if (! in_array('products_home_hero_order_idx', $indexes, true)) {
            Schema::table('products', function (Blueprint $table) {
                $table->index(['is_home_hero', 'home_hero_sort_order'], 'products_home_hero_order_idx');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(Schema::getIndexes('products'))->pluck('name')->all();
        if (in_array('products_home_hero_order_idx', $indexes, true)) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_home_hero_order_idx');
            });
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'home_hero_sort_order')) {
                $table->dropColumn('home_hero_sort_order');
            }
            if (Schema::hasColumn('products', 'is_home_hero')) {
                $table->dropColumn('is_home_hero');
            }
        });
    }
};
