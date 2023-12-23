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
        Schema::create('aws_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bucket');
            $table->string('Region');
            $table->string('userIdentity');
            $table->string('ownerIdentity');
            $table->string('arn');
            $table->string('time');
            $table->string('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_events');
    }
};
