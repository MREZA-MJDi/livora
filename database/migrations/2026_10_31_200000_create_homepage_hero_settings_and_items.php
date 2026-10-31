<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_hero_settings', function (Blueprint $table) {
            $table->id();
            $table->string('background_path')->nullable();
            $table->timestamps();
        });

        Schema::create('homepage_hero_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('slot')->unique();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_hero_items');
        Schema::dropIfExists('homepage_hero_settings');
    }
};
