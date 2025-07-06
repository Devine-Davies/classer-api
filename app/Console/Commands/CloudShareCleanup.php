<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Models\User;

class CloudShareCleanup extends Command
{
    protected $signature = 'app:cloud-share-cleanup {initiator}';
    protected $description = 'Cleans up expired CloudShares and reclaims cloud storage space.';
    protected int $totalSizeReclaimed = 0;

    /**
     * Constructor for the CloudShareCleanup command.
     * @param \App\Logging\AppLogger $logger
     */
    public function __construct(protected AppLogger $logger)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->logger->setContext(context: 'CloudShareCleanup');
    }

    /**
     * Handles the command execution.
     * @return void
     */
    public function handle()
    {
        $this->logger->info("Cleanup started", [
            'initiator' => $this->argument('initiator'),
        ]);

        try {
            DB::transaction(function () {
                // Fetch and process CloudShare instances that have expired
                CloudShare::where('expires_at', '<=', now())
                    ->chunk(100, function ($shares) {
                        $this->processChunk($shares);
                        $this->logger->info("Chunk completed", [
                            'entities' => $shares->pluck('id')->toArray(),
                            'total_size_reclaimed' => $this->totalSizeReclaimed,
                        ]);
                    });
            });
        } catch (\Throwable $e) {
            $this->logger->error("Cleanup failed", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Processes a chunk of CloudShare instances.
     * @param mixed $shares
     * @return void
     */
    protected function processChunk($shares): void
    {
        foreach ($shares as $share) {
            try {
                $this->processShare($share);
            } catch (\Throwable $e) {
                $this->logger->error("Error processing CloudShare", [
                    'user_id' => $share->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Processes a single CloudShare instance.
     * @param \App\Models\CloudShare $share
     * @return void
     */
    protected function processShare(CloudShare $share): void
    {
        $entities = collect($share->cloudEntities);
        $firstKey = $entities->pluck('key')->first();

        if (!$firstKey || !str_contains($firstKey, '/')) {
            $this->logger->error("Invalid key format for CloudShare", [
                'user_id' => $share->user_id,
                'share_id' => $share->id,
                'key' => $firstKey,
                'share' => $share->toArray(),
                'entitiesCount' => $entities->count(),
            ]);

            return;
        }

        $directory = explode('/', $firstKey)[0];

        if (!Storage::disk('s3')->deleteDirectory($directory)) {
            $this->logger->error("S3 delete failed", [
                'user_id' => $share->user_id,
                'share_id' => $share->id,
                'directory' => $directory,
            ]);
            return;
        }

        $reclaimed = $entities->sum('size');
        $this->totalSizeReclaimed += $reclaimed;

        $this->reclaimSpace($share->user_id, $reclaimed);
        $entities->each->delete();
        $share->delete();
    }

    /**
     *  Reclaim space for a user by reducing their total cloud usage.
     * @param string $userId
     * @param int $size
     * @return void
     */
    protected function reclaimSpace(string $userId, int $size): void
    {
        try {
            $user = User::where('id', $userId)->firstOrFail();
            $usageEntity = $user->cloudUsage()->firstOrFail();

            if ($usageEntity->total_usage < $size) {
                $this->logger->error("Failed to reclaim space", [
                    'user_id' => $userId,
                    'total_usage' => $usageEntity->total_usage,
                    'size_to_remove' => $size,
                ]);
            }

            // $usage->decrement('total_usage', $size);
            $newUsage = max(0, $usageEntity->total_usage - $size);
            $usageEntity->total_usage = $newUsage;
            $usageEntity->save();
        } catch (\Throwable $e) {
            $this->logger->error("Error retrieving user cloud usage", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return;
        }
    }
}
