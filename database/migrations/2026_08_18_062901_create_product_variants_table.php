<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('type');
            $table->string('name');
            $table->string('value');

            $table->string('sku')->nullable()->unique();

            $table->decimal('price_adjustment', 15, 2)->default(0);

            $table->unsignedInteger('stock')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['product_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
