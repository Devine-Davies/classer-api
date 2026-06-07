<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __construct(protected StripePaymentService $stripePaymentService) {}

    /**
     * Handle incoming Stripe webhook events.
     *
     * @param  Request  $request  Incoming webhook request.
     * @return Response Webhook handling response.
     */
    public function handle(Request $request): Response
    {
        try {
            $this->stripePaymentService->handleWebhook(
                $request->getContent(),
                (string) $request->header('Stripe-Signature')
            );

            return response('ok', Response::HTTP_OK);
        } catch (\Throwable) {
            return response('invalid', Response::HTTP_BAD_REQUEST);
        }
    }
}
