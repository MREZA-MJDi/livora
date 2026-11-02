<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->string('brand_name')->nullable()->after('id');
            $table->string('logo_path')->nullable()->after('brand_name');
            $table->string('whatsapp_url')->nullable()->after('email');
            $table->string('telegram_url')->nullable()->after('whatsapp_url');
            $table->string('instagram_url')->nullable()->after('telegram_url');
            $table->string('eitaa_url')->nullable()->after('instagram_url');
            $table->string('bale_url')->nullable()->after('eitaa_url');
            $table->decimal('map_latitude', 10, 7)->nullable()->after('address');
            $table->decimal('map_longitude', 10, 7)->nullable()->after('map_latitude');
            $table->text('warranty_text')->nullable()->after('hours');
            $table->text('shipping_text')->nullable()->after('warranty_text');
            $table->text('installation_text')->nullable()->after('shipping_text');
            $table->text('returns_text')->nullable()->after('installation_text');
            $table->boolean('floating_actions_enabled')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('contact_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'brand_name', 'logo_path', 'whatsapp_url', 'telegram_url', 'instagram_url',
                'eitaa_url', 'bale_url', 'map_latitude', 'map_longitude', 'warranty_text',
                'shipping_text', 'installation_text', 'returns_text', 'floating_actions_enabled',
            ]);
        });
    }
};
