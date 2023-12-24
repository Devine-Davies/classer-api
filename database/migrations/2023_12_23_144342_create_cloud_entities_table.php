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
            $table->string('uid');
            $table->string('entity_id');
            $table->string('entity_type');
            $table->string('user_id');
            $table->string('event_id')->nullable();
            $table->string('status')->default(3);
            $table->string('location')->nullable();
            $table->string('size')->nullable();
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
