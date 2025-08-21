<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store various payment methods for users,
 * - including card details and Stripe-specific information.
 * 
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Universally unique identifier for the payment method.
 * - user_id: Foreign key to the users table, linking the payment method to a user
 * - is_default: Boolean indicating if this is the user's default payment method.
 * - provider: String indicating the payment provider (e.g., 'stripe', 'paypal').
 * - type: String indicating the type of payment method (e.g., 'card', 'paypal').
 * - brand: String indicating the card brand (e.g., 'Visa', 'MasterCard').
 * - last4: String for the last 4 digits of the card number.
 * - exp_month: Integer for the card's expiration month.
 * - exp_year: Integer for the card's expiration year.
 * - stripe_customer_id: String for the Stripe customer ID.
 * - stripe_transaction_id: Unique string for the Stripe transaction ID (charge or payment_intent ID).
 * - stripe_payment_method_id: Unique string for the Stripe payment method ID (e.g. 'pm_xxx').
 * - revoked_at: Timestamp for when the payment method was removed from Stripe.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.   
 */
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
