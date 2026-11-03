<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Preserve reversibility without discarding orders created without an email.
        DB::table('orders')
            ->whereNull('email')
            ->update(['email' => '']);

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('email')->nullable(false)->change();
        });
    }
};
