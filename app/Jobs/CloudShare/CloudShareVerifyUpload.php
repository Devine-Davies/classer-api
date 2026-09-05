<?php

namespace App\Jobs\CloudShare;

use App\Enums\CloudShareStatus;
use App\Jobs\Admin\MailAdminErrorAlert;
use App\Logging\AppLogger;
use App\Models\CloudShare;
use App\Services\CloudShareManagementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to verify the upload of a cloud share.
 *
 * This job is dispatched when a cloud share upload is completed and needs to be verified.
 * It uses the CloudShareManagementService to confirm the upload.
 */
class CloudShareVerifyUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

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
        $cloudShareService->verify($this->cloudShare);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        CloudShare::query()
            ->whereKey($this->cloudShare->getKey())
            ->where('status', CloudShareStatus::VALIDATING->value)
            ->update([
                'status' => CloudShareStatus::FAILED->value,
            ]);

        $logger = app(AppLogger::class);
        $logger->setContext('CloudShareVerifyUpload');

        $logger->error('Cloud share verification failed', [
            'share_uid' => $this->cloudShare->uid,
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        MailAdminErrorAlert::dispatch(
            'CloudShareVerifyUpload failed',
            [
                'share_uid' => $this->cloudShare->uid,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]
        );
    }
}
