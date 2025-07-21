<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Http\Controllers\MailSenderController;
use App\Logging\AppLogger;

/**
 * Job to send user password reset success email
 *
 * This job is dispatched when a user successfully resets their password.
 * It uses the MailSenderController to handle the actual email sending.
 */
class MailUserPasswordResetSuccess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user
    ) {
        $this->queue = 'mail';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        MailSenderController::passwordResetSuccess($this->user);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailUserPasswordResetSuccess');
        $logger->error("Application threw an exception", [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch("MailUserPasswordResetSuccess failed", [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
