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
            // Fetch and process CloudShare instances that have expired
            // where expires_at is less than or equal to the current time or null
            DB::transaction(function () {
                CloudShare::where(function ($query) {
                    $query->where('expires_at', '<=', now())->orWhereNull('expires_at');
                })->chunk(100, function ($shares) {
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
     * 
     * Description:
     * the dir is e9dfa62d-3cfb-4385-862e-d131584e0db9 in cloud-share/e9dfa62d-3cfb-4385-862e-d131584e0db9/4dd85b75-fa9e-474f-9b63-f5e5956f6135.MP4
     * we can use the first part of the key as the directory and take it from the first entity's key
     * 
     * @param \App\Models\CloudShare $share
     * @return void
     */
    protected function processShare(CloudShare $share): void
    {
        $entities = collect($share->cloudEntities);
        $firstKey = $entities->pluck('key')->first();

        if (!$this->isValidKeyFormat($firstKey)) {
            $this->logInvalidKey($share, $firstKey, $entities->count());
            return;
        }

        $cloudShareDir = config('classer.cloud_share_directory', 'cloud-share');
        $directoryKey = explode('/', $firstKey)[1];
        $directory = $cloudShareDir . '/' . $directoryKey;
        if ($this->isProtectedDirectory(directory: $directory, more: [$cloudShareDir])) {
            $this->logInvalidDirectory($share, $directory, $firstKey);
            return;
        }

        if (!Storage::disk('s3')->deleteDirectory($directory)) {
            $this->logger->error("S3 delete failed", [
                'user_id' => $share->user_id,
                'share_id' => $share->id,
                'directory' => $directory,
            ]);
            return;
        }

        // Calculate the total size of the entities to be reclaimed
        $reclaimed = $entities->sum('size');
        $this->totalSizeReclaimed += $reclaimed;

        // Reclaim space in a transaction
        DB::transaction(function () use ($share, $entities, $reclaimed) {
            $this->reclaimSpace($share->user_id, $reclaimed);
            $entities->each->delete();
            $share->delete();
        });
    }

    /**
     *  Reclaim space for a user by reducing their total cloud usage.
     * @param string $userId
     * @param int $size
     * @return void
     */
    protected function reclaimSpace(string $userId, int $size): void
    {
        $user = User::find($userId);

        if (!$user || !$user->cloudUsage) {
            $this->logger->error('Unable to retrieve user or cloud usage record', [
                'user_id' => $userId,
            ]);
            return;
        }

        $usage = $user->cloudUsage;
        $originalUsage = $usage->total_usage;

        if ($originalUsage < $size) {
            $this->logger->warning('Reclaiming more space than user currently uses', [
                'user_id' => $userId,
                'total_usage' => $originalUsage,
                'size_to_remove' => $size,
            ]);
        }

        $usage->total_usage = max(0, $originalUsage - $size);
        $usage->save();
    }

    /**
     * Is the key format valid?
     * 
     * @param mixed $key
     * @return bool
     */
    protected function isValidKeyFormat(?string $key): bool
    {
        return $key && str_contains($key, '/');
    }

    /**
     * Is the directory allowed?
     * 
     * @param mixed $directory
     * @return bool
     */
    protected function isProtectedDirectory(?string $directory, ?array $more = []): bool
    {
        return in_array($directory, array_merge(
            [null, '.', '..'],
            $more ?? [],
        ));
    }

    /**
     * Log an invalid key format for a CloudShare.
     * 
     * @param \App\Models\CloudShare $share
     * @param mixed $key
     * @param int $count
     * @return void
     */
    protected function logInvalidKey(CloudShare $share, ?string $key, int $count): void
    {
        $this->logger->error("Invalid key format for CloudShare", [
            'user_id' => $share->user_id,
            'share_id' => $share->id,
            'key' => $key,
            'share' => $share->toArray(),
            'entitiesCount' => $count,
        ]);
    }

    /**
     * Summary of logInvalidDirectory
     * @param \App\Models\CloudShare $share
     * @param string $directory
     * @param string $key
     * @return void
     */
    protected function logInvalidDirectory(CloudShare $share, string $directory, string $key): void
    {
        $this->logger->error("Invalid directory for CloudShare", [
            'user_id' => $share->user_id,
            'share_id' => $share->id,
            'directory' => $directory,
            'key' => $key,
        ]);
    }
}
