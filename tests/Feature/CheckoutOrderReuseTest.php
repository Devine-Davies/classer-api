<?php

namespace Tests\Feature;

use App\Logging\AppLogger;
use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderCheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class CheckoutOrderReuseTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_generates_checkout_session_hash_when_missing(): void
    {
        $order = Order::create([
            'quantity' => 1,
            'amount' => 1200,
            'subtotal_amount' => 1200,
            'discount_amount' => 0,
            'total_amount' => 1200,
            'currency' => 'gbp',
            'status' => 'pending',
        ]);

        $this->assertNotNull($order->checkout_session_hash);
        $this->assertSame(64, strlen((string) $order->checkout_session_hash));
    }

    public function test_order_creation_preserves_provided_checkout_session_hash(): void
    {
        $providedHash = hash('sha256', 'provided-checkout-token');

        $order = Order::create([
            'checkout_session_hash' => $providedHash,
            'quantity' => 1,
            'amount' => 1200,
            'subtotal_amount' => 1200,
            'discount_amount' => 0,
            'total_amount' => 1200,
            'currency' => 'gbp',
            'status' => 'pending',
        ]);

        $this->assertSame($providedHash, $order->checkout_session_hash);
    }

    public function test_checkout_session_hash_reuses_pending_order(): void
    {
        $catalogItem = $this->createCatalogItem(priceAmount: 1200);
        $checkoutSessionHash = hash('sha256', 'checkout-token');
        $service = new OrderCheckoutService(new AppLogger);

        $firstOrder = $service->createOrReusePendingOrder(
            catalogItemUids: [$catalogItem->uid],
            quantities: [$catalogItem->uid => 1],
            checkoutSessionHash: $checkoutSessionHash,
        );

        $secondOrder = $service->createOrReusePendingOrder(
            catalogItemUids: [$catalogItem->uid],
            quantities: [$catalogItem->uid => 3],
            checkoutSessionHash: $checkoutSessionHash,
        );

        $this->assertSame($firstOrder->uid, $secondOrder->uid);
        $this->assertSame($checkoutSessionHash, $secondOrder->checkout_session_hash);
        $this->assertSame(3, (int) $secondOrder->quantity);
        $this->assertSame(3600, (int) $secondOrder->amount);
        $this->assertSame(1, $secondOrder->items()->count());
        $this->assertSame(3, (int) $secondOrder->items()->first()->quantity);
    }

    public function test_checkout_session_hash_does_not_update_paid_order(): void
    {
        $catalogItem = $this->createCatalogItem(priceAmount: 1200);
        $checkoutSessionHash = hash('sha256', 'paid-checkout-token');
        $service = new OrderCheckoutService(new AppLogger);

        $order = $service->createOrReusePendingOrder(
            catalogItemUids: [$catalogItem->uid],
            quantities: [$catalogItem->uid => 1],
            checkoutSessionHash: $checkoutSessionHash,
        );

        $order->forceFill(['status' => 'paid'])->save();

        $this->expectException(LogicException::class);

        $service->createOrReusePendingOrder(
            catalogItemUids: [$catalogItem->uid],
            quantities: [$catalogItem->uid => 2],
            checkoutSessionHash: $checkoutSessionHash,
        );
    }

    private function createCatalogItem(int $priceAmount): CatalogItem
    {
        $product = Product::create([
            'title' => 'Reuse Test Product '.$priceAmount,
            'code' => 'REUSE'.$priceAmount,
        ]);

        $catalogItem = $product->catalogItem()->firstOrFail();
        $catalogItem->fill([
            'price_amount' => $priceAmount,
            'currency' => 'gbp',
            'is_published' => true,
        ]);
        $catalogItem->save();

        return $catalogItem->fresh();
    }
}