<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', [
                'online',
                'installment',
            ])
                ->default('online')
                ->after('payment_status');

            $table->string('payment_provider')
                ->nullable()
                ->after('payment_method');

            $table->index([
                'payment_method',
                'payment_provider',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex([
                'payment_method',
                'payment_provider',
            ]);

            $table->dropColumn([
                'payment_method',
                'payment_provider',
            ]);
        });
    }
};
