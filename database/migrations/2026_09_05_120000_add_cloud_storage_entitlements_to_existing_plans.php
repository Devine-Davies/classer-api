<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plan_entitlements')) {
            Schema::create('plan_entitlements', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uid')->unique()->index();
                $table->uuid('plan_id')->index();
                $table->string('capability');
                $table->unsignedBigInteger('quota')->default(0);
                $table->timestamps();

                $table->foreign('plan_id')->references('uid')->on('plans')->cascadeOnDelete();
                $table->unique(['plan_id', 'capability']);
            });
        }

        $now = now();

        DB::table('plans')
            ->where('type', 'cloud_share')
            ->select(['uid', 'quota'])
            ->orderBy('uid')
            ->each(function (object $plan) use ($now): void {
                foreach (['cloud_share', 'cloud_backup'] as $capability) {
                    DB::table('plan_entitlements')->insertOrIgnore([
                        'uid' => (string) Str::uuid(),
                        'plan_id' => $plan->uid,
                        'capability' => $capability,
                        'quota' => (int) ($plan->quota ?? 0),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Entitlements may have existed before this data backfill and cannot be safely distinguished.
    }
};
