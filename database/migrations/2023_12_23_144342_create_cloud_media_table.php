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
            $table->string('aws_event_id');
            $table->string('directory');
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
