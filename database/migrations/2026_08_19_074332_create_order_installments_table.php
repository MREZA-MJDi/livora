<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('sequence');

            $table->enum('type', [
                'cash',
                'cheque',
            ]);

            $table->decimal('amount', 15, 2);

            $table->date('due_date')->nullable();

            $table->enum('status', [
                'pending',
                'paid',
                'overdue',
                'cancelled',
            ])->default('pending');

            /*
             * Cheque information
             */
            $table->string('cheque_number')->nullable();
            $table->string('cheque_bank')->nullable();
            $table->string('cheque_holder')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'order_id',
                'sequence',
            ]);

            $table->index([
                'order_id',
                'status',
            ]);

            $table->index([
                'type',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_installments');
    }
};
