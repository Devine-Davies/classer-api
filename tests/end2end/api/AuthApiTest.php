<?php

namespace Tests\End2End\Api;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\End2End\Api\Concerns\BuildsApiActors;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use BuildsApiActors;
    use RefreshDatabase;

    public static function invalidPublicAuthPayloadProvider(): array
    {
        return [
            'register requires name/email' => ['POST', '/api/auth/register', [], 400],
            'verify registration requires token and password' => ['POST', '/api/auth/register/verify', [], 422],
            'forgot password requires email' => ['POST', '/api/auth/password/forgot', [], 400],
            'reset password requires token/password' => ['POST', '/api/auth/password/reset', [], 401],
            'login requires credentials' => ['POST', '/api/auth/login', [], 422],
        ];
    }

    #[DataProvider('invalidPublicAuthPayloadProvider')]
    public function test_public_auth_endpoints_validate_payloads(
        string $method,
        string $uri,
        array $payload,
        int $expectedStatus
    ): void {
        $response = $this->json($method, $uri, $payload);

        $response->assertStatus($expectedStatus);
    }

    public function test_verified_user_can_login_and_receive_api_token(): void
    {
        $user = $this->makeVerifiedUser([
            'email' => 'auth.e2e@example.com',
            'password' => 'secret1234',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['status', 'message', 'token']);

        $this->assertNotEmpty($response->json('token'));
        $this->assertNotEmpty($response->headers->get('X-Token'));
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->makeVerifiedUser([
            'email' => 'auth.badpass@example.com',
            'password' => 'secret1234',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'auth.badpass@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', false);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    public function test_logout_revokes_existing_tokens(): void
    {
        $user = $this->makeVerifiedUser();
        $token = $user->createToken('e2e-auth', ['user'])->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Logged out');

        $this->assertSame(0, $user->fresh()->tokens()->count());
    }

    public function test_auto_login_requires_user_ability(): void
    {
        $user = $this->makeVerifiedUser();
        $this->actingAsUser($user, ['admin']);

        $response = $this->getJson('/api/auth/auto-login');

        $response->assertStatus(403);
    }

    public function test_auto_login_rejects_inactive_account(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::INACTIVE,
        ]);

        $this->actingAsUser($user, ['user']);

        $response = $this->getJson('/api/auth/auto-login');

        $response->assertStatus(401);
    }

    public function test_auto_login_returns_a_fresh_token_for_verified_users(): void
    {
        $user = $this->makeVerifiedUser();
        $this->actingAsUser($user, ['user']);

        $response = $this->getJson('/api/auth/auto-login');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Success')
            ->assertJsonStructure(['status', 'message', 'token']);

        $this->assertNotEmpty($response->headers->get('X-Token'));
    }
}
