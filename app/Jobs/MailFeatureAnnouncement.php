<?php

namespace App\Jobs;

use App\Logging\AppLogger;
use App\Mail\SuperSimpleEmail;
use App\Models\User;
use App\Services\MailSenderService;
use App\Utils\EmailHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class MailFeatureAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected User $user
    ) {
        $this->queue = 'mail';
    }

    public function handle(): void
    {
        if (method_exists(MailSenderService::class, 'featureAnnouncement')) {
            MailSenderService::featureAnnouncement($this->user);

            return;
        }

        // Fallback for mixed deploy states where queue workers still run an older MailSenderService.
        $to = $this->user->email;
        $content = EmailHelper::render(
            <<<'HTML'
                <p>We have shipped new improvements to make sharing from Classer faster and easier.</p>
                <p>Update your app to try the latest flow and let us know what you think.</p>
            HTML,
            [],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, 'New in Classer: Feature update', [
                'title' => 'Hi '.$this->user->name,
                'name' => $this->user->name,
                'button-label' => 'See What Is New',
                'button-link' => url('/classer-share'),
                'content' => $content,
            ]),
        );
    }

    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailFeatureAnnouncement');
        $logger->error('Application threw an exception', [
            'user_uid' => $this->user->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailFeatureAnnouncement failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}
