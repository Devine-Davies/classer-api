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
        Schema::create('user_cloud_usages', function (Blueprint $table) {
            $table->id();

            // UUID-based unique identifier
            $table->uuid('uid')->unique()->index()->default(Str::uuid());

            // Foreign key to users table
            $table->string('user_id')
                ->constrained('users', 'uid') // Assuming a users table exists
                ->index()
                ->onDelete('cascade');

            // Total storage used in bytes
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
