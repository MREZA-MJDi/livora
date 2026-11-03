<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_settings', function (Blueprint $table): void {
            $table->foreignId('default_og_media_id')
                ->nullable()
                ->after('default_og_image')
                ->constrained('media')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('seo_settings', function (Blueprint $table): void {
            $table->dropForeign(['default_og_media_id']);
            $table->dropColumn('default_og_media_id');
        });
    }
};
