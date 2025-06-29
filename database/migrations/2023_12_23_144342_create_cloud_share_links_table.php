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
        Schema::create('cloud_share', function (Blueprint $table) {
            $table->id();

            // Public unique identifier
            $table->uuid('uid')->index();

            // Linked user
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Linked resource (e.g. media fingerprint or entity UID)
            $table->string('resource_id')->index();

            // Optional expiration timestamp
            $table->timestamp('expires_at')->nullable();

            // Size of shared resource in bytes
            $table->unsignedBigInteger('size')->nullable()->comment('Size in bytes');

            // soft delete flag
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
