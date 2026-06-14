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
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->index();

            $table->string('sellable_type');
            $table->uuid('sellable_id');

            $table->string('sku', 64)->unique()->index();
            $table->string('slug')->unique()->index();
            $table->string('title');
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('price_amount');
            $table->unsignedTinyInteger('promotion_percentage')->default(0);
            $table->string('currency', 3)->default('gbp');
            $table->string('image_url')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('promotion_eligible')->default(true);
            $table->boolean('discount_code_eligible')->default(true);
            $table->boolean('shipping_required')->default(false);
            $table->timestamps();

            $table->index(['sellable_type', 'sellable_id']);
            $table->unique(['sellable_type', 'sellable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};
