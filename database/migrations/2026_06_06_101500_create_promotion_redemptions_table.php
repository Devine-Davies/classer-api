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
        Schema::create('promotion_redemptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->index();

            $table->string('promotion_code')->index();
            $table->string('source_type')->nullable()->index();
            $table->uuid('source_uid')->nullable()->index();

            $table->uuid('order_id')->nullable()->index();
            $table->uuid('order_item_id')->nullable()->index();
            $table->uuid('user_id')->nullable()->index();
            $table->string('customer_email')->nullable()->index();

            $table->string('status')->default('pending')->index();
            $table->string('redeem_token_hash', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'promotion_code']);

            $table->foreign('order_id')->references('uid')->on('orders')->nullOnDelete();
            $table->foreign('order_item_id')->references('uid')->on('order_items')->nullOnDelete();
            $table->foreign('user_id')->references('uid')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_redemptions');
    }
};
