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
 * - name: User's full name.
 * - email: User's email address.
 * - dob: User's date of birth (optional).
 * - password: Hashed password for user authentication (nullable).
 * - password_reset_token: Token for password reset functionality (nullable).
 * - email_verification_token: Token for email verification (nullable).
 * - account_status: Enum for user account status (e.g., 0 = inactive, 1 = active, 2 = suspended, etc.).
 * - remember_token: Token for "remember me" functionality.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Indexes and primary key
            $table->id();
            $table->uuid('uid')->unique()->index();
            // User information
            $table->string('name');
            $table->string('email')->unique()->index();
            $table->date('dob')->nullable();
            $table->string('password')->nullable();
            $table->string('password_reset_token')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->unsignedTinyInteger('account_status')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
