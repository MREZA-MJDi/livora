<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('installment_enabled')
                ->default(false)
                ->after('payment_provider');

            $table->unsignedTinyInteger('installment_cash_percent')
                ->nullable()
                ->after('installment_enabled');

            $table->decimal('installment_cash_amount', 15, 2)
                ->nullable()
                ->after('installment_cash_percent');

            $table->decimal('installment_deferred_amount', 15, 2)
                ->nullable()
                ->after('installment_cash_amount');

            $table->enum('installment_remainder_method', [
                'cheque',
            ])
                ->nullable()
                ->after('installment_deferred_amount');

            $table->unsignedTinyInteger('installment_cheque_count')
                ->nullable()
                ->after('installment_remainder_method');

            $table->unsignedSmallInteger('installment_interval_months')
                ->nullable()
                ->after('installment_cheque_count');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'installment_enabled',
                'installment_cash_percent',
                'installment_cash_amount',
                'installment_deferred_amount',
                'installment_remainder_method',
                'installment_cheque_count',
                'installment_interval_months',
            ]);
        });
    }
};
