<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cloud_entities', function (Blueprint $table) {
            $table->id();
        
            // Public or internal identifier
            $table->uuid('uid')->index();
        
            // Key for storage (e.g. S3 path)
            $table->string('key')->index();
        
            // Polymorphic relationship (e.g. media, file, etc.)
            $table->morphs('cloudable'); // cloudable_id, cloudable_type (indexed)
        
            // Metadata
            $table->string('e_tag')->nullable(); // e.g. S3 checksum
            $table->text('upload_url')->nullable();
            $table->text('public_url')->nullable();
        
            // Content info
            $table->string('type')->nullable(); // e.g. video/mp4
            $table->unsignedBigInteger('size')->nullable(); // file size in bytes
            $table->timestamp('expires_at')->nullable();
        
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
