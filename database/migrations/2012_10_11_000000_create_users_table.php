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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
        
            // UUID-based unique user identifier
            $table->uuid('uid')->unique()->index();
        
            // Core identity
            $table->string('name');
            $table->string('email')->unique()->index();
        
            // Optional personal fields
            $table->date('dob')->nullable();
        
            // Auth fields
            $table->string('password')->nullable();
            $table->string('password_reset_token')->nullable();
            $table->string('email_verification_token')->nullable();
        
            // Relationship to subscriptions (can use foreignId if a real FK is defined)
            // $table->foreignUuid('subscription_id')->nullable()->constrained('user_subscriptions')->nullOnDelete();
        
            // Status enum (e.g., 0 = inactive, 1 = active, 2 = suspended, etc.)
            $table->unsignedTinyInteger('account_status')->default(0);
        
            // Laravel convenience fields
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
