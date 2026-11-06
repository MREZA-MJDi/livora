<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('card_image')->nullable();
            $table->foreignId('card_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('banner_image')->nullable();
            $table->foreignId('banner_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('banner_title', 255)->nullable();
            $table->text('banner_subtitle')->nullable();
            $table->index(['parent_id', 'is_active', 'sort_order'], 'categories_parent_active_order_index');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_parent_active_order_index');
            $table->dropConstrainedForeignId('banner_media_id');
            $table->dropConstrainedForeignId('card_media_id');
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['card_image', 'banner_image', 'banner_title', 'banner_subtitle']);
        });
    }
};
