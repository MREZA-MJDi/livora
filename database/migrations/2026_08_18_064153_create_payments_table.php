<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            /*
             |--------------------------------------------------------------------------
             | Relations
             |--------------------------------------------------------------------------
             */

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             |--------------------------------------------------------------------------
             | Payment Gateway
             |--------------------------------------------------------------------------
             |
             | Example:
             | digipay
             | snappay
             | torobpay
             |
             */

            $table->string('gateway');

            /*
             |--------------------------------------------------------------------------
             | Gateway References
             |--------------------------------------------------------------------------
             |
             | authority:
             | Provider-side payment/request reference.
             |
             | transaction_id:
             | Final provider transaction/reference ID.
             |
             */

            $table->string('authority')
                ->nullable()
                ->unique();

            $table->string('transaction_id')
                ->nullable()
                ->unique();

            /*
             |--------------------------------------------------------------------------
             | Financial
             |--------------------------------------------------------------------------
             */

            $table->decimal(
                'amount',
                15,
                2
            );

            /*
             |--------------------------------------------------------------------------
             | Payment Status
             |--------------------------------------------------------------------------
             */

            $table->enum('status', [
                'pending',
                'initiated',
                'paid',
                'failed',
                'cancelled',
                'refunded',
            ])->default('pending');

            /*
             |--------------------------------------------------------------------------
             | Payment Completion
             |--------------------------------------------------------------------------
             */

            $table->timestamp('paid_at')
                ->nullable();

            /*
             |--------------------------------------------------------------------------
             | Gateway / Internal Metadata
             |--------------------------------------------------------------------------
             |
             | Stores normalized provider responses, callback data,
             | error information and other non-critical details.
             |
             */

            $table->json('metadata')
                ->nullable();

            /*
             |--------------------------------------------------------------------------
             | Timestamps
             |--------------------------------------------------------------------------
             */

            $table->timestamps();

            /*
             |--------------------------------------------------------------------------
             | Indexes
             |--------------------------------------------------------------------------
             */

            $table->index([
                'order_id',
                'status',
            ]);

            $table->index([
                'user_id',
                'status',
            ]);

            $table->index([
                'gateway',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
