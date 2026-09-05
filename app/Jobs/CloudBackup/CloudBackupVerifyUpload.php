<?php

namespace App\Jobs\CloudBackup;

use App\Enums\CloudBackupStatus;
use App\Jobs\Admin\MailAdminErrorAlert;
use App\Logging\AppLogger;
use App\Models\CloudBackup;
use App\Services\CloudBackupManagementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to verify the upload of a cloud backup.
 *
 * This job is dispatched when a cloud backup upload is completed and needs to be verified.
 * It uses the CloudBackupManagementService to confirm the upload.
 */
class CloudBackupVerifyUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(protected CloudBackup $backup)
    {
        $this->queue = 'verify';
    }

    /**
     * Execute the job.
     * handles the verification of the cloud backup upload.
     */
    public function handle(CloudBackupManagementService $service): void
    {
        $service->verify($this->backup);
    }

    /**
     * Handle a job failure.
     * This method is called when the job fails, allowing for custom failure handling.
     * 
     * @param \Throwable $exception
     */
    public function failed(\Throwable $exception): void
    {
        CloudBackup::query()
            ->whereKey($this->backup->getKey())
            ->where('status', CloudBackupStatus::VALIDATING->value)
            ->update([
                'status' => CloudBackupStatus::FAILED->value,
            ]);

        $logger = app(AppLogger::class);
        $logger->setContext('CloudBackupVerifyUpload');

        $logger->error('Cloud backup verification failed', [
            'backup_uid' => $this->backup->uid,
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        MailAdminErrorAlert::dispatch(
            'CloudBackupVerifyUpload failed',
            [
                'backup_uid' => $this->backup->uid,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]
        );
    }
}
