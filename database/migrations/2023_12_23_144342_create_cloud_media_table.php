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
        Schema::create('cloud_media', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('media_id');
            $table->string('media_type');
            $table->string('user_id');
            $table->string('event_id');
            $table->string('status')->default(1);
            $table->string('location');
            $table->string('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloud_media');
    }
};