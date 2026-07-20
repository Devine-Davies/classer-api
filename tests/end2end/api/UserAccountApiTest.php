<?php

namespace Tests\End2End\Api;

use App\Enums\AccountStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\End2End\Api\Concerns\AssertsEndpointGuards;
use Tests\End2End\Api\Concerns\BuildsApiActors;
use Tests\TestCase;

class UserAccountApiTest extends TestCase
{
    use AssertsEndpointGuards;
    use BuildsApiActors;
    use RefreshDatabase;

    private const USER_ROUTES = [
        ['method' => 'GET', 'uri' => '/api/user'],
        ['method' => 'PATCH', 'uri' => '/api/user', 'payload' => ['name' => 'Updated Name']],
        ['method' => 'DELETE', 'uri' => '/api/user'],
        ['method' => 'PATCH', 'uri' => '/api/user/update-password', 'payload' => [
            'password' => 'password123',
            'newPassword' => 'password1234',
            'passwordConfirmation' => 'password1234',
        ]],
        ['method' => 'GET', 'uri' => '/api/user/enable-subscription'],
        ['method' => 'GET', 'uri' => '/api/user/cloud/share'],
        ['method' => 'POST', 'uri' => '/api/cloud/share', 'payload' => []],
    ];

    public function test_user_routes_require_authentication(): void
    {
        $this->assertRoutesRequireAuthentication(self::USER_ROUTES);
    }

    public function test_user_routes_require_user_ability(): void
    {
        $this->assertRoutesRequireAbility(self::USER_ROUTES);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = $this->actingAsUser();

        $response = $this->patchJson('/api/user', [
            'name' => 'Changed via E2E',
        ]);

        $response->assertOk()
            ->assertJsonPath('uid', $user->uid)
            ->assertJsonPath('name', 'Changed via E2E');
    }

    public function test_update_password_rejects_wrong_current_password(): void
    {
        $this->actingAsUser();

        $response = $this->patchJson('/api/user/update-password', [
            'password' => 'incorrect-current',
            'newPassword' => 'newsecret123',
            'passwordConfirmation' => 'newsecret123',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_update_password_changes_user_password(): void
    {
        $user = $this->actingAsUser();

        $response = $this->patchJson('/api/user/update-password', [
            'password' => 'password123',
            'newPassword' => 'newsecret123',
            'passwordConfirmation' => 'newsecret123',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Password updated');

        $this->assertTrue(Hash::check('newsecret123', (string) $user->fresh()->password));
    }

    public function test_deactivate_sets_account_status_to_deactivated(): void
    {
        $this->actingAsUser();

        $response = $this->deleteJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('accountStatus', AccountStatus::DEACTIVATED->value)
            ->assertJsonPath('accountStatusLabel', 'Deactivated');
    }

    public function test_cloud_share_index_requires_active_subscription(): void
    {
        $this->actingAsUser();

        $response = $this->getJson('/api/user/cloud/share');

        $response->assertStatus(403);
    }

    public function test_cloud_share_index_returns_user_shares_with_active_subscription(): void
    {
        $user = $this->actingAsUser();
        $this->createActiveSubscriptionFor($user);

        $response = $this->getJson('/api/user/cloud/share');

        $response->assertOk()
            ->assertJsonIsArray();
    }

    public function test_cloud_share_create_requires_subscription(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/cloud/share', [
            'resourceId' => 'resource-e2e',
            'entities' => [
                [
                    'uid' => 'entity-1',
                    'sourceFile' => 'video.mp4',
                    'contentType' => 'video/mp4',
                    'size' => 1024,
                ],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_cloud_share_create_currently_errors_with_active_subscription_due_to_middleware_bug(): void
    {
        $user = $this->actingAsUser();
        $this->createActiveSubscriptionFor($user);

        $response = $this->postJson('/api/cloud/share', []);

        $response->assertStatus(500);
    }
}
