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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique()->index();
            $table->string('code')->unique()->index();
            $table->unsignedTinyInteger('discount_percentage');
            $table->unsignedTinyInteger('max_discount_percentage')->nullable();
            $table->unsignedInteger('min_order_amount')->nullable();

            $table->uuid('product_id')->nullable()->index();
            $table->uuid('assigned_user_id')->nullable()->index();
            $table->string('assigned_email')->nullable()->index();

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->boolean('one_use_per_customer')->default(false);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('disabled_at')->nullable();

            $table->text('internal_note')->nullable();
            $table->uuid('created_by_user_id')->nullable()->index();
            $table->uuid('updated_by_user_id')->nullable()->index();
            $table->uuid('disabled_by_user_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('product_id')->references('uid')->on('products')->nullOnDelete();
            $table->foreign('assigned_user_id')->references('uid')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('uid')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('uid')->on('users')->nullOnDelete();
            $table->foreign('disabled_by_user_id')->references('uid')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
