<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store cloud share links,
 * - allowing users to share resources with unique identifiers and optional expiration.
 *
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Universally unique identifier for the share link, indexed for quick access.
 * - user_id: Foreign key linking to the users table, indicating the owner of the share link.
 * - resource_id: Identifier for the resource being shared (e.g., media fingerprint or entity UID).
 * - expires_at: Optional timestamp indicating when the share link expires.
 * - size: Size of the shared resource in bytes, nullable if not applicable.
 * - soft delete flag: Allows for soft deletion of records.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cloud_share', function (Blueprint $table) {
            // Identifiers
            $table->id();
            $table->uuid('uid')->index();
            // Details
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('resource_id')->index();
            $table->unsignedBigInteger('size')->nullable()->comment('Size in bytes');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_share_links');
    }
};
