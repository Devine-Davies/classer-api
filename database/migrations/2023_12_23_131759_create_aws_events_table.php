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
            $table->string('region');
            $table->string('user_identity');
            $table->string('owner_identity');
            $table->string('arn');
            $table->string('time');
            $table->longText('payload');
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
