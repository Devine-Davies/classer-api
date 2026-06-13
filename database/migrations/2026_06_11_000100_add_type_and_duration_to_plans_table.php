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
        if (! Schema::hasTable('plans')) {
            return;
        }

        if (! Schema::hasColumn('plans', 'type') || ! Schema::hasColumn('plans', 'duration')) {
            Schema::table('plans', function (Blueprint $table) {
                if (! Schema::hasColumn('plans', 'type')) {
                    $table->string('type')->nullable()->after('quota');
                }

                if (! Schema::hasColumn('plans', 'duration')) {
                    $table->string('duration')->nullable()->comment('Flexible duration label')->after('type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left as no-op because these columns may be defined
        // in the base create migration on fresh environments.
    }
};
