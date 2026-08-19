<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('installment_enabled')
                ->default(false)
                ->after('price');

            $table->unsignedTinyInteger('installment_cash_percent')
                ->nullable()
                ->after('installment_enabled');

            $table->enum('installment_remainder_method', [
                'cheque',
            ])
                ->nullable()
                ->after('installment_cash_percent');

            $table->unsignedTinyInteger('installment_cheque_count')
                ->nullable()
                ->after('installment_remainder_method');

            $table->unsignedSmallInteger('installment_interval_months')
                ->nullable()
                ->after('installment_cheque_count');

            $table->index([
                'installment_enabled',
                'installment_cash_percent',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex([
                'products_installment_enabled_installment_cash_percent_index',
            ]);

            $table->dropColumn([
                'installment_enabled',
                'installment_cash_percent',
                'installment_remainder_method',
                'installment_cheque_count',
                'installment_interval_months',
            ]);
        });
    }
};
