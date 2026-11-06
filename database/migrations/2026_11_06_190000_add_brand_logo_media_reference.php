<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->foreignId('logo_media_id')->nullable()->constrained('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('logo_media_id');
        });
    }
};
