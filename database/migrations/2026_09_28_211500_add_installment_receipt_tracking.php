<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_installments', function (Blueprint $table) {
            $table->foreignId('paid_by_user_id')
                ->nullable()
                ->after('paid_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_installments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_by_user_id');
        });
    }
};
