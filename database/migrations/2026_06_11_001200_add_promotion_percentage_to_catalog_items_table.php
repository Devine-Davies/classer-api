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
        if (Schema::hasTable('catalog_items') && ! Schema::hasColumn('catalog_items', 'promotion_percentage')) {
            Schema::table('catalog_items', function (Blueprint $table) {
                $table->unsignedTinyInteger('promotion_percentage')->default(0)->after('price_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left as no-op to avoid destructive rollback on live commerce data.
    }
};
