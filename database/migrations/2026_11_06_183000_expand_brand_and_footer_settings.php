<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->text('brand_description')->nullable();
            $table->string('enamad_url', 2048)->nullable();
            $table->string('samandehi_url', 2048)->nullable();
            $table->json('footer_menu')->nullable();
            $table->json('payment_methods')->nullable();
            $table->string('copyright_text', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'brand_description', 'enamad_url', 'samandehi_url',
                'footer_menu', 'payment_methods', 'copyright_text',
            ]);
        });
    }
};
