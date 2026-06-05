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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->index();
            $table->uuid('order_id')->index();
            $table->uuid('product_id')->nullable()->index();
            $table->string('product_name');
            $table->string('purchase_type')->nullable();
            $table->unsignedInteger('unit_amount');
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('line_amount');
            $table->string('currency', 3)->default('gbp');
            $table->timestamps();

            $table->foreign('order_id')->references('uid')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('uid')->on('products')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
