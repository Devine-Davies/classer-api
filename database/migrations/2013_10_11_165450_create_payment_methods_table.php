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
        Schema::create('payment_methods', function (Blueprint $table) {
            // Identifiers
            $table->id()->index();
            $table->uuid('uid')->unique()->index();

            // Foreign key to users table
            $table->uuid('user_id')
                ->constrained('users')
                ->onDelete('cascade'); // Assuming a users table exists

            // General payment method fields 
            $table->boolean('is_default')->default(false);
            $table->string('provider')->default('stripe'); // e.g. 'stripe', 'paypal', etc.

            // Card info
            $table->string('type')->default('card'); // 'card', 'paypal', etc.
            $table->string('brand')->nullable(); // Visa, MasterCard, etc.
            $table->string('last4')->nullable(); // Last 4 digits
            $table->tinyInteger('exp_month')->nullable();
            $table->smallInteger('exp_year')->nullable();

            // Stripe relationship
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_transaction_id')->unique(); // charge or payment_intent ID
            $table->string('stripe_payment_method_id')->unique(); // e.g. pm_xxx

            $table->timestamp('revoked_at')->nullable(); // If removed from Stripe
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
