<?php

namespace App\Jobs;

use App\Http\Controllers\MailSenderController;
use App\Logging\AppLogger;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MailOrderPaymentConfirmed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Order $order,
        protected OrderPayment $payment
    ) {
        $this->queue = 'mail';
    }

    public function handle(): void
    {
        $this->order->loadMissing('product');
        MailSenderController::orderPaymentConfirmed($this->order, $this->payment);
    }

    public function failed(\Throwable $exception): void
    {
        $logger = app(AppLogger::class);
        $logger->setContext('MailOrderPaymentConfirmed');
        $logger->error('Application threw an exception', [
            'order_uid' => $this->order->uid,
            'payment_uid' => $this->payment->uid,
            'exception' => $exception,
        ]);

        MailAdminErrorAlert::dispatch('MailOrderPaymentConfirmed failed', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'order_uid' => $this->order->uid,
            'payment_uid' => $this->payment->uid,
        ]);
    }
}
