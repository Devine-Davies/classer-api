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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->index();
            $table->string('sku', 64)->unique()->index();
            $table->string('slug')->unique()->index();
            $table->string('name');
            $table->string('short_description', 255)->nullable();
            $table->text('long_description')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_amount');
            $table->unsignedTinyInteger('promotion_percentage')->default(0);
            $table->string('currency', 3)->default('gbp');
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
