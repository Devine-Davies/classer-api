<?php

namespace App\Console\Commands;

use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Services\CloudShareCleanupService;
use Illuminate\Console\Command;

/**
 * Manual command to trigger cloud share cleanup.
 *
 * This command allows manual cleanup of a cloud share by its UID.
 * This cmd will force cleanup of the directory associated with the share,
 * if the directory is not protected, it will continue with the cleanup process.
 * update the CloudShare record to mark it as cleaned up and
 * recalculate storage back to the user.
 *
 * Usage:
 * php artisan manual:cloud-share-cleanup {cloudShareUid}
 * php artisan manual:cloud-share-cleanup 77c86e4a-fc1e-4675-9323-543a5bfdab07
 */
class ManualCloudShareCleanup extends Command
{
    protected $signature = 'manual:cloud-share-cleanup {cloudShareUid}';

    protected $description = 'Manually trigger cloud share cleanup';

    public function __construct(
        protected AppLogger $logger,
        protected CloudShareCleanupService $shareService
    ) {
        $this->logger->setContext('ManualCloudShareCleanup');
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $cloudShareUid = $this->argument('cloudShareUid');
            $cloudShare = CloudShare::where('uid', $cloudShareUid)->first();

            if (! $cloudShare) {
                return $this->failed("Cloud share not found: {$cloudShareUid}");
            }

            $directory = $this->shareService->resolveDirectory($cloudShare);

            if (! $directory || $this->shareService->isProtected($directory)) {
                return $this->failed("Invalid directory or protected share: {$directory}");
            }

            if (! $this->shareService->deleteDirectory($directory)) {
                return $this->failed("S3 delete failed for directory: {$directory}");
            }

            $this->shareService->finalize($cloudShare);
            $this->logger->info('Cleanup completed', [
                'share_uid' => $cloudShareUid,
                'directory' => $directory,
            ]);

            $this->info("Cleanup completed for {$cloudShareUid}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            return $this->failed('Failed to clean up cloud share: '.$e->getMessage());
        }
    }

    /**
     * Handle a command failure.
     */
    public function failed($error): int
    {
        $this->error($error);
        $this->logger->error('ManualCloudShareCleanup command failed', [
            'cloud_share_uid' => $this->argument('cloudShareUid'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
