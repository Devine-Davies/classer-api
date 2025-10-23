<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\MailSenderController;
use App\Logging\AppLogger;
use App\Models\User;

class MailEarlyAccessInvite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user)
    {
        $this->queue = 'mail';
    }

    /**
     * Execute the job.
     */ 
    public function handle(): void
    {
        MailSenderController::inviteUserToEarlyAccess($this->user);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailEarlyAccessInvite');
        $logger->error('Application threw an exception', [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailEarlyAccessInvite failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
