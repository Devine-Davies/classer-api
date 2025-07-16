<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Services\CloudShareCleanupService;

class CloudShareCleanup extends Command
{
    protected $signature = 'app:cloud-share-cleanup {initiator}';
    protected $description = 'Cleans up expired CloudShares and reclaims cloud storage space.';
    protected int $totalSizeReclaimed = 0;

    /**
     * Constructor for the CloudShareCleanup command.
     * @param \App\Logging\AppLogger $logger
     */
    public function __construct(
        protected AppLogger $logger,
        protected CloudShareCleanupService $shareService
    ) {
        parent::__construct();
        $this->logger->setContext('CloudShareCleanup');
    }

    /**
     * Handles the command execution.
     * @return void
     */
    public function handle(): void
    {
        // 1) Start global timer
        $globalStart = microtime(true);

        $this->logger->info("Cleanup started", [
            'initiator' => $this->argument('initiator'),
        ]);

        try {
            CloudShare::where(function ($q) {
                $q->where('expires_at', '<=', now())
                    ->orWhereNull('expires_at');
            })
                ->chunk(100, function ($shares) {
                    // 2) Start chunk timer
                    $chunkStart = microtime(true);

                    $this->processChunk($shares);

                    // 3) Compute chunk duration
                    $chunkDuration = round(microtime(true) - $chunkStart, 2);

                    $this->logger->info("Chunk completed", [
                        'entities'              => $shares->pluck('id')->toArray(),
                        'total_size_reclaimed'  => $this->totalSizeReclaimed,
                        'chunk_duration_secs'   => $chunkDuration,
                    ]);
                });
        } catch (\Throwable $e) {
            $this->logger->error("Cleanup failed", [
                'error' => $e->getMessage(),
            ]);

            return;
        }

        // 4) Final summary with total duration
        $totalDuration = round(microtime(true) - $globalStart, 2);
        $this->logger->info("Cleanup completed", [
            'total_size_reclaimed' => $this->totalSizeReclaimed,
            'total_duration_secs'  => $totalDuration,
        ]);
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
     */
    protected function processShare(CloudShare $share): void
    {
        $directory = $this->shareService->resolveDirectory($share);
        if (! $directory || $this->shareService->isProtected($directory)) {
            $this->logInvalidDirectory($share, $directory, 'resolveDirectory failed');
            return;
        }

        if (! $this->shareService->deleteDirectory($directory)) {
            $this->logInvalidS3DeleteDirectory($share, $directory);
            return;
        }

        $reclaimed = $this->shareService->computeReclaimSize(collect($share->cloudEntities));
        $this->totalSizeReclaimed += $reclaimed;
        $this->shareService->finalizeCleanup($share, $reclaimed);
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

    /**
     * Summary of logInvalidS3DeleteDirectory
     * @param \App\Models\CloudShare $share
     * @param string $directory
     * @return void
     */
    protected function logInvalidS3DeleteDirectory(CloudShare $share, string $directory): void
    {
        $this->logger->error("S3 delete failed", [
            'user_id' => $share->user_id,
            'share_id' => $share->id,
            'directory' => $directory,
        ]);
    }
}
