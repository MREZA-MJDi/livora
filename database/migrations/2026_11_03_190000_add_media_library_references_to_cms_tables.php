<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'blog_posts', 'content_pages', 'shop_page_settings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
                $table->index('media_id');
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'blog_posts', 'content_pages', 'shop_page_settings'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign([$tableName === 'shop_page_settings' ? 'media_id' : 'media_id']);
                $table->dropIndex([$tableName === 'shop_page_settings' ? 'media_id' : 'media_id']);
                $table->dropColumn('media_id');
            });
        }
    }
};
