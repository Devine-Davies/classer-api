<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store personal access tokens for users,
 * - allowing them to authenticate API requests securely.
 *
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - tokenable_type: Morphs to the user or any other model that can have tokens.
 * - tokenable_id: The ID of the user or model that owns the token.
 * - name: Name of the token for identification.
 * - token: Unique token string, hashed for security.
 * - abilities: Optional JSON field to define specific abilities or scopes for the token.
 * - last_used_at: Timestamp indicating when the token was last used.
 * - expires_at: Timestamp indicating when the token expires.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
