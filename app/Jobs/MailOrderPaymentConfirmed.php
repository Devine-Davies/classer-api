<?php

namespace App\Jobs;

use App\Services\MailSenderService;
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

    /**
     * Execute the job.
     *
     * @desc This method is called when the job is processed. It sends an order payment confirmation email to the customer using the MailSenderService.
     */
    public function handle(): void
    {
        MailSenderService::orderPaymentConfirmed($this->order, $this->payment);
    }

    /**
     * Handle a job failure.
     *
     * @desc This method is called if the job fails during processing. It logs the exception and dispatches an alert to the admin.
     */
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
