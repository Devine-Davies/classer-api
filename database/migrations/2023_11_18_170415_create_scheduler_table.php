<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store scheduled commands or jobs,
 * - allowing for flexible scheduling and execution of tasks.
 * 
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - command: Command to be executed (e.g., artisan command or job identifier).
 * - metadata: Optional JSON field for additional configuration or metadata.
 * - scheduled_for: Timestamp indicating when the command is scheduled to run.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scheduler', function (Blueprint $table) {
            // Identifiers
            $table->id();
            // Details
            $table->string('command');
            $table->json('metadata')->nullable();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduler');
    }
};
