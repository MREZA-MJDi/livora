<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();

            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->decimal('price', 15, 2);
            $table->decimal('compare_at_price', 15, 2)->nullable();

            $table->unsignedInteger('stock')->default(0);

            $table->enum('status', [
                'draft',
                'active',
                'archived',
            ])->default('draft');

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
