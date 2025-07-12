<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


/**
 * Description:
 * - This table is designed to store user cloud usage records,
 * - including total storage usage in bytes, linked to a user via a foreign key.
 * 
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Universally unique identifier for the user cloud usage record.
 * - user_id: Foreign key to the users table, linking to the user who owns the record.
 * - total_usage: Unsigned big integer for total storage usage in bytes.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes. 
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_cloud_usages', function (Blueprint $table) {
            // Identifiers
            $table->id();
            // Details
            $table->uuid('uid')->unique()->index();
            $table->string('user_id')->constrained('users', 'uid')->index()->onDelete('cascade');
            $table->unsignedBigInteger('total_usage')->default(0)->comment('Total storage usage in bytes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cloud_usages');
    }
};
