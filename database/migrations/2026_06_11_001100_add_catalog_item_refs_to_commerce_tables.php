<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Catalog item references are now defined in each table's create migration.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left as no-op to avoid dropping columns that may be used by live checkout data.
    }
};
