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
use App\Models\Subscription;

class MailUserSubscriptionActivated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user,
        protected Subscription $subscription
    ) {
        $this->queue = 'mail';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        MailSenderController::subscriptionActivated($this->user, $this->subscription);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailUserSubscriptionActivated');
        $logger->error("Application threw an exception", [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch("MailUserSubscriptionActivated failed", [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
