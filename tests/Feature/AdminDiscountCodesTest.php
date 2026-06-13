<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use App\Models\DiscountCodeRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDiscountCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_discount_codes_with_resource_shape(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        DiscountCode::create([
            'code' => 'ADMIN10',
            'discount_percentage' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/admin/discount-codes');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.code', 'ADMIN10')
            ->assertJsonStructure([
                'data' => [
                    [
                        'uid',
                        'code',
                        'discount_percentage',
                        'usage_count',
                        'is_active',
                    ],
                ],
            ]);
    }

    public function test_admin_cannot_change_locked_fields_after_redemption(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin);

        $product = Product::create([
            'slug' => 'admin-lock-product',
            'name' => 'Admin Lock Product',
            'price_amount' => 5000,
            'currency' => 'gbp',
            'is_active' => true,
        ]);

        $discountCode = DiscountCode::create([
            'code' => 'LOCK20',
            'discount_percentage' => 20,
            'product_id' => $product->uid,
            'is_active' => true,
        ]);

        $order = Order::create([
            'product_id' => $product->uid,
            'discount_code_id' => $discountCode->uid,
            'quantity' => 1,
            'amount' => 4000,
            'subtotal_amount' => 5000,
            'discount_amount' => 1000,
            'total_amount' => 4000,
            'currency' => 'gbp',
            'status' => 'paid',
            'customer_name' => 'Lock User',
            'customer_email' => 'lock@example.com',
            'paid_at' => now(),
        ]);

        DiscountCodeRedemption::create([
            'discount_code_id' => $discountCode->uid,
            'order_id' => $order->uid,
            'customer_email' => 'lock@example.com',
            'redeemed_at' => now(),
        ]);

        $response = $this->patchJson('/api/admin/discount-codes/'.$discountCode->uid, [
            'discount_percentage' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.discount_code.0', 'This discount code has redemptions and only status, expiry, or internal note can be edited.');
    }

    protected function createAdminUser(): User
    {
        return User::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Admin Tester',
            'email' => 'admin.'.Str::random(8).'@example.com',
            'password' => bcrypt('password123'),
            'account_status' => 1,
        ]);
    }
}
