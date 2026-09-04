<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_entities', function (Blueprint $table) {
            $table->id();

            // Stable public/internal identifier
            $table->uuid('uid')->unique();

            // S3 object key
            $table->string('key')->unique();

            // Parent domain object:
            // CloudShare, CloudBackup, etc.
            $table->morphs('cloudable');

            // What this object represents inside the parent
            // e.g. video, thumbnail, metadata
            $table->string('object_role')->nullable();

            // Original local filename
            $table->string('original_name')->nullable();

            // MIME type
            $table->string('mime_type')->nullable();

            // Upload expectations
            $table->unsignedBigInteger('expected_size')->nullable();

            // What S3 actually reports after upload
            $table->unsignedBigInteger('actual_size')->nullable();

            // Integrity / storage metadata
            $table->string('e_tag')->nullable();
            $table->string('checksum')->nullable();

            // Upload lifecycle
            $table->string('status')->default('pending');

            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('object_role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_entities');
    }
};
