<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('inventory_status', 32)
                ->default('pending')
                ->after('payment_status');

            $table->timestamp('inventory_processed_at')
                ->nullable()
                ->after('inventory_status');

            $table->json('inventory_metadata')
                ->nullable()
                ->after('inventory_processed_at');

            $table->index(
                ['inventory_status', 'payment_status'],
                'orders_inventory_payment_status_idx'
            );
        });

        // Existing paid orders pre-date automated stock allocation. Do not
        // mutate historic stock retroactively; flag them for explicit review.
        DB::table('orders')
            ->where('payment_status', 'paid')
            ->update([
                'inventory_status' => 'needs_reconciliation',
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_inventory_payment_status_idx');
            $table->dropColumn([
                'inventory_status',
                'inventory_processed_at',
                'inventory_metadata',
            ]);
        });
    }
};
