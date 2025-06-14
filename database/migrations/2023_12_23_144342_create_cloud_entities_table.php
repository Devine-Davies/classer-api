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
            $table->string('key');
            $table->morphs('cloudable'); // creates cloudable_id and cloudable_type
            $table->string('e_tag')->nullable();
            $table->longText('upload_url')->nullable();
            $table->longText('public_url')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->string('expires_at')->nullable();
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
