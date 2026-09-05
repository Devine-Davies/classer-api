<?php

use App\Enums\CloudBackupStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cloud_backups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();

            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('uid')
                ->on('users')
                ->cascadeOnDelete();

            $table->string('resource_id');
            $table->string('active_resource_key', 64)->nullable()->unique();
            $table->unsignedBigInteger('expected_size')->nullable();
            $table->unsignedBigInteger('actual_size')->nullable();
            $table->unsignedTinyInteger('status')
                ->default(CloudBackupStatus::PENDING->value);

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('last_restored_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cloud_backups');
    }
};
