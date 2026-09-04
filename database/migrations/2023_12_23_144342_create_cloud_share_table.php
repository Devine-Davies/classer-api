<?php

use App\Enums\CloudShareStatus;
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
    public function up(): void
    {
        Schema::create('cloud_share', function (Blueprint $table) {
            $table->id();

            $table->uuid('uid')->unique();

            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('uid')
                ->on('users')
                ->cascadeOnDelete();

            // Local/media identifier being shared
            $table->string('resource_id')->index();

            // Upload expectations
            $table->unsignedBigInteger('expected_size')->nullable();
            $table->unsignedBigInteger('actual_size')->nullable();

            // Share lifecycle
            $table->unsignedTinyInteger('status')
                ->default(CloudShareStatus::PENDING->value);

            // Abandoned upload-session lifetime
            $table->timestamp('upload_expires_at')->nullable();

            // Public share lifetime
            $table->timestamp('expires_at')->nullable();

            // Lifecycle timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('upload_expires_at');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_share');
    }
};
