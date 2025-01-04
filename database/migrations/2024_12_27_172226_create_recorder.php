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
        Schema::create('recorder', function (Blueprint $table) {
            $table->id()->unique();
            $table->bigInteger('uid')->unsigned()->index()->nullable();
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
            $table->string('type');
            $table->integer('code');
            $table->text('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorder');
    }
};
