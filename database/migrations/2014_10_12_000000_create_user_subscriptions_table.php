<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
            $table->uuid('payment_method_id')
                ->constrained()
                ->onDelete('cascade'); // Assuming a users table exists

            // Status & billing lifecycle
            $table->string('status')->default('active'); // active, canceled, incomplete, etc.
            $table->timestamp('expiration_date')->nullable(); // current_period_end from Stripe
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('auto_renew_date')->nullable(); // Matches current_period_end if renewing

            // Cancellations
            $table->timestamp('cancellation_date')->nullable(); // canceled_at from Stripe
            $table->string('cancellation_reason')->nullable(); // if collected from user

            // Internal metadata
            $table->string('payment_method')->nullable(); // e.g., card, paypal
            $table->string('transaction_id')->nullable(); // custom reference or charge_id
            $table->string('updated_by')->nullable(); // staff/admin email or user ID
            $table->text('notes')->nullable(); // manual annotations

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
