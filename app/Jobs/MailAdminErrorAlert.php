<?php

namespace App\Jobs;

use App\Http\Controllers\MailSenderController;
use App\Logging\AppLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailAdminErrorAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $title,
        protected array $exception = []
    ) {
        $this->queue = 'mail';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        MailSenderController::sendAdminErrorAlert(
            $this->title,
            $this->exception
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailUserAccountVerified');
        $logger->error('Application threw an exception', [
            'title' => $this->title,
            'exception' => $exception,
        ]);
    }
}
