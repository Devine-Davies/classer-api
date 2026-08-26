<?php

namespace Tests\Feature;

use App\Logging\AppLogger;
use App\Models\DiscountCode;
use App\Models\DiscountCodeRedemption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CheckoutDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_checkout_api_is_not_registered(): void
    {
        $this->postJson('/api/checkout/orders')->assertNotFound();
    }

    public function test_webhook_redeems_discount_once_and_refund_does_not_restore_usage(): void
    {
        Queue::fake();

        $product = Product::create([
            'slug' => 'webhook-test-product',
            'name' => 'Webhook Test Product',
            'price_amount' => 10000,
            'currency' => 'gbp',
            'is_active' => true,
        ]);

        $discountCode = DiscountCode::create([
            'code' => 'SAVE20',
            'discount_percentage' => 20,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $order = Order::create([
            'product_id' => $product->uid,
            'discount_code_id' => $discountCode->uid,
            'quantity' => 1,
            'amount' => 8000,
            'subtotal_amount' => 10000,
            'discount_amount' => 2000,
            'total_amount' => 8000,
            'currency' => 'gbp',
            'status' => 'pending',
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'discount_snapshot' => [
                'code' => 'SAVE20',
                'percentage' => 20,
            ],
        ]);

        OrderItem::create([
            'order_id' => $order->uid,
            'product_id' => $product->uid,
            'product_name' => $product->name,
            'unit_amount' => 10000,
            'quantity' => 1,
            'line_amount' => 10000,
            'currency' => 'gbp',
        ]);

        OrderPayment::create([
            'order_id' => $order->uid,
            'stripe_payment_intent_id' => 'pi_discount_test',
            'status' => 'pending',
            'amount' => 8000,
            'currency' => 'gbp',
        ]);

        $service = new StripePaymentService(new AppLogger);
        $method = new \ReflectionMethod($service, 'processEvent');
        $method->setAccessible(true);

        $method->invoke($service, (object) [
            'id' => 'evt_discount_success_1',
            'type' => 'payment_intent.succeeded',
            'data' => (object) [
                'object' => (object) [
                    'id' => 'pi_discount_test',
                    'customer' => 'cus_123',
                    'payment_method' => 'pm_123',
                ],
            ],
        ]);

        $this->assertSame(1, DiscountCodeRedemption::count());
        $this->assertSame(1, (int) $discountCode->fresh()->usage_count);

        $method->invoke($service, (object) [
            'id' => 'evt_discount_success_2',
            'type' => 'payment_intent.succeeded',
            'data' => (object) [
                'object' => (object) [
                    'id' => 'pi_discount_test',
                    'customer' => 'cus_123',
                    'payment_method' => 'pm_123',
                ],
            ],
        ]);

        $this->assertSame(1, DiscountCodeRedemption::count());
        $this->assertSame(1, (int) $discountCode->fresh()->usage_count);

        $method->invoke($service, (object) [
            'id' => 'evt_discount_refund_1',
            'type' => 'charge.refunded',
            'data' => (object) [
                'object' => (object) [
                    'id' => 'ch_123',
                    'payment_intent' => 'pi_discount_test',
                ],
            ],
        ]);

        $this->assertSame(1, DiscountCodeRedemption::count());
        $this->assertSame(1, (int) $discountCode->fresh()->usage_count);
        $this->assertDatabaseHas('order_payments', [
            'stripe_payment_intent_id' => 'pi_discount_test',
            'status' => 'refunded',
        ]);
    }

    public function test_resolve_payment_intent_id_ignores_charge_succeeded_event(): void
    {
        $service = new StripePaymentService(new AppLogger);
        $method = new \ReflectionMethod($service, 'resolvePaymentIntentId');
        $method->setAccessible(true);

        $intentId = $method->invoke($service, (object) [
            'id' => 'evt_charge_succeeded_1',
            'type' => 'charge.succeeded',
            'data' => (object) [
                'object' => (object) [
                    'id' => 'ch_123',
                    'payment_intent' => 'pi_discount_test',
                ],
            ],
        ]);

        $this->assertNull($intentId);
    }
}
