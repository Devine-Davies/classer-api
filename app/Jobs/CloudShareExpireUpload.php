<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Services\CloudShareCleanupService;

/**
 * Job to verify the upload of a cloud share.
 *
 * This job is dispatched when a cloud share upload is completed and needs to be verified.
 * It uses the CloudShareManagementService to confirm the upload.
 */
class CloudShareExpireUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The maximum number of times the job may be attempted.
     */
    public int $tries = 3;
    public int $backoff = 10; // seconds
    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected CloudShare $cloudShare
    ) {
        $this->queue = 'expire';
    }

    /**
     * Execute the job.
     */
    public function handle(
        CloudShareCleanupService $shareService
    ): void {
        $share = $this->cloudShare->load('cloudEntities');
        $directory = $shareService->resolveDirectory($share);

        if (! $directory || $shareService->isProtected($directory)) {
            throw new \Exception("Invalid directory or protected share: {$directory}");
            return;
        }

        if (! $shareService->deleteDirectory($directory)) {
            throw new \Exception("S3 delete failed for directory: {$directory}");
            return;
        }

        $shareService->finalize($share);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('CloudShareExpireUpload');
        $logger->error("Application threw an exception", [
            'share_uid' => $this->cloudShare->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch("CloudShareExpireUpload failed", [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
