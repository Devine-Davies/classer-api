<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Description:
 * - This table is designed to store subscription plans or types,
 * - including their unique identifiers, titles, codes, and optional storage quotas.  
 * 
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Universally unique identifier for the payment method.
 * - title: Title of the subscription plan.
 * - code: Unique code for the subscription plan (e.g., 'pro', 'basic').
 * - quota: Optional storage quota in bytes (e.g., 20MB = 20 * 1024 * 1024).
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.  
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            // Identifier
            $table->id();
            $table->uuid('uid')->unique()->index()->default(Str::uuid());
            // Subscription details
            $table->string('title');
            $table->string('code')->unique();
            $table->unsignedBigInteger('quota')->nullable()->comment('Storage quota in bytes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
