<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Description:
 * - This table is designed to store AWS event records,
 * - capturing details about events such as bucket, region, user identity, and payload.
 *
 * Table structure:
 * - id: Primary key, auto-incrementing integer.
 * - name: Name of the event.
 * - bucket: Name of the S3 bucket associated with the event.
 * - region: AWS region where the event occurred.
 * - user_identity: Identity of the user associated with the event.
 * - owner_identity: Identity of the owner of the resource.
 * - entity_id: Unique identifier for the entity involved in the event.
 * - arn: Amazon Resource Name for the resource involved in the event.
 * - time: Timestamp of when the event occurred.
 * - payload: Long text field to store the event payload in JSON format.
 * - timestamps: Laravel's created_at and updated_at fields for tracking changes.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aws_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('bucket');
            $table->string('region');
            $table->string('user_identity');
            $table->string('owner_identity');
            $table->string('entity_id');
            $table->string('arn');
            $table->string('time');
            $table->longText('payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aws_events');
    }
};
