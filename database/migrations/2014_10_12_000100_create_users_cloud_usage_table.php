<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cloud_usages', function (Blueprint $table) {
            $table->id();

            $table->uuid('uid')->unique();

            $table->uuid('user_id')->unique();

            $table->foreign('user_id')
                ->references('uid')
                ->on('users')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('share_usage')
                ->default(0)
                ->comment('Cloud Share usage in bytes');

            $table->unsignedBigInteger('backup_usage')
                ->default(0)
                ->comment('Cloud Backup usage in bytes');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cloud_usages');
    }
};