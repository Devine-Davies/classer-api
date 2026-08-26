<?php

namespace App\Jobs\Mail;

use App\Jobs\Admin\MailAdminErrorAlert;
use App\Logging\AppLogger;
use App\Models\User;
use App\Services\MailSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailServiceAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user
    ) {
        $this->queue = 'mail';
    }

    public function handle(): void
    {
        MailSenderService::serviceAnnouncement($this->user);
    }

    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailServiceAnnouncement');
        $logger->error('Application threw an exception', [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailServiceAnnouncement failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}