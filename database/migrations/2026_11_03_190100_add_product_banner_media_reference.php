<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('banner_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->index('banner_media_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['banner_media_id']);
            $table->dropIndex(['banner_media_id']);
            $table->dropColumn('banner_media_id');
        });
    }
};
