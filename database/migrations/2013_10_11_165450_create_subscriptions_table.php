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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
        
            // Universally unique identifier
            $table->uuid('uid')->unique()->index()->default(Str::uuid());
            $table->string('title');
            $table->string('code')->unique(); // Plan or subscription code (e.g. 'pro', 'basic')
        
            // Optional storage quota in bytes (e.g. 20MB = 20 * 1024 * 1024)
            $table->unsignedBigInteger('quota')->nullable()->comment('Storage quota in bytes');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
