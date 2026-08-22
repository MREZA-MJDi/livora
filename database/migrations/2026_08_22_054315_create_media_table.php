<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('disk')->default('public');

            $table->string('path');

            $table->string('filename');

            $table->string('original_name');

            $table->string('mime_type');

            $table->string('extension', 20)->nullable();

            $table->unsignedBigInteger('size')->default(0);

            $table->string('alt')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('mime_type');
            $table->index('extension');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
