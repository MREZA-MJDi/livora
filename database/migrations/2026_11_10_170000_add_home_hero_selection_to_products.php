<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasHeroFlag = Schema::hasColumn('products', 'is_home_hero');
        $hasHeroOrder = Schema::hasColumn('products', 'home_hero_sort_order');

        if (! $hasHeroFlag || ! $hasHeroOrder) {
            Schema::table('products', function (Blueprint $table) use ($hasHeroFlag, $hasHeroOrder) {
                if (! $hasHeroFlag) {
                    $table->boolean('is_home_hero')->default(false)->after('is_new');
                }
                if (! $hasHeroOrder) {
                    $table->unsignedSmallInteger('home_hero_sort_order')->nullable()->after('is_home_hero');
                }
            });
        }

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

        $hasHeroFlag = Schema::hasColumn('products', 'is_home_hero');
        $hasHeroOrder = Schema::hasColumn('products', 'home_hero_sort_order');

        if ($hasHeroFlag || $hasHeroOrder) {
            Schema::table('products', function (Blueprint $table) use ($hasHeroFlag, $hasHeroOrder) {
                $columns = [];
                if ($hasHeroOrder) $columns[] = 'home_hero_sort_order';
                if ($hasHeroFlag) $columns[] = 'is_home_hero';
                if ($columns !== []) $table->dropColumn($columns);
            });
        }
    }
};
