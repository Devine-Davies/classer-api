<?php

namespace App\Jobs;

use App\Http\Controllers\MailSenderController;
use App\Logging\AppLogger;
use App\Models\PromotionRedemption;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailPromotionalRedeemEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected PromotionRedemption $redemption,
        protected string $token
    ) {
        $this->queue = 'mail';
    }

    public function handle(): void
    {
        MailSenderController::promotionalRedeemEmail($this->redemption, $this->token);
    }

    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailPromotionalRedeemEmail');
        $logger->error('Application threw an exception', [
            'promotion_redemption_uid' => $this->redemption->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailPromotionalRedeemEmail failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'promotion_redemption_uid' => $this->redemption->uid,
        ]);
    }
}
