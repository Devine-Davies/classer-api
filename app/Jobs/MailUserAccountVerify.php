<?php

namespace App\Jobs;

use App\Logging\AppLogger;
use App\Models\User;
use App\Services\MailSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to send user verification email
 *
 * This job is dispatched when a user registers and needs to verify their email address.
 * It uses the MailSenderService to handle the actual email sending.
 */
class MailUserAccountVerify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user
    ) {
        $this->queue = 'mail';
    }

    /**
     * Execute the job.
     *
     * @desc This method is called when the job is processed. It checks if the user's account is inactive and sends a verification email using the MailSenderService. If the account is already active, it logs a warning message.
     */
    public function handle(): void
    {
        if ($this->user->accountInactive()) {
            MailSenderService::verifyAccount($this->user);
        }
    }

    /**
     * Handle a job failure.
     *
     * @desc This method is called if the job fails during processing. It logs the exception
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailUserAccountVerify');
        $logger->error('Application threw an exception', [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailUserAccountVerify failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
