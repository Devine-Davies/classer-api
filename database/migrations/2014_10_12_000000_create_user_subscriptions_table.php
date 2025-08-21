<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store user subscriptions, linking users to their
 * - subscription plans, payment methods, and billing lifecycle information.
 * 
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Universally unique identifier for the subscription.
 * - user_id: Foreign key to the users table, linking to the user who owns the subscription.
 * - subscription_id: Foreign key to the subscriptions table, linking to the subscription plan.
 * - payment_method_id: Foreign key to the payment_methods table, linking to the user's payment method.
 * - status: Current status of the subscription (e.g., active, canceled, incomplete, etc.).
 * - expiration_date: Timestamp indicating when the current subscription period ends.
 * - auto_renew: Boolean indicating if the subscription will auto-renew.
 * - auto_renew_date: Timestamp indicating when the subscription will auto-renew.
 * - cancellation_date: Timestamp indicating when the subscription was canceled.
 * - cancellation_reason: Optional reason for cancellation, if collected from the user.
 * - payment_method: Type of payment method used (e.g., card, paypal).
 * - transaction_id: Custom reference or charge ID for the subscription payment.
 * - updated_by: Email or user ID of the staff/admin who last updated the subscription.
 * - notes: Optional field for manual annotations or comments about the subscription.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.   
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            // Primary key and unique identifier
            $table->id();
            $table->string('uid')->unique();

            // Foreign key to the users table
            $table->string('user_id')
                ->constrained()
                ->index();

            // Foreign key to the subscriptions table
            $table->string('subscription_id')
                ->constrained()
                ->nullOnDelete()
                ->index();

            // Foreign key to users table
            $table->uuid('payment_method_id')->constrained()->onDelete('cascade');

            $table->string('status')->default('active');
            $table->timestamp('expiration_date')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('auto_renew_date')->nullable();
            $table->timestamp('cancellation_date')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('updated_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
