<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Logging\AppLogger;
use App\Http\Controllers\MailSenderController;

class MailAdminErrorAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $title,
        protected array $exception = []
    )
    {
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
        $logger->error("Application threw an exception", [
            'title' => $this->title,
            'exception' => $exception,
        ]);
    }
}
