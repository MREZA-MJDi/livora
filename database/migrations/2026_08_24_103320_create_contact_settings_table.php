<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->text('address')->nullable();
            $table->text('hours')->nullable();

            $table->string('phone_label')->default('تماس تلفنی');
            $table->string('email_label')->default('ایمیل');
            $table->string('support_label')->default('پشتیبانی');
            $table->string('online_label')->default('ارتباط آنلاین');

            $table->text('support_description')->nullable();
            $table->text('online_description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
