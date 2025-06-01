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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(Str::uuid())->index()->unique();
            $table->string('name');
            $table->string('email')->index()->unique();
            $table->dateTime('dob')->nullable();
            $table->string('password')->nullable();
            $table->string('password_reset_token')->nullable();
            $table->string('email_verification_token')->nullable();
            $table->string('subscription_id')->nullable();
            $table->integer('account_status')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
