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

class CloudBackupVerifyUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected CloudBackup $backup)
    {
        $this->queue = 'verify';
    }

    public function handle(CloudBackupManagementService $service): void
    {
        $service->verify($this->backup);
    }

    public function failed(\Throwable $exception): void
    {
        CloudBackup::query()
            ->whereKey($this->backup->getKey())
            ->where('status', CloudBackupStatus::VALIDATING->value)
            ->update(['status' => CloudBackupStatus::FAILED->value]);

        $logger = app(AppLogger::class);
        $logger->setContext('CloudBackupVerifyUpload');
        $logger->error('Cloud backup verification failed', [
            'backup_uid' => $this->backup->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('CloudBackupVerifyUpload failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
