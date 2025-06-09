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
        Schema::create('scheduler', function (Blueprint $table) {
            $table->id();
        
            // Command to be executed (e.g., artisan command or job identifier)
            $table->string('command');
        
            // Optional metadata (JSON or structured config)
            $table->json('metadata')->nullable();
        
            // When the command is scheduled to run
            $table->timestamp('scheduled_for')->nullable()->index();
        
            // Laravel-managed timestamps
            $table->timestamps(); // includes created_at & updated_at with automatic handling
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
