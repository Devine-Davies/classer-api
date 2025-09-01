<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\MailSenderController;
use App\Models\User;
use App\Logging\AppLogger;

/**
 * Job to send user verification email
 * 
 * This job is dispatched when a user registers and needs to verify their email address.
 * It uses the MailSenderController to handle the actual email sending.
 */
class MailUserAccountVerify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user
    ) {
        $this->queue = 'mail';
    }

    public function handle()
    {
        if ($this->user->accountInactive()) {
            MailSenderController::verifyAccount($this->user);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailUserAccountVerify');
        $logger->error("Application threw an exception", [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch("MailUserAccountVerify failed", [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
