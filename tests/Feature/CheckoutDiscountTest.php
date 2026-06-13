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

    public function test_create_order_applies_percentage_discount(): void
    {
        $product = Product::create([
            'slug' => 'discount-test-product',
            'name' => 'Discount Test Product',
            'price_amount' => 10000,
            'currency' => 'gbp',
            'is_active' => true,
        ]);

        DiscountCode::create([
            'code' => 'SAVE25',
            'discount_percentage' => 25,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $response = $this->postJson('/api/checkout/orders', [
            'product_uid' => $product->uid,
            'quantity' => 1,
            'discount_code' => 'SAVE25',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('order.subtotal_amount', 10000)
            ->assertJsonPath('order.discount_amount', 2500)
            ->assertJsonPath('order.total_amount', 7500)
            ->assertJsonPath('order.amount', 7500)
            ->assertJsonPath('order.discount.code', 'SAVE25');
    }

    public function test_create_order_rejects_discount_that_reduces_total_to_zero(): void
    {
        $product = Product::create([
            'slug' => 'free-product-test',
            'name' => 'Free Product Test',
            'price_amount' => 5000,
            'currency' => 'gbp',
            'is_active' => true,
        ]);

        DiscountCode::create([
            'code' => 'FREE100',
            'discount_percentage' => 100,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $response = $this->postJson('/api/checkout/orders', [
            'product_uid' => $product->uid,
            'quantity' => 1,
            'discount_code' => 'FREE100',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.reason_code.0', 'CANNOT_REDUCE_TOTAL_TO_ZERO');
    }

    public function test_apply_discount_endpoint_updates_pending_order_preview(): void
    {
        $product = Product::create([
            'slug' => 'apply-endpoint-product',
            'name' => 'Apply Endpoint Product',
            'price_amount' => 10000,
            'currency' => 'gbp',
            'is_active' => true,
        ]);

        $discountCode = DiscountCode::create([
            'code' => 'SAVE10',
            'discount_percentage' => 10,
            'is_active' => true,
            'usage_count' => 0,
        ]);

        $order = Order::create([
            'product_id' => $product->uid,
            'quantity' => 1,
            'amount' => 10000,
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'currency' => 'gbp',
            'status' => 'pending',
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

        $response = $this->postJson('/api/checkout/orders/'.$order->uid.'/discount', [
            'discount_code' => $discountCode->code,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('is_valid', true)
            ->assertJsonPath('reason_code', null)
            ->assertJsonPath('code', 'SAVE10')
            ->assertJsonPath('pricing_preview.subtotal', 10000)
            ->assertJsonPath('pricing_preview.discount', 1000)
            ->assertJsonPath('pricing_preview.total', 9000);
    }

    public function test_apply_discount_endpoint_returns_reason_code_for_invalid_code(): void
    {
        $product = Product::create([
            'slug' => 'apply-invalid-product',
            'name' => 'Apply Invalid Product',
            'price_amount' => 10000,
            'currency' => 'gbp',
            'is_active' => true,
        ]);

        $order = Order::create([
            'product_id' => $product->uid,
            'quantity' => 1,
            'amount' => 10000,
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'currency' => 'gbp',
            'status' => 'pending',
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

        $response = $this->postJson('/api/checkout/orders/'.$order->uid.'/discount', [
            'discount_code' => 'DOES_NOT_EXIST',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('is_valid', false)
            ->assertJsonPath('reason_code', 'INVALID_CODE')
            ->assertJsonPath('message', 'Discount code is not eligible.');
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
