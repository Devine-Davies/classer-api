<?php

namespace Tests\Unit\Http\Resources\Web\Admin;

use App\Enums\AccountStatus;
use App\Http\Resources\Web\Admin\UserAccountResource;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class UserAccountResourceTest extends TestCase
{
    public function test_it_exposes_display_fields_for_admin_view(): void
    {
        $resource = new UserAccountResource(new class {
            public $uid = 'abc123';
            public $name = 'Jane Doe';
            public $email = 'jane@example.com';
            public $dob = '1990-01-01';
            public $created_at = '2024-01-01 10:00:00';
            public $updated_at = '2024-01-02 10:00:00';
            public $account_status = AccountStatus::VERIFIED;
            public $subscriptions = [
                ['status' => 'active', 'plan' => ['title' => 'Pro']],
                ['status' => 'cancelled', 'plan' => ['title' => 'Basic']],
            ];
            public $cloudUsage = null;
            public $plan = ['title' => 'Pro'];
            public $plan_id = null;

            public function relationLoaded($name): bool
            {
                return false;
            }
        });

        $data = $resource->resolve(new Request());

        $this->assertSame('Verified', $data['accountStatusLabel']);
        $this->assertSame('emerald', $data['accountStatusClass']);
        $this->assertSame('Pro', $data['planLabel']);
        $this->assertSame(['Pro'], $data['activePlanLabels']);
        $this->assertSame(1, $data['activeSubscriptionCount']);
        $this->assertSame(2, $data['subscriptionCount']);
    }

    public function test_it_maps_integer_account_status_values_to_the_expected_label(): void
    {
        $resource = new UserAccountResource(new class {
            public $uid = 'abc123';
            public $name = 'Jane Doe';
            public $email = 'jane@example.com';
            public $dob = '1990-01-01';
            public $created_at = '2024-01-01 10:00:00';
            public $updated_at = '2024-01-02 10:00:00';
            public $account_status = 0;
            public $subscriptions = [];
            public $cloudUsage = null;
            public $plan = null;
            public $plan_id = null;

            public function relationLoaded($name): bool
            {
                return false;
            }
        });

        $data = $resource->resolve(new Request());

        $this->assertSame('Inactive', $data['accountStatusLabel']);
        $this->assertSame('slate', $data['accountStatusClass']);
    }
}
