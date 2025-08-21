<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store recorder events,
 * - capturing details about the type of event, associated user, and metadata.
 *
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - uid: Foreign key to the users table, linking to the user who triggered the event.
 * - type: String indicating the type of event recorded.
 * - code: Integer code representing the event.
 * - metadata: Text field for additional information about the event.
 * - created_at: Timestamp indicating when the event was recorded.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recorder', function (Blueprint $table) {
            // Identifiers
            $table->id()->unique();
            $table->bigInteger('uid')->unsigned()->index()->nullable();
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
            // Details
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
