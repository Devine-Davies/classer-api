<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Services\CloudShareManagementService;

/**
 * Job to verify the upload of a cloud share.
 *
 * This job is dispatched when a cloud share upload is completed and needs to be verified.
 * It uses the CloudShareManagementService to confirm the upload.
 */
class CloudShareVerifyUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected CloudShare $cloudShare
    ) {
        $this->queue = 'verify';
    }

    /**
     * Execute the job.
     */
    public function handle(
        CloudShareManagementService $cloudShareService,
    ): void {
        $cloudShare = $this->cloudShare->load('cloudEntities');
        $cloudShareService->verify($cloudShare);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('CloudShareVerifyUpload');
        $logger->error("Application threw an exception", [
            'share_uid' => $this->cloudShare->uid,
            'exception' => $exception,
        ]);

        // Dispatch an alert to the admin about the failure
        MailAdminErrorAlert::dispatch("CloudShareVerifyUpload failed", [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
