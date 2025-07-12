<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store cloud entities,
 * - such as files or media, with metadata and polymorphic relationships.
 *
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Universally unique identifier for the cloud entity.
 * - key: Key for storage (e.g., S3 path).
 * - cloudable_id: ID of the related model (polymorphic).
 * - cloudable_type: Type of the related model (polymorphic).
 * - e_tag: Optional ETag for versioning or checksums.
 * - upload_url: URL for uploading the entity.
 * - public_url: Publicly accessible URL for the entity.
 * - type: MIME type of the content (e.g., video/mp4).
 * - size: Size of the content in bytes.
 * - expires_at: Optional expiration timestamp for temporary URLs.
 * - softDeletes: Soft delete flag to mark entities as deleted without removing them from the database.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cloud_entities', function (Blueprint $table) {
            // Identifiers
            $table->id();
            $table->uuid('uid')->index();
            // Details
            $table->string('key')->index();
            $table->morphs('cloudable');
            $table->string('type')->nullable();
            $table->string('e_tag')->nullable();
            $table->text('upload_url')->nullable();
            $table->text('public_url')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_entities');
    }
};
