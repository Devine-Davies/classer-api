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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_amount', 'promotion_percentage', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('price_amount')->default(0)->after('description');
            $table->unsignedTinyInteger('promotion_percentage')->default(0)->after('price_amount');
            $table->string('currency', 3)->default('gbp')->after('promotion_percentage');
        });
    }
};
