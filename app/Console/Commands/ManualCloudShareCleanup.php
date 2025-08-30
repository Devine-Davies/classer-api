<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Logging\AppLogger;
use App\Services\CloudShareCleanupService;
use App\Models\CloudShare;

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
     * Execute the job.
     */
    public function handle(): void
    {
        $cloudShareUid = $this->argument('cloudShareUid');
        $cloudShare = CloudShare::where('uid', $cloudShareUid)->first();
        $directory = $this->shareService->resolveDirectory($cloudShare);

        if (! $directory || $this->shareService->isProtected($directory)) {
            throw new \Exception("Invalid directory or protected share: {$directory}");
        }

        if (! $this->shareService->deleteDirectory($directory)) {
            throw new \Exception("S3 delete failed for directory: {$directory},");
        }

        $this->shareService->finalize($cloudShare);
        $this->logger->info("Cleanup completed", [
            'share_uid' => $cloudShareUid,
            'directory' => $directory,
        ]);
    }

    /**
     * Handle a command failure.
     */
    function failed($error): int
    {
        $this->error($error);
        $this->logger->error("AssignSubscription command failed", [
            'email' => $this->argument('email'),
            'code' => $this->argument('code'),
            'error' => $error,
        ]);

        return Command::FAILURE;
    }
}
