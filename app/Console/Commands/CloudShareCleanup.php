<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\CloudShare;
use App\Models\User;

class CloudShareCleanup extends Command
{
    protected $signature = 'app:cloud-share-cleanup {initiator}';
    protected $description = 'Cleans up expired CloudShares and reclaims cloud storage space.';

    protected int $totalSizeReclaimed = 0;

    public function handle()
    {
        DB::beginTransaction();

        try {
            CloudShare::where('expires_at', '<', now())
                ->chunk(100, fn($shares) => $this->processChunk($shares));

            DB::commit();
            $this->info("Reclaimed " . round($this->totalSizeReclaimed / 1024 / 1024, 2) . " MB from expired cloud shares.");
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("❌ Cleanup failed: " . $e->getMessage());
        }
    }

    protected function processChunk($shares): void
    {
        foreach ($shares as $share) {
            try {
                $this->processShare($share);
            } catch (\Throwable $e) {
                $this->error("❌ Failed processing share ID {$share->id}: " . $e->getMessage());
            }
        }
    }

    protected function processShare(CloudShare $share): void
    {
        $entities = collect($share->cloudEntities);
        $firstKey = $entities->pluck('key')->first();

        if (!$firstKey || !str_contains($firstKey, '/')) {
            $this->error("⚠️ Skipping CloudShare ID {$share->id}, invalid key format. {$firstKey}");
            return;
        }

        $directory = explode('/', $firstKey)[0];

        if (!Storage::disk('s3')->deleteDirectory($directory)) {
            $this->error("❌ S3 delete failed for CloudShare ID {$share->id}");
            return;
        }

        $reclaimed = $entities->sum('size');
        $this->totalSizeReclaimed += $reclaimed;

        $this->reclaimSpace($share->user_id, $reclaimed);
        $entities->each->delete();
        $share->delete();
    }

    protected function reclaimSpace(string $userId, int $size): void
    {
        $user = User::where('id', $userId)->first();

        if (!$user) {
            $this->warn("⚠️ User not found for UID: {$userId}");
            return;
        }

        $usage = $user->cloudUsage()->first();

        if (!$usage) {
            $this->warn("⚠️ No usage record for UID: {$userId}");
            return;
        }

        if ($usage->total_usage < $size) {
            $this->warn("⚠️ Usage underflow: UID {$userId} has {$usage->total_usage} bytes but {$size} will be removed.");
        }

        // $usage->decrement('total_usage', $size);
        $newUsage = max(0, $usage->total_usage - $size);
        $usage->total_usage = $newUsage;
        $usage->save();
    }
}
