<?php

namespace Tests\Feature;

use App\Logging\AppLogger;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookEventHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_payment_method_intent_status_maps_to_pending(): void
    {
        $service = new StripePaymentService(new AppLogger);
        $method = new \ReflectionMethod($service, 'mapIntentStatus');
        $method->setAccessible(true);

        $this->assertSame('pending', $method->invoke($service, 'requires_payment_method'));
    }

    public function test_canceled_intent_status_maps_to_failed(): void
    {
        $service = new StripePaymentService(new AppLogger);
        $method = new \ReflectionMethod($service, 'mapIntentStatus');
        $method->setAccessible(true);

        $this->assertSame('failed', $method->invoke($service, 'canceled'));
    }

    public function test_requires_action_event_updates_payment_status(): void
    {
        [$order, $payment] = $this->createPendingOrderAndPayment('pi_requires_action_test');

        $this->processEvent(
            id: 'evt_requires_action_1',
            type: 'payment_intent.requires_action',
            objectPayload: [
                'id' => 'pi_requires_action_test',
            ]
        );

        $this->assertDatabaseHas('order_payments', [
            'uid' => $payment->uid,
            'status' => 'requires_action',
        ]);

        $this->assertDatabaseHas('orders', [
            'uid' => $order->uid,
            'status' => 'pending',
        ]);
    }

    public function test_canceled_event_marks_payment_failed_and_keeps_order_pending(): void
    {
        [$order, $payment] = $this->createPendingOrderAndPayment('pi_canceled_test');

        $this->processEvent(
            id: 'evt_canceled_1',
            type: 'payment_intent.canceled',
            objectPayload: [
                'id' => 'pi_canceled_test',
            ]
        );

        $this->assertDatabaseHas('order_payments', [
            'uid' => $payment->uid,
            'status' => 'failed',
            'failure_code' => 'payment_intent_canceled',
        ]);

        $this->assertDatabaseHas('orders', [
            'uid' => $order->uid,
            'status' => 'pending',
        ]);
    }

    /**
     * @return array{Order, OrderPayment}
     */
    private function createPendingOrderAndPayment(string $intentId): array
    {
        $order = Order::create([
            'quantity' => 1,
            'amount' => 1500,
            'subtotal_amount' => 1500,
            'discount_amount' => 0,
            'total_amount' => 1500,
            'currency' => 'gbp',
            'status' => 'pending',
        ]);

        $payment = OrderPayment::create([
            'order_id' => $order->uid,
            'stripe_payment_intent_id' => $intentId,
            'status' => 'pending',
            'amount' => 1500,
            'currency' => 'gbp',
        ]);

        return [$order, $payment];
    }

    private function processEvent(string $id, string $type, array $objectPayload): void
    {
        $service = new StripePaymentService(new AppLogger);

        $event = new class($id, $type, $objectPayload)
        {
            public string $id;

            public string $type;

            public object $data;

            public function __construct(string $id, string $type, array $objectPayload)
            {
                $this->id = $id;
                $this->type = $type;
                $this->data = (object) [
                    'object' => (object) $objectPayload,
                ];
            }

            /**
             * @return array<string, mixed>
             */
            public function toArray(): array
            {
                return [
                    'id' => $this->id,
                    'type' => $this->type,
                    'data' => [
                        'object' => (array) $this->data->object,
                    ],
                ];
            }
        };

        $method = new \ReflectionMethod($service, 'processEvent');
        $method->setAccessible(true);
        $method->invoke($service, $event);
    }
}
