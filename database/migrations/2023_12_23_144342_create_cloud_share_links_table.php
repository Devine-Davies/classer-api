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
            $table->string('uid');
            $table->string('user_id');
            $table->string('resource_id');
            $table->string('expires_at')->nullable();
            $table->string('size')->nullable();
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
