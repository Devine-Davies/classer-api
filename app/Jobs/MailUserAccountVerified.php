<?php

namespace App\Jobs;

use App\Services\MailSenderService;
use App\Logging\AppLogger;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job to send user account verification email
 *
 * This job is dispatched when a user successfully verifies their account.
 * It uses the MailSenderService to handle the actual email sending.
 */
class MailUserAccountVerified implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user
    ) {
        $this->queue = 'mail';
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        MailSenderService::accountVerified($this->user);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailUserAccountVerified');
        $logger->error('Application threw an exception', [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailUserAccountVerified failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
