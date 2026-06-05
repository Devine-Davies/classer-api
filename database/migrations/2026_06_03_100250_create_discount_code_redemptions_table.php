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
        Schema::create('discount_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->index();
            $table->uuid('discount_code_id')->index();
            $table->uuid('order_id')->unique()->index();
            $table->uuid('order_payment_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('customer_email')->nullable()->index();
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->unique(['discount_code_id', 'user_id']);
            $table->unique(['discount_code_id', 'customer_email']);

            $table->foreign('discount_code_id')->references('uid')->on('discount_codes')->cascadeOnDelete();
            $table->foreign('order_id')->references('uid')->on('orders')->cascadeOnDelete();
            $table->foreign('order_payment_id')->references('uid')->on('order_payments')->nullOnDelete();
            $table->foreign('user_id')->references('uid')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_code_redemptions');
    }
};
